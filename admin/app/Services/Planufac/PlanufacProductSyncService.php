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
    public function syncAll(int $pageSize = 200): array
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
        $total = null;

        do {
            $resp = $this->client->listProducts($pageSize, $start);
            $items = $resp['items'] ?? [];
            $total = $resp['total'] ?? $total;

            if (!is_array($items) || count($items) === 0) {
                break;
            }

            $now = Carbon::now();
            $rows = [];
            $brandNameByErpId = [];
            $categoryNameByErpId = [];

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $erpId = $this->extractId($item);
                if ($erpId === null) {
                    continue;
                }

                $name = $this->extractString($item, ['name', 'product_name', 'title']) ?? NULL;
                $sku = $this->extractString($item, ['sku', 'code', 'product_code', 'productCode']) ?? NULL;
                $productUnitSKU=$this->extractString($item, ['product_unit_sku', 'barcode']) ?? NULL;
                $description = $this->extractString($item, ['description', 'product_description', 'productDescription', 'desc']);
                $price = $this->extractNumber($item, ['retail_price', 'retailPrice']) ?? 0;
                $costPrice = $this->extractNumber($item, ['cost_price', 'costPrice', 'purchase_price', 'purchasePrice', 'buying_price', 'buyingPrice']);
                $weight = $this->extractNumber($item, ['weight', 'product_weight', 'productWeight', 'net_weight', 'netWeight']);
                $imageUrl = $this->extractImageUrl($item);
                $isActive = $this->extractActive($item);

                $brandName = $this->extractNestedName($item, 'brand', ['name']) ?? $this->extractString($item, ['brand_name', 'brandName']);
                $categoryName = $this->extractNestedName($item, 'category', ['name'])
                    ?? $this->extractNestedName($item, 'group', ['name'])
                    ?? $this->extractString($item, ['category_name', 'categoryName', 'group_name', 'groupName']);

                $brandNameByErpId[(string) $erpId] = $brandName;
                $categoryNameByErpId[(string) $erpId] = $categoryName;

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

            $this->syncProductBrandsAndCategories($rows, $brandNameByErpId, $categoryNameByErpId);

            $afterExistingCount = (int) DB::table('products')
                ->whereIn('planufac_product_id', array_values(array_unique(array_column($rows, 'planufac_product_id'))))
                ->count();

            $inserted = max(0, $afterExistingCount - $existingCount);
            $updated = max(0, count($rows) - $inserted);
            $summary['inserted'] += $inserted;
            $summary['updated'] += $updated;

            $start += $pageSize;
        } while ($total === null || $start < $total);

        $summary['finished_at'] = Carbon::now()->toDateTimeString();

        Cache::put(self::CACHE_LAST_SYNC_KEY, $summary, now()->addDays(7));

        return $summary;
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

    private function syncProductBrandsAndCategories(array $rows, array $brandNameByErpId, array $categoryNameByErpId): void
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
        $categoryIdByName = [];

        foreach ($erpIds as $erpId) {
            $productId = $productIdByErpId[(string) $erpId] ?? null;
            if (!$productId) {
                continue;
            }

            $brandName = $brandNameByErpId[(string) $erpId] ?? null;
            $categoryName = $categoryNameByErpId[(string) $erpId] ?? null;

            $brandId = null;
            if (is_string($brandName) && trim($brandName) !== '') {
                $brandName = trim($brandName);
                if (!array_key_exists($brandName, $brandIdByName)) {
                    $brand = Brand::withTrashed()->where('name', $brandName)->first();
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

                DB::table('product_brand')->insertOrIgnore([
                    'product_id' => $productId,
                    'brand_id' => $brandId,
                ]);
            }

            $categoryId = null;
            if (is_string($categoryName) && trim($categoryName) !== '') {
                $categoryName = trim($categoryName);
                if (!array_key_exists($categoryName, $categoryIdByName)) {
                    $category = Category::withTrashed()->where('name', $categoryName)->first();
                    if (!$category) {
                        $category = Category::create([
                            'name' => $categoryName,
                            'parent_id' => null,
                            'description' => null,
                            'is_active' => 1,
                            'sort_order' => 0,
                            'is_special' => 0,
                        ]);
                    } elseif (method_exists($category, 'trashed') && $category->trashed()) {
                        $category->restore();
                        $category->is_active = 1;
                        $category->save();
                    }
                    $categoryIdByName[$categoryName] = (int) $category->id;
                }
                $categoryId = $categoryIdByName[$categoryName];
            }

            // Optional: relate brand <-> category if both exist (used by some filters).
            if ($brandId && $categoryId) {
                DB::table('brand_category')->insertOrIgnore([
                    'brand_id' => $brandId,
                    'category_id' => $categoryId,
                    'is_primary' => 1,
                ]);
            }
        }
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

