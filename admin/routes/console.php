<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Models\Product;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:import-sql {path}', function (string $path) {
    /** @var ClosureCommand $this */
    $fullPath = base_path($path);
    if (!File::exists($fullPath)) {
        $this->error("File not found: {$fullPath}");
        return 1;
    }

    $content = File::get($fullPath);
    $totalInserted = 0;
    $totalProcessed = 0;

    $pattern = '/INSERT\s+INTO\s+`?([\w\-]+)`?\s*\(([^\)]*)\)\s*VALUES\s*(.*?);/ims';
    if (!preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
        $this->warn('No INSERT statements found.');
        return 0;
    }

    $allowed = [
        'name','sku','barcode','brand','category','description','image','stock_qty','in_stock','status','price','discounted_price'
    ];
    $aliasMap = [
      // name
      'product_name' => 'name', 'title' => 'name', 'name' => 'name',
      // sku
      'product_sku' => 'sku', 'sku_code' => 'sku', 'code' => 'sku', 'sku' => 'sku',
      // barcode / upc / ean
      'upc' => 'barcode', 'ean' => 'barcode', 'barcode' => 'barcode',
      // brand
      'brand_name' => 'brand', 'brand' => 'brand', 'manufacturer' => 'brand',
      // category (string)
      'category_name' => 'category', 'category' => 'category',
      // description
      'details' => 'description', 'desc' => 'description', 'description' => 'description',
      // image
      'image_url' => 'image', 'image_path' => 'image', 'image' => 'image', 'photo' => 'image',
      // qty
      'qty' => 'stock_qty', 'quantity' => 'stock_qty', 'stock' => 'stock_qty', 'stock_quantity' => 'stock_qty', 'inhand' => 'stock_qty',
      // in_stock
      'is_in_stock' => 'in_stock', 'available' => 'in_stock', 'active' => 'in_stock',
      // status
      'product_status' => 'status', 'state' => 'status', 'status' => 'status',
      // price
      'mrp' => 'price', 'amount' => 'price', 'unit_price' => 'price', 'price' => 'price',
      // discounted_price
      'sale_price' => 'discounted_price', 'discount_price' => 'discounted_price', 'offer_price' => 'discounted_price', 'discounted_price' => 'discounted_price',
    ];

    foreach ($matches as $m) {
        $columnsRaw = $m[2];
        $valuesRaw = trim($m[3]);

        $columns = array_map(function ($c) use ($aliasMap) {
            $c = trim($c);
            $c = trim($c, "`\" ");
            // map aliases to our target names if known
            $lower = strtolower($c);
            return $aliasMap[$lower] ?? $lower;
        }, explode(',', $columnsRaw));

        $tuples = [];
        $buf = '';
        $depth = 0;
        $inStr = false;
        $esc = false;
        for ($i=0; $i<strlen($valuesRaw); $i++) {
            $ch = $valuesRaw[$i];
            $buf .= $ch;
            if ($inStr) {
                if ($esc) { $esc = false; }
                elseif ($ch === '\\') { $esc = true; }
                elseif ($ch === "'") { $inStr = false; }
                continue;
            }
            if ($ch === "'") { $inStr = true; continue; }
            if ($ch === '(') { $depth++; continue; }
            if ($ch === ')') { $depth--; }
            if ($depth === 0 && $ch === ')') {
                $tuples[] = trim($buf);
                $buf = '';
                while ($i+1 < strlen($valuesRaw) && in_array($valuesRaw[$i+1], [',',' ','\n','\r','\t'])) { $i++; }
            }
        }

        foreach ($tuples as $tuple) {
            $tuple = trim($tuple);
            if (!str_starts_with($tuple, '(') || !str_ends_with($tuple, ')')) continue;
            $inner = substr($tuple, 1, -1);

            $vals = [];
            $val = '';
            $inStr2 = false; $esc2 = false;
            for ($i=0; $i<strlen($inner); $i++) {
                $ch = $inner[$i];
                if ($inStr2) {
                    if ($esc2) { $val .= $ch; $esc2 = false; continue; }
                    if ($ch === '\\') { $val .= $ch; $esc2 = true; continue; }
                    if ($ch === "'") { $inStr2 = false; $val .= $ch; continue; }
                    $val .= $ch; continue;
                }
                if ($ch === "'") { $inStr2 = true; $val .= $ch; continue; }
                if ($ch === ',') { $vals[] = trim($val); $val=''; continue; }
                $val .= $ch;
            }
            if ($val !== '') { $vals[] = trim($val); }

            if (count($vals) !== count($columns)) continue;
            $assoc = [];
            foreach ($columns as $idx => $col) {
                if (!in_array($col, $allowed)) continue;
                $raw = $vals[$idx];
                $value = null;
                if (strcasecmp($raw, 'NULL') === 0) {
                    $value = null;
                } else {
                    if (strlen($raw) >= 2 && $raw[0] === "'" && substr($raw, -1) === "'") {
                        $value = stripcslashes(substr($raw, 1, -1));
                    } else {
                        $value = trim($raw);
                    }
                }
                if (in_array($col, ['price','discounted_price'])) {
                    $value = $value === null || $value === '' ? null : (float) $value;
                } elseif (in_array($col, ['stock_qty'])) {
                    $value = (int) ($value ?? 0);
                } elseif ($col === 'in_stock') {
                    // Accept 1/0, true/false, yes/no
                    $normalized = is_string($value) ? strtolower(trim($value, "'\" ")) : $value;
                    $truthy = ['1','true','yes','y'];
                    $falsy = ['0','false','no','n'];
                    if (in_array($normalized, $truthy, true)) $value = true;
                    elseif (in_array($normalized, $falsy, true)) $value = false;
                    else $value = (bool)$value;
                }
                $assoc[$col] = $value;
            }

            if (empty($assoc)) continue;
            $totalProcessed++;
            if (!isset($assoc['name']) || !isset($assoc['sku'])) {
                continue;
            }
            if (isset($assoc['stock_qty']) && !isset($assoc['in_stock'])) {
                $assoc['in_stock'] = ((int)$assoc['stock_qty']) > 0;
            }
            if (!isset($assoc['status'])) { $assoc['status'] = 'Publish'; }

            Product::updateOrCreate(['sku' => $assoc['sku']], $assoc);
            $totalInserted++;
        }
    }

    $this->info("Processed: {$totalProcessed}, Inserted/Updated: {$totalInserted}");
    return 0;
})->purpose('Import products from SQL dump into products table (maps only related fields)');
