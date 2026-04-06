<?php

namespace App\Services\Planufac;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public function syncAll(int $pageSize = 1000, ?string $brandIdCsv = null): array
    {
        $startedAt = Carbon::now();
        Log::info('Syncing Planufac products', [
            'pageSize' => $pageSize,
            'brandIdCsv' => $brandIdCsv,
        ]);
        $summary = [
            'inserted' => 0,
            'updated' => 0,
            'processed' => 0,
            'started_at' => $startedAt->toDateTimeString(),
            'finished_at' => null,
        ];

        $start = 0;
        $filterBrandId = $this->parseSingleBrandFilterId($brandIdCsv);

        $defaultUnitId = DB::table('units')->orderBy('id')->value('id');
        $defaultUnitId = $defaultUnitId !== null ? (int) $defaultUnitId : null;
        $defaultVatMethod = DB::table('vat_methods')->orderBy('id')->first();

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
            /** @var array<string, string> sku => planufac_product_id */
            $batchSkus = [];
            /** @var array<string, string> product_unit_sku => planufac_product_id */
            $batchUnitSkus = [];

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $erpId = $this->extractId($item);
                if ($erpId === null) {
                    continue;
                }

                $erpIdStr = (string) $erpId;

                $name = $this->extractString($item, ['name', 'product_name', 'title']) ?? null;
                $sku = $this->extractString($item, ['sku', 'code', 'product_code', 'productCode']) ?? null;

                $productUnitSKU = $this->extractString($item, ['product_unit_sku', 'barcode']) ?? null;

                if ($this->shouldSkipDuplicateProductIdentifiers($erpIdStr, $sku, $productUnitSKU, $batchSkus, $batchUnitSkus)) {
                    continue;
                }
                $description = $this->extractString($item, ['description', 'product_description', 'productDescription', 'desc']);
                $price = $this->extractNumber($item, ['retail_price', 'retailPrice']) ?? 0;
                $costPrice = $this->extractNumber($item, ['cost_price', 'costPrice', 'purchase_price', 'purchasePrice', 'buying_price', 'buyingPrice']);
                $weight = $this->extractNumber($item, ['weight', 'product_weight', 'productWeight', 'net_weight', 'netWeight']);
                $imageUrl = $this->extractImageUrl($item);
                $isActive = $this->extractActive($item);

                $brandName = $this->extractBrandDisplayName($item);
                // appproductsapi: group + category are strings; main = group (root), sub = category (child).
                [$mainCategoryName, $subCategoryName] = $this->extractPlanufacCategoryPath($item);

                $brandNameByErpId[$erpIdStr] = $brandName;
                $brandErpIdByProductErpId[$erpIdStr] = $this->extractBrandErpId($item) ?? $filterBrandId;
                $categoryPathByErpId[$erpIdStr] = [
                    'main' => $mainCategoryName,
                    'sub' => $subCategoryName,
                ];

                if ($sku !== null && $sku !== '') {
                    $batchSkus[$sku] = $erpIdStr;
                }
                if ($productUnitSKU !== null && $productUnitSKU !== '') {
                    $batchUnitSkus[$productUnitSKU] = $erpIdStr;
                }

                $vatSnapshot = $this->vatSnapshotFromDefaultMethod($defaultVatMethod, (float) $price);

                $rows[] = array_merge([
                    'planufac_product_id' => $erpIdStr,
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
                    'unit_id' => $defaultUnitId,
                    'updated_at' => $now,
                    'created_at' => $now,
                ], $vatSnapshot);
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
                    [
                        'planufac_synced_at',
                        'planufac_payload',
                        'name',
                        'sku',
                        'product_unit_sku',
                        'description',
                        'price',
                        'cost_price',
                        'weight',
                        'image_url',
                        'is_active',
                        'unit_id',
                        'vat_method_id',
                        'vat_method_name',
                        'vat_method_type',
                        'vat_percentage',
                        'vat_amount',
                        'updated_at',
                    ]
                );
            } catch (QueryException $e) {
                // Fallback path: if sku has a unique constraint and conflicts, update by sku instead.
                foreach ($rows as $row) {
                    try {
                        $this->upsertProductRowFallback($row);
                    } catch (QueryException $fallbackEx) {
                        if ($this->isMysqlDuplicateKeyError($fallbackEx)) {
                            continue;
                        }
                        throw $fallbackEx;
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

    /**
     * Skip when sku or product_unit_sku (barcode) is already used by another planufac product
     * or by another row in the current API batch.
     *
     * @param  array<string, string>  $batchSkus
     * @param  array<string, string>  $batchUnitSkus
     */
    private function shouldSkipDuplicateProductIdentifiers(
        string $erpIdStr,
        ?string $sku,
        ?string $productUnitSku,
        array $batchSkus,
        array $batchUnitSkus
    ): bool {
        $skuT = ($sku !== null && trim((string) $sku) !== '') ? trim((string) $sku) : '';
        $unitT = ($productUnitSku !== null && trim((string) $productUnitSku) !== '') ? trim((string) $productUnitSku) : '';

        if ($skuT !== '') {
            if (isset($batchSkus[$skuT]) && $batchSkus[$skuT] !== $erpIdStr) {
                return true;
            }
            if (DB::table('products')
                ->where('sku', $skuT)
                ->where('planufac_product_id', '!=', $erpIdStr)
                ->exists()) {
                return true;
            }
        }

        if ($unitT !== '') {
            if (isset($batchUnitSkus[$unitT]) && $batchUnitSkus[$unitT] !== $erpIdStr) {
                return true;
            }
            if (DB::table('products')
                ->where('product_unit_sku', $unitT)
                ->where('planufac_product_id', '!=', $erpIdStr)
                ->exists()) {
                return true;
            }
        }

        return false;
    }

    private function isMysqlDuplicateKeyError(QueryException $e): bool
    {
        $msg = $e->getMessage();
        if (str_contains($msg, 'Duplicate entry') || str_contains($msg, '1062')) {
            return true;
        }
        $info = $e->errorInfo;
        if (is_array($info) && array_key_exists(1, $info)) {
            return (int) $info[1] === 1062;
        }

        return false;
    }

    /**
     * Per-row fallback when bulk upsert fails (e.g. sku unique). Skips row on duplicate product_unit_sku / sku.
     *
     * @param  array<string, mixed>  $row
     */
    private function upsertProductRowFallback(array $row): void
    {
        $sku = (string) ($row['sku'] ?? '');
        $planufacId = (string) ($row['planufac_product_id'] ?? '');
        $unitSku = isset($row['product_unit_sku']) && $row['product_unit_sku'] !== null && $row['product_unit_sku'] !== ''
            ? trim((string) $row['product_unit_sku'])
            : '';

        if ($sku === '') {
            if ($unitSku !== '' && DB::table('products')->where('product_unit_sku', $unitSku)->exists()) {
                return;
            }
            try {
                DB::table('products')->insert($row);
            } catch (QueryException $e) {
                if ($this->isMysqlDuplicateKeyError($e)) {
                    return;
                }
                throw $e;
            }

            return;
        }

        $existing = DB::table('products')->where('sku', $sku)->first();
        if ($existing) {
            if ($unitSku !== '') {
                $taken = DB::table('products')
                    ->where('product_unit_sku', $unitSku)
                    ->where('id', '!=', $existing->id)
                    ->exists();
                if ($taken) {
                    return;
                }
            }
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
                'unit_id' => $row['unit_id'] ?? null,
                'vat_method_id' => $row['vat_method_id'] ?? null,
                'vat_method_name' => $row['vat_method_name'] ?? null,
                'vat_method_type' => $row['vat_method_type'] ?? null,
                'vat_percentage' => $row['vat_percentage'] ?? 0,
                'vat_amount' => $row['vat_amount'] ?? 0,
                'updated_at' => $row['updated_at'],
            ]);

            return;
        }

        $row['sku'] = $sku . '-' . $planufacId;

        if ($unitSku !== '' && DB::table('products')->where('product_unit_sku', $unitSku)->exists()) {
            return;
        }

        try {
            DB::table('products')->insert($row);
        } catch (QueryException $e) {
            if ($this->isMysqlDuplicateKeyError($e)) {
                return;
            }
            throw $e;
        }
    }

    /**
     * When sync is scoped to a single brand_id query param, use it as erp_brand_id if the row has no id.
     */
    private function parseSingleBrandFilterId(?string $brandIdCsv): ?int
    {
        if ($brandIdCsv === null || trim($brandIdCsv) === '') {
            return null;
        }
        $s = trim($brandIdCsv);
        if (!preg_match('/^\d+$/', $s)) {
            return null;
        }

        return (int) $s;
    }

    /**
     * appproductsapi returns "brand" as a string; older payloads may use nested brand.name.
     */
    private function extractBrandDisplayName(array $item): ?string
    {
        if (array_key_exists('brand', $item) && is_string($item['brand'])) {
            $t = trim($item['brand']);

            return $t !== '' ? $t : null;
        }

        return $this->extractNestedName($item, 'brand', ['name'])
            ?? $this->extractString($item, ['brand_name', 'brandName']);
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

    /**
     * Group / category from appproductsapi are plain strings; legacy payloads may nest { name: "..." }.
     */
    private function extractScalarGroupOrCategory(array $item, string $key): ?string
    {
        if (!array_key_exists($key, $item)) {
            return null;
        }
        $v = $item[$key];
        if (is_string($v)) {
            $s = trim($v);

            return $s !== '' ? $s : null;
        }
        if (is_array($v)) {
            return $this->extractNestedName($item, $key, ['name']);
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
        $main = $this->extractScalarGroupOrCategory($item, 'group');
        $sub = $this->extractScalarGroupOrCategory($item, 'category');

        if ($main === null && $sub !== null) {
            return [$sub, null];
        }

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
       
        // default active on sync
        return 1;
    }

    /**
     * Match admin product save logic: derive VAT fields from the default vat_methods row (if any).
     *
     * @param  object|null  $vat  first row from vat_methods ordered by id asc
     * @return array{vat_method_id: ?int, vat_method_name: ?string, vat_method_type: ?string, vat_percentage: float, vat_amount: float}
     */
    private function vatSnapshotFromDefaultMethod(?object $vat, float $price): array
    {
        if ($vat === null) {
            return [
                'vat_method_id' => null,
                'vat_method_name' => null,
                'vat_method_type' => null,
                'vat_percentage' => 0,
                'vat_amount' => 0,
            ];
        }

        $vatMethodId = (int) $vat->id;
        $vatMethodName = isset($vat->name) ? (string) $vat->name : null;
        $vatMethodType = isset($vat->type) ? (string) $vat->type : null;
        $amount = isset($vat->amount) ? (float) $vat->amount : 0.0;

        $vatPercentage = 0.0;
        $vatAmount = 0.0;

        if ($vatMethodType === 'Percentage') {
            $vatPercentage = $amount;
            $vatAmount = $price * $amount / 100;
        } else {
            $vatAmount = $amount;
            if ($price > 0) {
                $vatPercentage = ($amount / $price) * 100;
            }
        }

        return [
            'vat_method_id' => $vatMethodId,
            'vat_method_name' => $vatMethodName,
            'vat_method_type' => $vatMethodType,
            'vat_percentage' => $vatPercentage,
            'vat_amount' => $vatAmount,
        ];
    }
}

