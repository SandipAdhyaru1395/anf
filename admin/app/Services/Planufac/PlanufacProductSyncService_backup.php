<?php

namespace App\Services\Planufac;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PlanufacProductSyncService
{
    public const CACHE_LAST_SYNC_KEY = 'planufac.products.sync.last';

    public function __construct(private readonly PlanufacClient $client)
    {
    }

    /**
     * Sync all products from ERP into local products table.
     *
     * @return array{inserted:int,updated:int,processed:int,started_at:string,finished_at:string}
     */
    public function syncAll(int $pageSize = 20, ?string $brandIdCsv = null): array
    {
        $startedAt = Carbon::now();

        $summary = [
            'inserted' => 0,
            'updated' => 0,
            'processed' => 0,
            'started_at' => $startedAt->toDateTimeString(),
            'finished_at' => null,
        ];

        $start = 0;

        while (true) {
            $resp = $this->client->listProducts($pageSize, $start, 'products.name', 'asc', '', $brandIdCsv);
            $items = $resp['items'] ?? [];

            if (!is_array($items) || count($items) === 0) {
                break;
            }

            $batchCount = count($items);

            $now = Carbon::now();
            $rows = [];
            $brandNameByErpId = [];
            $brandErpIdByProductErpId = [];
            $categoryPathByErpId = [];

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $erpId = $this->extractId($item);
                if ($erpId === null) {
                    continue;
                }

                if ($this->hasDiscontinuedTag($item)) {
                    continue;
                }

                $name = $this->extractString($item, ['name', 'product_name', 'title']) ?? null;
                $sku = $this->extractString($item, ['sku', 'code', 'product_code', 'productCode']) ?? null;
                if ($this->skuOrNameContainsDeleted($name, $sku, $item)) {
                    continue;
                }

                $productUnitSKU = $this->extractString($item, ['product_unit_sku', 'barcode']) ?? null;
                $description = $this->extractString($item, ['description', 'product_description', 'productDescription', 'desc']);
                $price = $this->extractNumber($item, ['retail_price', 'retailPrice']) ?? 0;
                $costPrice = $this->extractNumber($item, ['cost_price', 'costPrice', 'purchase_price', 'purchasePrice', 'buying_price', 'buyingPrice']);
                $weight = $this->extractNumber($item, ['weight', 'product_weight', 'productWeight', 'net_weight', 'netWeight']);
                $imageUrl = $this->extractImageUrl($item);
                $isActive = $this->extractActive($item);

                $brandName = $this->extractNestedName($item, 'brand', ['name']) ?? $this->extractString($item, ['brand_name', 'brandName']);
                // Hierarchy: group.name = main (root) category, category.name = subcategory under that root; brand sits under the leaf.
                [$mainCategoryName, $subCategoryName] = $this->extractPlanufacCategoryPath($item);

                $brandNameByErpId[(string) $erpId] = $brandName;
                $brandErpIdByProductErpId[(string) $erpId] = $this->extractBrandErpId($item);
                $categoryPathByErpId[(string) $erpId] = [
                    'main' => $mainCategoryName,
                    'sub' => $subCategoryName,
                ];

                $rows[] = [
                    'planufac_product_id' => (string) $erpId,
                    'planufac_synced_at' => $now,
                    'planufac_payload' => json_encode($item, JSON_UNESCAPED_SLASHES),
                    'name' => $name,
                    'sku' => $sku,
                    'product_unit_sku' => $productUnitSKU,
                    'description' => $description,
                    'price' => $price,
                    'cost_price' => $costPrice,
                    'weight' => $weight,
                    'image_url' => $imageUrl,
                    'is_active' => $isActive,
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
            }

            if (count($rows) === 0) {
                $start += $pageSize;
                if ($batchCount < $pageSize) {
                    break;
                }
                continue;
            }

            $summary['processed'] += count($rows);

            // Count existing (for inserted/updated stats) using ERP ids.
            $existingCount = (int) DB::table('products')
                ->whereIn('planufac_product_id', array_values(array_unique(array_column($rows, 'planufac_product_id'))))
                ->count();

            try {
                // Fast path: ERP id is unique (we added unique index)
                DB::table('products')->upsert(
                    $rows,
                    ['planufac_product_id'],
                    ['planufac_synced_at', 'planufac_payload', 'name', 'sku', 'product_unit_sku','description', 'price', 'cost_price', 'weight', 'image_url', 'is_active', 'updated_at']
                );
            } catch (QueryException $e) {
                // Fallback path: if sku has a unique constraint and conflicts, update by sku instead.
                foreach ($rows as $row) {
                    $sku = (string) ($row['sku'] ?? '');
                    if ($sku === '') {
                        continue;
                    }

                    $existing = DB::table('products')->where('sku', $sku)->first();
                    if ($existing) {
                        DB::table('products')->where('id', $existing->id)->update([
                            'planufac_product_id' => $row['planufac_product_id'],
                            'planufac_synced_at' => $row['planufac_synced_at'],
                            'planufac_payload' => $row['planufac_payload'],
                            'name' => $row['name'],
                            'product_unit_sku' => $row['product_unit_sku'],
                            'description' => $row['description'],
                            'price' => $row['price'],
                            'cost_price' => $row['cost_price'],
                            'weight' => $row['weight'],
                            'image_url' => $row['image_url'],
                            'is_active' => $row['is_active'],
                            'updated_at' => $row['updated_at'],
                        ]);
                    } else {
                        // Last resort insert with a de-conflicted sku
                        $row['sku'] = $sku . '-' . $row['planufac_product_id'];
                        DB::table('products')->insert($row);
                    }
                }
            }

            $this->syncProductBrandsAndCategories($rows, $brandNameByErpId, $brandErpIdByProductErpId, $categoryPathByErpId);

            $afterExistingCount = (int) DB::table('products')
                ->whereIn('planufac_product_id', array_values(array_unique(array_column($rows, 'planufac_product_id'))))
                ->count();

            $inserted = max(0, $afterExistingCount - $existingCount);
            $updated = max(0, count($rows) - $inserted);
            $summary['inserted'] += $inserted;
            $summary['updated'] += $updated;

            $start += $pageSize;

            // Do not use API `total` / `recordsTotal` as the only stop condition: some Planufac
            // responses set it to 1 or to the current page length, which aborts after the first page.
            // DataTables-style rule: a page shorter than `length` means we are done.
            if ($batchCount < $pageSize) {
                break;
            }
        }

        $summary['finished_at'] = Carbon::now()->toDateTimeString();

        Cache::put(self::CACHE_LAST_SYNC_KEY, $summary, now()->addDays(7));

        return $summary;
    }

    private function hasDiscontinuedTag(array $item): bool
    {
        if (!array_key_exists('tags', $item) || !is_array($item['tags'])) {
            return false;
        }
        foreach ($item['tags'] as $tag) {
            if (is_string($tag) && strcasecmp(trim($tag), 'discontinued') === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Skip products whose name or SKU (including Planufac formatted fields) contains "deleted" (case-insensitive).
     */
    private function skuOrNameContainsDeleted(?string $name, ?string $sku, array $item): bool
    {
        $haystacks = [
            (string) ($name ?? ''),
            (string) ($sku ?? ''),
        ];
        foreach (['formattedSku', 'formatted_sku', 'formattedName', 'formatted_name'] as $k) {
            if (array_key_exists($k, $item) && is_string($item[$k])) {
                $haystacks[] = $item[$k];
            }
        }

        foreach ($haystacks as $h) {
            $h = trim($h);
            if ($h !== '' && stripos($h, 'deleted') !== false) {
                return true;
            }
        }

        return false;
    }

    private function extractId(array $item): ?string
    {
        foreach (['id', 'product_id', 'productId', 'uuid'] as $k) {
            if (!array_key_exists($k, $item)) {
                continue;
            }
            $v = $item[$k];
            if (is_int($v) || is_string($v)) {
                $s = trim((string) $v);
                if ($s !== '') {
                    return $s;
                }
            }
        }
        return null;
    }

    private function extractString(array $item, array $keys): ?string
    {
        foreach ($keys as $k) {
            if (!array_key_exists($k, $item)) {
                continue;
            }
            $v = $item[$k];
            if (is_string($v)) {
                $s = trim($v);
                if ($s !== '') {
                    return $s;
                }
            }
        }
        return null;
    }

    private function extractImageUrl(array $item): ?string
    {
        // Sometimes the API returns the URL directly in top-level keys.
        $direct = $this->extractString($item, ['image_url', 'imageUrl']);
        if ($direct !== null) {
            return $direct;
        }

        // Sometimes "image" is already a URL string.
        if (array_key_exists('image', $item) && is_string($item['image'])) {
            $s = trim($item['image']);
            if ($s !== '') {
                return $s;
            }
        }

        // Planufac commonly returns: "image": { "filename": "https://..." , ... }
        if (array_key_exists('image', $item) && is_array($item['image'])) {
            $img = $item['image'];
            foreach (['filename', 'url', 'src', 'path'] as $k) {
                if (array_key_exists($k, $img) && is_string($img[$k])) {
                    $s = trim($img[$k]);
                    if ($s !== '') {
                        return $s;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Planufac product payload may include top-level brand_id or nested brand.id.
     */
    private function extractBrandErpId(array $item): ?int
    {
        foreach (['brand_id', 'brandId'] as $k) {
            if (!array_key_exists($k, $item)) {
                continue;
            }
            $v = $item[$k];
            if (is_int($v)) {
                return $v;
            }
            if (is_string($v) && $v !== '' && ctype_digit($v)) {
                return (int) $v;
            }
        }

        if (array_key_exists('brand', $item) && is_array($item['brand'])) {
            $nested = $item['brand'];
            foreach (['id', 'brand_id'] as $k) {
                if (!array_key_exists($k, $nested)) {
                    continue;
                }
                $v = $nested[$k];
                if (is_int($v)) {
                    return $v;
                }
                if (is_string($v) && $v !== '' && ctype_digit($v)) {
                    return (int) $v;
                }
            }
        }

        return null;
    }

    private function extractNestedName(array $item, string $containerKey, array $nameKeys = ['name']): ?string
    {
        if (!array_key_exists($containerKey, $item) || !is_array($item[$containerKey])) {
            return null;
        }

        /** @var array $nested */
        $nested = $item[$containerKey];
        foreach ($nameKeys as $k) {
            if (array_key_exists($k, $nested) && is_string($nested[$k])) {
                $s = trim($nested[$k]);
                if ($s !== '') {
                    return $s;
                }
            }
        }

        return null;
    }

    /**
     * Planufac: group = main category (root), category = child under group; product and brand attach to the leaf.
     *
     * @param  array<string, array{main: ?string, sub: ?string}>  $categoryPathByErpId
     */
    private function syncProductBrandsAndCategories(array $rows, array $brandNameByErpId, array $brandErpIdByProductErpId, array $categoryPathByErpId): void
    {
        $erpIds = array_values(array_unique(array_column($rows, 'planufac_product_id')));
        if (count($erpIds) === 0) {
            return;
        }

        $productIdByErpId = DB::table('products')
            ->select(['id', 'planufac_product_id'])
            ->whereIn('planufac_product_id', $erpIds)
            ->get()
            ->mapWithKeys(fn ($r) => [(string) $r->planufac_product_id => (int) $r->id])
            ->all();

        $brandIdByName = [];
        /** @var array<string, int> keyed by "main\0sub" (sub empty = leaf is root) */
        $leafCategoryIdByPath = [];

        foreach ($erpIds as $erpId) {
            $productId = $productIdByErpId[(string) $erpId] ?? null;
            if (!$productId) {
                continue;
            }

            $brandName = $brandNameByErpId[(string) $erpId] ?? null;
            $path = $categoryPathByErpId[(string) $erpId] ?? null;
            $mainName = is_array($path) ? ($path['main'] ?? null) : null;
            $subName = is_array($path) ? ($path['sub'] ?? null) : null;

            $brandId = null;
            if (is_string($brandName) && trim($brandName) !== '') {
                $brandName = trim($brandName);
                $erpBrandId = $brandErpIdByProductErpId[(string) $erpId] ?? null;

                if (!array_key_exists($brandName, $brandIdByName)) {
                    $brand = null;
                    if ($erpBrandId !== null) {
                        $brand = Brand::withTrashed()->where('erp_brand_id', $erpBrandId)->first();
                    }
                    if (!$brand) {
                        $brand = Brand::withTrashed()->where('name', $brandName)->first();
                    }
                    if (!$brand) {
                        $brand = Brand::create([
                            'name' => $brandName,
                            'is_active' => 1,
                        ]);
                    } elseif (method_exists($brand, 'trashed') && $brand->trashed()) {
                        $brand->restore();
                        $brand->is_active = 1;
                        $brand->save();
                    }
                    $brandIdByName[$brandName] = (int) $brand->id;
                }
                $brandId = $brandIdByName[$brandName];

                if ($erpBrandId !== null) {
                    Brand::where('id', $brandId)
                        ->where(function ($q) {
                            $q->whereNull('erp_brand_id')->orWhere('erp_brand_id', 0);
                        })
                        ->update(['erp_brand_id' => $erpBrandId]);
                }

                DB::table('product_brand')->insertOrIgnore([
                    'product_id' => $productId,
                    'brand_id' => $brandId,
                ]);
            }

            $leafCategoryId = $this->resolveOrCreateCategoryLeaf($mainName, $subName, $leafCategoryIdByPath);

            // Brand ↔ leaf category (group → category → brand; product is linked via product_brand).
            // No `product_categories` table in this project — category for a product is implied through brand_category + product_brand.
            if ($brandId && $leafCategoryId !== null) {
                DB::table('brand_category')->insertOrIgnore([
                    'brand_id' => $brandId,
                    'category_id' => $leafCategoryId,
                    'is_primary' => 1,
                ]);
            }
        }
    }

    /**
     * @return array{0: ?string, 1: ?string} [mainCategoryName, subCategoryName]
     */
    private function extractPlanufacCategoryPath(array $item): array
    {
        $groupName = $this->extractNestedName($item, 'group', ['name']);
        $categoryName = $this->extractNestedName($item, 'category', ['name']);

        $main = $groupName !== null && trim($groupName) !== '' ? trim($groupName) : null;
        $sub = $categoryName !== null && trim($categoryName) !== '' ? trim($categoryName) : null;

        // Payloads with only category (no group): keep previous flat behaviour (single root).
        if ($main === null && $sub !== null) {
            return [$sub, null];
        }

        // Same name on group and category: one level only (avoid redundant parent/child with identical names).
        if ($main !== null && $sub !== null && strcasecmp($main, $sub) === 0) {
            return [$main, null];
        }

        return [$main, $sub];
    }

    /**
     * Ensure root (main) and optional child (sub) categories exist; return the leaf category id for pivots.
     *
     * @param  array<string, int>  $cache  updated by reference; keys "main\0" and "main\0sub"
     */
    private function resolveOrCreateCategoryLeaf(?string $mainName, ?string $subName, array &$cache): ?int
    {
        if ($mainName === null || $mainName === '') {
            return null;
        }

        $subKey = ($subName !== null && $subName !== '') ? $subName : '';
        $leafKey = $mainName . "\0" . $subKey;
        if (array_key_exists($leafKey, $cache)) {
            return $cache[$leafKey];
        }

        $main = Category::withTrashed()
            ->whereNull('parent_id')
            ->where('name', $mainName)
            ->first();

        if (!$main) {
            $main = Category::create([
                'name' => $mainName,
                'parent_id' => null,
                'description' => null,
                'is_active' => 1,
                'sort_order' => 0,
                'is_special' => 0,
            ]);
        } elseif (method_exists($main, 'trashed') && $main->trashed()) {
            $main->restore();
            $main->is_active = 1;
            $main->save();
        }

        $mainId = (int) $main->id;
        $cache[$mainName . "\0"] = $mainId;

        if ($subKey === '') {
            $cache[$leafKey] = $mainId;

            return $mainId;
        }

        $child = Category::withTrashed()
            ->where('parent_id', $mainId)
            ->where('name', $subName)
            ->first();

        if (!$child) {
            $child = Category::create([
                'name' => $subName,
                'parent_id' => $mainId,
                'description' => null,
                'is_active' => 1,
                'sort_order' => 0,
                'is_special' => 0,
            ]);
        } elseif (method_exists($child, 'trashed') && $child->trashed()) {
            $child->restore();
            $child->is_active = 1;
            $child->save();
        }

        $leafId = (int) $child->id;
        $cache[$leafKey] = $leafId;

        return $leafId;
    }

    private function extractNumber(array $item, array $keys): ?float
    {
        foreach ($keys as $k) {
            if (!array_key_exists($k, $item)) {
                continue;
            }
            $v = $item[$k];
            if (is_numeric($v)) {
                return (float) $v;
            }
        }
        return null;
    }

    private function extractActive(array $item): int
    {
        $v = $item['is_active'] ?? $item['active'] ?? $item['status'] ?? null;
        if (is_bool($v)) {
            return $v ? 1 : 0;
        }
        if (is_numeric($v)) {
            return ((int) $v) === 1 ? 1 : 0;
        }
        if (is_string($v)) {
            $s = strtolower(trim($v));
            if (in_array($s, ['1', 'true', 'active', 'published', 'yes'], true)) {
                return 1;
            }
            if (in_array($s, ['0', 'false', 'inactive', 'unpublished', 'no'], true)) {
                return 0;
            }
        }
        // default active on sync
        return 1;
    }
}

