<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use App\Services\WarehouseProductSyncService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\BrandCategory;
use App\Models\Tag;
use App\Models\BrandTag;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Models\VatMethod;
use App\Models\Setting;
use App\Models\Unit;

class ProductController extends Controller
{
  public function index()
  {
    $data['total_products_count'] = Product::all()->count();
    $data['active_products_count'] = Product::where('is_active',1)->count();
    $data['inactive_products_count'] = Product::where('is_active',0)->count();

    return view('content.product.list',$data);
  }

  public function create(Request $request)
  {
   
    $validated = $request->validate([
      'brands' => ['required'],
      'step' => ['required', 'numeric', 'min:1'],
      'productTitle' => ['required'],
      'productSku' => ['required','unique:products,sku'],
      'productUnitSku' => ['required','unique:products,product_unit_sku'],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productImage' => [
        'nullable',
        function ($attribute, $value, $fail) use ($request) {
          $imageUrl = trim($request->input('productImageUrl', ''));
          // If no file uploaded and no URL provided, require at least one
          if (!$request->hasFile('productImage') && empty($imageUrl)) {
            $fail('Either an image file or image URL is required.');
          }
          // If file is provided, validate it
          if ($request->hasFile('productImage')) {
            $file = $request->file('productImage');
            if (!$file->isValid()) {
              $fail('The uploaded image file is invalid.');
            }
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedMimes)) {
              $fail('Only jpg, png, jpeg, and webp images are allowed.');
            }
          }
        },
      ],
      'productImageUrl' => [
        'nullable',
        'max:2048',
        function ($attribute, $value, $fail) use ($request) {
          $trimmedValue = trim($value ?? '');
          // If URL is provided, validate it
          if (!empty($trimmedValue)) {
            if (!filter_var($trimmedValue, FILTER_VALIDATE_URL)) {
              $fail('The image URL must be a valid URL.');
            }
          }
          // If no URL and no file, require at least one
          if (empty($trimmedValue) && !$request->hasFile('productImage')) {
            $fail('Either an image file or image URL is required.');
          }
        },
      ],
      'vat_method_id' => ['nullable','exists:vat_methods,id'],
      'unit_id' => ['nullable','exists:units,id'],
      'costPrice' => ['nullable', 'numeric', 'min:0'],
      'weight' => ['nullable','numeric'],
      'rrp' => ['nullable','numeric'],
      'expiry_date' => ['nullable','date_format:d/m/Y'],
    ],[
      'brands.required' => 'Brand is required',
      'step.required' => 'Step quantity is required',
      'step.numeric' => 'Must be valid number',
      'step.min' => 'Must be greater than 0',
      'productTitle.required' => 'Name is required',
      'productSku.unique' => 'Product code is already taken',
      'productSku.required' => 'Product code is required',
      'productUnitSku.required' => 'Product unit code is required',
      'productUnitSku.unique' => 'Product unit code is already taken',
      'productPrice.required' => 'Price is required',
      'productPrice.numeric' => 'Price must be valid number',
      'productPrice.min' => 'Price can not be less than 0',
      'productImageUrl.max' => 'Image URL must not exceed 2048 characters',
      'costPrice.numeric' => 'Cost price must be valid number',
      'costPrice.min' => 'Cost price can not be less than 0',
      'weight.numeric' => 'Weight must be a number',
      'rrp.numeric' => 'RRP must be a number',
      'expiry_date.date_format' => 'Expiry date must be in dd/mm/yyyy format',
    ]);

    // Determine image URL: use uploaded file if provided, otherwise use URL input
    $imageUrl = null;
    if ($request->hasFile('productImage')) {
      $path = $request->file('productImage')->store('products', 'public');
      $imageUrl = asset('storage/' . $path);
    } elseif (!empty($request->productImageUrl)) {
      $imageUrl = $request->productImageUrl;
    }
    $price = $validated['productPrice'];
    $vatAmount = 0; $vatPercentage = 0; $vatMethodId = null; $vatMethodName = null; $vatMethodType = null;
    if ($request->vat_method_id) {
        $vatMethod = \App\Models\VatMethod::findOrFail($request->vat_method_id);
        $vatMethodId = $vatMethod->id;
        if ($vatMethod->type == 'Percentage') {
            $vatPercentage = $vatMethod->amount;
            $vatAmount = $price * $vatMethod->amount / 100;
        } else {
            // For Fixed type, calculate percentage: (fixed_amount / price) * 100
            $vatAmount = $vatMethod->amount;
            if ($price > 0) {
                $vatPercentage = ($vatMethod->amount / $price) * 100;
            }
        }
        $vatMethodName = $vatMethod->name;
        $vatMethodType = $vatMethod->type;
    }
    $expiryDate = null;
    if ($request->expiry_date) {
      $dt = \DateTime::createFromFormat('d/m/Y', $request->expiry_date);
      $expiryDate = $dt ? $dt->format('Y-m-d') : null;
    }
    $quantity = $request->quantity ?? 0;
    $costPrice = $request->costPrice ?? 0;

    $product = DB::transaction(function () use ($validated, $request, $price, $vatAmount, $vatPercentage, $vatMethodId, $vatMethodName, $vatMethodType, $expiryDate, $quantity, $costPrice, $imageUrl) {
      $product = Product::create([
        'name' => $validated['productTitle'],
        'sku' => $validated['productSku'],
        'product_unit_sku' => $validated['productUnitSku'],
        'step_quantity' => $validated['step'],
        'description' => $request->productDescription ?? null,
        'price' => $price,
        'cost_price' => $costPrice,
        'wallet_credit' => $request->walletCredit ?? 0,
        'weight' => $request->weight ?? null,
        'rrp' => $request->rrp ?? null,
        'expiry_date' => $expiryDate,
        'image_url' => $imageUrl,
        'stock_quantity' => $quantity,
        'vat_percentage' => $vatPercentage,
        'vat_method_id' => $vatMethodId,
        'vat_amount' => $vatAmount,
        'vat_method_name' => $vatMethodName,
        'vat_method_type' => $vatMethodType,
        'unit_id' => $request->unit_id,
        'is_active' => $request->productStatus ?? 0,
        'brand_id' => $request->brand_id,
      ]);

      // Create product brands
      foreach ($request->brands as $brand) {
        ProductBrand::create([
          'product_id' => $product->id,
          'brand_id' => $brand
        ]);
      }

      // Sync warehouse product
      WarehouseProductSyncService::sync($product->id, $quantity, $costPrice);
      
      return $product;
    });

    Toastr::success('Product created successfully!');
    return redirect()->route('product.list');
  }

  public function edit($id){
    
    $data['product'] = Product::findOrFail($id);
    $data['brands'] = Brand::all();

    $data['productBrands'] = ProductBrand::where('product_id', $id)->pluck('brand_id')->toArray();
    $data['vatMethods'] = VatMethod::where('status', 'Active')->orderBy('name')->get();
    $data['units'] = Unit::where('status', 'Active')->orderBy('name')->get();
    $settings = Setting::all()->pluck('value', 'key');
    $data['currencySymbol'] = $settings['currency_symbol'] ?? '₱';

    return view('content.product.edit',$data);
  }
  
  public function update(Request $request){
    
    $product = Product::findOrFail($request->id);
    $hasExistingImage = !empty($product->image_url);
    
    $validated = $request->validate([
      'brands' => ['required'],
      'step' => ['required', 'numeric', 'min:1'],
      'productTitle' => ['required'],
      'productSku' => ['required','unique:products,sku,'.$request->id],
      'productUnitSku' => ['required','unique:products,product_unit_sku,'.$request->id],
      'productPrice' => ['required', 'numeric', 'min:0'],
      'productImage' => [
        'nullable',
        function ($attribute, $value, $fail) use ($request, $hasExistingImage) {
          $imageUrl = trim($request->input('productImageUrl', ''));
          // If no file uploaded, no URL provided, and no existing image, require at least one
          if (!$request->hasFile('productImage') && empty($imageUrl) && !$hasExistingImage) {
            $fail('Either an image file or image URL is required.');
          }
          // If file is provided, validate it
          if ($request->hasFile('productImage')) {
            $file = $request->file('productImage');
            if (!$file->isValid()) {
              $fail('The uploaded image file is invalid.');
            }
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedMimes)) {
              $fail('Only jpg, png, jpeg, and webp images are allowed.');
            }
          }
        },
      ],
      'productImageUrl' => [
        'nullable',
        'max:2048',
        function ($attribute, $value, $fail) use ($request, $hasExistingImage) {
          $trimmedValue = trim($value ?? '');
          // If URL is provided, validate it
          if (!empty($trimmedValue)) {
            if (!filter_var($trimmedValue, FILTER_VALIDATE_URL)) {
              $fail('The image URL must be a valid URL.');
            }
          }
          // If no URL, no file, and no existing image, require at least one
          if (empty($trimmedValue) && !$request->hasFile('productImage') && !$hasExistingImage) {
            $fail('Either an image file or image URL is required.');
          }
        },
      ],
      'vat_method_id' => ['nullable','exists:vat_methods,id'],
      'unit_id' => ['nullable','exists:units,id'],
      'costPrice' => ['nullable', 'numeric', 'min:0'],
      'weight' => ['nullable','numeric'],
      'rrp' => ['nullable','numeric'],
      'expiry_date' => ['nullable','date_format:d/m/Y'],
    ],[
      'brands.required' => 'Brand is required',
      'step.required' => 'Step quantity is required',
      'step.numeric' => 'Must be valid number',
      'step.min' => 'Must be greater than 0',
      'productTitle.required' => 'Name is required',
      'productSku.unique' => 'Product code is already taken',
      'productSku.required' => 'Product code is required',
      'productUnitSku.required' => 'Product unit code is required',
      'productUnitSku.unique' => 'Product unit code is already taken',
      'productPrice.required' => 'Price is required',
      'productPrice.numeric' => 'Price must be valid number',
      'productPrice.min' => 'Price can not be less than 0',
      'productImageUrl.max' => 'Image URL must not exceed 2048 characters',
      'costPrice.numeric' => 'Cost price must be valid number',
      'costPrice.min' => 'Cost price can not be less than 0',
      'weight.numeric' => 'Weight must be a number',
      'rrp.numeric' => 'RRP must be a number',
      'expiry_date.date_format' => 'Expiry date must be in dd/mm/yyyy format',
    ]);
     

    // Determine image URL: prioritize uploaded file, then URL input, then keep existing
    $imageUrl = $product->image_url; // Default to existing image

    if($request->hasFile('productImage')){
      $path = $request->file('productImage')->store('products', 'public');
      $imageUrl = asset('storage/' . $path);
    } elseif(!empty($request->productImageUrl)){
      $imageUrl = $request->productImageUrl;
    }
    $price = $validated['productPrice'];
    $vatAmount = 0; $vatPercentage = 0; $vatMethodId = null; $vatMethodName = null; $vatMethodType = null;
    if ($request->vat_method_id) {
        $vatMethod = \App\Models\VatMethod::findOrFail($request->vat_method_id);
        $vatMethodId = $vatMethod->id;
        if ($vatMethod->type == 'Percentage') {
            $vatPercentage = $vatMethod->amount;
            $vatAmount = $price * $vatMethod->amount / 100;
        } else {
            // For Fixed type, calculate percentage: (fixed_amount / price) * 100
            $vatAmount = $vatMethod->amount;
            if ($price > 0) {
                $vatPercentage = ($vatMethod->amount / $price) * 100;
            }
        }
        $vatMethodName = $vatMethod->name;
        $vatMethodType = $vatMethod->type;
    }
    $expiryDate = null;
    if ($request->expiry_date) {
      $dt = \DateTime::createFromFormat('d/m/Y', $request->expiry_date);
      $expiryDate = $dt ? $dt->format('Y-m-d') : null;
    }
    $quantity = $request->quantity ?? 0;
    $costPrice = $request->costPrice ?? 0;

    DB::transaction(function () use ($request, $validated, $price, $vatAmount, $vatPercentage, $vatMethodId, $vatMethodName, $vatMethodType, $expiryDate, $quantity, $costPrice, $imageUrl, $product) {
      
      $product->update([
        'name' => $validated['productTitle'],
        'sku' => $validated['productSku'],
        'product_unit_sku' => $validated['productUnitSku'],
        'step_quantity' => $validated['step'],
        'description' => $request->productDescription ?? null,
        'price' => $price,
        'cost_price' => $costPrice,
        'wallet_credit' => $request->walletCredit ?? 0,
        'weight' => $request->weight ?? null,
        'rrp' => $request->rrp ?? null,
        'expiry_date' => $expiryDate,
        'image_url' => $imageUrl,
        'stock_quantity' => $quantity,
        'vat_percentage' => $vatPercentage,
        'vat_method_id' => $vatMethodId,
        'vat_amount' => $vatAmount,
        'vat_method_name' => $vatMethodName,
        'vat_method_type' => $vatMethodType,
        'unit_id' => $request->unit_id,
        'is_active' => $request->productStatus ?? 0,
      ]);

      // Update product brands
      ProductBrand::where('product_id', $request->id)->delete();
      foreach ($request->brands as $brand) {
        ProductBrand::create([
          'product_id' => $request->id,
          'brand_id' => $brand
        ]);
      }

      // Sync warehouse product
      WarehouseProductSyncService::sync($request->id, $quantity, $costPrice);
    });

    Toastr::success('Product updated successfully!');
    return redirect()->route('product.list');

  }

  public function add(){
    $data['brands'] = Brand::all();
    $data['vatMethods'] = VatMethod::where('status', 'Active')->orderBy('name')->get();
    $data['units'] = Unit::where('status', 'Active')->orderBy('name')->get();
    $settings = Setting::all()->pluck('value', 'key');
    $data['currencySymbol'] = $settings['currency_symbol'] ?? '₱';
    return view('content.product.add',$data);
  }

 public function ajaxList(Request $request)
{
    $query = Product::select([
        'id',
        'name as product_name', 
        'description',
        'sku',
        'price',
        'image_url',
        'is_active'
    ]);

    return DataTables::eloquent($query)
        ->filterColumn('product_name', function($query, $keyword) {
            $query->where('products.name', 'like', "%{$keyword}%");
        })
        ->filterColumn('sku', function($query, $keyword) {
            $query->where('products.sku', 'like', "%{$keyword}%");
        })
        ->filterColumn('price', function($query, $keyword) {
            $query->where('products.price', 'like', "%{$keyword}%");
        })
        ->order(function($query) use ($request) {
         
            if ($request->has('order')) {
                $columnIndex = $request->order[0]['column'];
                $dir = $request->order[0]['dir'];

                // Column index mapping
                switch ($columnIndex) {
                    case 2: // product_name
                        $query->orderBy('products.name', $dir);
                        break;
                    case 3: // sku
                        $query->orderBy('products.sku', $dir);
                        break;
                    case 4: // price
                        $query->orderBy('products.price', $dir);
                        break;
                    case 5: // price
                        $query->orderBy('products.is_active', $dir);
                        break;
                    default: // default fallback
                        $query->orderBy('products.id', 'desc');
                        break;
                }
            } else {
                $query->orderBy('products.id', 'desc'); // default
            }
        })
        ->addColumn('product_brand', function($product) {
            return Str::limit($product->description, 40);
        })
        ->toJson();
}


  public function searchAjax(Request $request)
  {
    $q = trim($request->get('q', ''));
    $limit = (int) $request->get('limit', 10);

    $query = Product::select(['id','name','sku','price','image_url','wallet_credit'])
      ->where('is_active',1);

    if ($q !== '') {
      $query->where(function($sub) use ($q) {
        $sub->where('name','like',"%{$q}%")
            ->orWhere('sku','like',"%{$q}%");
      });
    }

    $products = $query->orderBy('id','desc')->limit($limit)->get();

    return response()->json([
      'results' => $products->map(function($p){
        return [
          'id' => $p->id,
          'text' => $p->name . ' (' . $p->sku . ')',
          'price' => $p->price,
          'wallet_credit' => $p->wallet_credit,
          'image_url' => $p->image_url,
        ];
      })
    ]);
  }
 
  public function delete($id)
  {
    $product = Product::findOrFail($id);
    $product->delete();
    Toastr::success('Product deleted successfully!');
    return redirect()->back();
  }

  public function checkSku(Request $request)
  {
    $request->validate([
      'sku' => ['required','string'],
      'id' => ['nullable','integer']
    ]);

    $sku = trim($request->sku);
    $id = $request->id;

    $exists = Product::where('sku', $sku)
      ->when(!empty($id), function($q) use ($id) { $q->where('id', '!=', $id); })
      ->exists();

    return response()->json(['valid' => !$exists]);
  }

  public function checkUnitSku(Request $request)
  {
    $request->validate([
      'sku' => ['required','string'],
      'id' => ['nullable','integer']
    ]);

    $sku = trim($request->sku);
    $id = $request->id;

    $exists = Product::where('product_unit_sku', $sku)
      ->when(!empty($id), function($q) use ($id) { $q->where('id', '!=', $id); })
      ->exists();

    return response()->json(['valid' => !$exists]);
  }

  public function import(Request $request)
  {
    $request->validate([
      'importFile' => ['required', 'file', 'max:10240'], // 10MB max
    ], [
      'importFile.required' => 'Please select a file to import',
      'importFile.file' => 'The uploaded file is invalid',
      'importFile.max' => 'File size must not exceed 10MB',
    ]);
    
    // Custom validation for file type
    $file = $request->file('importFile');
    $extension = strtolower($file->getClientOriginalExtension());
    $allowedExtensions = ['csv', 'txt'];
    
    if (!in_array($extension, $allowedExtensions)) {
      return redirect()->back()
        ->withErrors(['importFile' => 'File must be in CSV format (.csv). If you have an Excel file, please convert it to CSV first.'])
        ->withInput();
    }
    
    // Check if file is actually an Excel file (ZIP signature)
    $fileContent = file_get_contents($file->getRealPath(), false, null, 0, 4);
    $isExcelFile = (substr($fileContent, 0, 2) === 'PK'); // Excel files start with ZIP signature
    
    if ($isExcelFile) {
      return redirect()->back()
        ->withErrors(['importFile' => 'The file appears to be an Excel file. Please convert it to CSV format first. In Excel: File > Save As > CSV (Comma delimited) (*.csv)'])
        ->withInput();
    }

    $results = [
      'success' => 0,
      'failed' => 0,
      'errors' => []
    ];

    try {
      // Read CSV file
      $rows = $this->readCsvFile($file);

      if (empty($rows)) {
        Toastr::error('No data found in the file or file is empty. Please ensure your file is a valid CSV file.');
        return redirect()->back();
      }

      // Get header row and remove it from data
      if (empty($rows)) {
        Toastr::error('File appears to be empty or could not be read. Please ensure it is a valid CSV file.');
        return redirect()->back();
      }
      
      $headers = array_shift($rows);
      
      // Validate that we got actual headers (not binary data)
      if (empty($headers) || (count($headers) === 1 && strlen(trim($headers[0])) < 3)) {
        Toastr::error('Could not read headers from file. The file may be corrupted or in an unsupported format. Please ensure you are uploading a CSV file (not Excel format).');
        return redirect()->back();
      }
      
      // Clean headers - remove BOM and trim
      $headers = array_map(function($header) {
        // Remove BOM characters
        $header = preg_replace('/\x{FEFF}/u', '', $header);
        // Remove any non-printable characters except spaces
        $header = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $header);
        // Trim whitespace
        $header = trim($header);
        return $header;
      }, $headers);
      
      // Filter out empty headers
      $headers = array_filter($headers, function($h) { return !empty(trim($h)); });
      $headers = array_values($headers); // Re-index array
      
      $headerMap = $this->mapHeaders($headers);

      // Validate headers (case-insensitive matching with alternative names)
      $requiredHeadersMap = [
        'Product Name' => ['Product Name', 'product name', 'PRODUCT NAME', 'Name', 'name', 'Product Title', 'product title'],
        'Product Code' => ['Product Code', 'product code', 'PRODUCT CODE', 'Code', 'code', 'SKU', 'sku', 'Product SKU'],
        'Product Unit Code' => ['Product Unit Code', 'product unit code', 'PRODUCT UNIT CODE', 'Unit Code', 'unit code', 'Product Unit SKU'],
        'Selling Price' => ['Selling Price', 'selling price', 'SELLING PRICE', 'Price', 'price', 'Sale Price'],
        'Status' => ['Status (1 = Active / 0 = Inactive)', 'Status', 'status', 'STATUS', 'Product Status', 'product status', 'Status (1 = Active / 0 = Inactive)'],
        'Brand' => ['Brand', 'brand', 'BRAND', 'Product Brand', 'product brand']
      ];
      
      // Optional headers map (for better matching)
      $optionalHeadersMap = [
        'Image' => ['Image', 'image', 'IMAGE', 'Product Image', 'product image', 'Image URL', 'image url', 'ImageUrl'],
        'Cost Price' => ['Cost Price (Optional)', 'Cost Price', 'cost price', 'COST PRICE', 'Cost', 'cost'],
        'Wallet Credit' => ['Wallet Credit (Optional)', 'Wallet Credit', 'wallet credit', 'WALLET CREDIT', 'Wallet', 'wallet'],
        'Weight (Kg)' => ['Weight (Kg) (Optional)', 'Weight (Kg)', 'weight (kg)', 'WEIGHT (KG)', 'Weight', 'weight', 'Weight (kg)', 'Weight(Kg)'],
        'RRP' => ['RRP (Optional)', 'RRP', 'rrp', 'Recommended Retail Price'],
        'Expiry Date' => ['Expiry Date (Optional)', 'Expiry Date', 'expiry date', 'EXPIRY DATE', 'Expiry', 'expiry', 'Exp Date', 'exp date'],
        'Quantity' => ['Quantity', 'quantity', 'QUANTITY', 'Qty', 'qty', 'Stock Quantity', 'stock quantity'],
        'Step Quantity' => ['Step Quantity', 'step quantity', 'STEP QUANTITY', 'Step', 'step'],
        'VAT Method' => ['VAT Method (Optional)', 'VAT Method', 'vat method', 'VAT METHOD', 'Vat Method', 'VAT', 'vat', 'Tax Method', 'tax method'],
        'Product Unit' => ['Product Unit', 'product unit', 'PRODUCT UNIT', 'Unit', 'unit'],
        'Description' => ['Description (Optional)', 'Description', 'description', 'DESCRIPTION', 'Desc', 'desc'],
        'Category' => ['Category', 'category', 'CATEGORY', 'Product Category', 'product category'],
        'Type ( Sub Category )' => ['Type ( Sub Category )', 'Type ( Sub Category )', 'Type (Sub Category)', 'Type', 'type', 'Sub Category', 'sub category', 'SubCategory', 'Subcategory'],
        'Brand Image' => ['Brand Image', 'brand image', 'BRAND IMAGE', 'Brand Image URL', 'brand image url', 'BrandImage'],
        'Brand Tags' => ['Brand Tags', 'brand tags', 'BRAND TAGS', 'Brand Tag', 'brand tag', 'Tags', 'tags']
      ];
      
      $missingHeaders = [];
      $foundHeaders = array_keys($headerMap);
      $normalizedHeaderMap = [];
      
      // Build normalized header map for required headers
      foreach ($requiredHeadersMap as $canonicalName => $variations) {
        $found = false;
        $foundIndex = null;
        
        // Check each variation
        foreach ($variations as $variation) {
          // Check exact match
          if (isset($headerMap[$variation])) {
            $normalizedHeaderMap[$canonicalName] = $headerMap[$variation];
            $found = true;
            break;
          }
          
          // Check case-insensitive match
          foreach ($foundHeaders as $foundHeader) {
            if (strcasecmp(trim($foundHeader), trim($variation)) === 0) {
              $normalizedHeaderMap[$canonicalName] = $headerMap[$foundHeader];
              $found = true;
              break 2;
            }
          }
        }
        
        if (!$found) {
          $missingHeaders[] = $canonicalName;
        }
      }
      
      // Build normalized header map for optional headers (like Image)
      foreach ($optionalHeadersMap as $canonicalName => $variations) {
        $found = false;
        
        // Check each variation
        foreach ($variations as $variation) {
          // Check exact match
          if (isset($headerMap[$variation])) {
            $normalizedHeaderMap[$canonicalName] = $headerMap[$variation];
            $found = true;
            break;
          }
          
          // Check case-insensitive match
          foreach ($foundHeaders as $foundHeader) {
            if (strcasecmp(trim($foundHeader), trim($variation)) === 0) {
              $normalizedHeaderMap[$canonicalName] = $headerMap[$foundHeader];
              $found = true;
              break 2;
            }
          }
        }
      }
      
      // Update headerMap with normalized names
      $headerMap = $normalizedHeaderMap;

      if (!empty($missingHeaders)) {
        $errorMsg = 'Missing required columns: ' . implode(', ', $missingHeaders);
        $errorMsg .= '<br><strong>Found headers in file:</strong> ' . implode(', ', array_map(function($h) { return '"' . $h . '"'; }, array_slice($foundHeaders, 0, 20)));
        if (count($foundHeaders) > 20) {
          $errorMsg .= ' (and ' . (count($foundHeaders) - 20) . ' more)';
        }
        Toastr::error($errorMsg, '', ['timeOut' => 10000]);
        return redirect()->back();
      }

      // Step 1: Validate ALL rows first (don't insert anything yet)
      $validatedProducts = [];
      $allErrors = [];
      
      foreach ($rows as $rowIndex => $row) {
        $rowNumber = $rowIndex + 2; // +2 because header is row 1, and array is 0-indexed
        
        try {
          $productData = $this->mapRowToProductData($row, $headerMap, $rowNumber);
          
          // Validate product data using same rules as create method
          $validationResult = $this->validateProductData($productData);
          
          if ($validationResult['valid']) {
            // Store validated product data for later insertion
            $validatedProducts[] = $productData;
          } else {
            $allErrors[] = "Row {$rowNumber}: " . implode(', ', $validationResult['errors']);
          }
        } catch (\Exception $e) {
          $allErrors[] = "Row {$rowNumber}: " . $e->getMessage();
        }
      }

      // Step 2: If there are any validation errors, show them and don't insert anything
      if (!empty($allErrors)) {
        $errorMessage = "Import failed. Please fix the following errors and try again:<br>";
        if (count($allErrors) <= 20) {
          $errorMessage .= "<ul style='margin: 10px 0; padding-left: 20px;'>";
          foreach ($allErrors as $error) {
            $errorMessage .= "<li>" . htmlspecialchars($error) . "</li>";
          }
          $errorMessage .= "</ul>";
        } else {
          $errorMessage .= "<ul style='margin: 10px 0; padding-left: 20px;'>";
          foreach (array_slice($allErrors, 0, 20) as $error) {
            $errorMessage .= "<li>" . htmlspecialchars($error) . "</li>";
          }
          $errorMessage .= "</ul>";
          $errorMessage .= "<strong>And " . (count($allErrors) - 20) . " more error(s).</strong>";
        }
        Toastr::error($errorMessage, '', ['timeOut' => 15000, 'escapeHtml' => false]);
        return redirect()->back();
      }

      // Step 3: All rows passed validation - now insert all records in a single transaction
      if (empty($validatedProducts)) {
        Toastr::warning('No valid products found to import.');
        return redirect()->back();
      }

      try {
        DB::beginTransaction();
        
        foreach ($validatedProducts as $productData) {
          $this->createProductFromImport($productData);
          $results['success']++;
        }
        
        DB::commit();
        
        Toastr::success("Successfully imported {$results['success']} product(s).");
      } catch (\Exception $e) {
        DB::rollBack();
        Toastr::error('Import failed: ' . $e->getMessage());
      }

    } catch (\Exception $e) {
      Toastr::error('Import failed: ' . $e->getMessage());
    }

    return redirect()->route('product.list');
  }

  private function readCsvFile($file)
  {
    $rows = [];
    // Try to detect encoding and handle BOM
    $content = file_get_contents($file->getRealPath());
    
    // Remove BOM if present
    $content = preg_replace('/\x{FEFF}/u', '', $content);
    
    // Try to convert to UTF-8 if not already
    if (!mb_check_encoding($content, 'UTF-8')) {
      $content = mb_convert_encoding($content, 'UTF-8', 'auto');
    }
    
    // Write cleaned content to temp file
    $tempFile = tempnam(sys_get_temp_dir(), 'csv_import_');
    file_put_contents($tempFile, $content);
    
    $handle = fopen($tempFile, 'r');
    
    if ($handle !== false) {
      while (($row = fgetcsv($handle, 1000, ',')) !== false) {
        $rows[] = $row;
      }
      fclose($handle);
    }
    
    // Clean up temp file
    if (file_exists($tempFile)) {
      unlink($tempFile);
    }
    
    return $rows;
  }

  private function readExcelFile($file)
  {
    // Excel files cannot be read directly with fgetcsv
    // This method is kept for future use with PhpSpreadsheet library
    // For now, users should convert Excel to CSV
    return [];
  }

  private function mapHeaders($headers)
  {
    $map = [];
    foreach ($headers as $index => $header) {
      // Already cleaned in import method, but ensure trim here too
      $header = trim($header);
      // Remove BOM if still present
      $header = preg_replace('/\x{FEFF}/u', '', $header);
      if (!empty($header)) {
        $map[$header] = $index;
      }
    }
    return $map;
  }

  private function mapRowToProductData($row, $headerMap, $rowNumber)
  {
    $getValue = function($headerName) use ($row, $headerMap) {
      // Try exact match first
      if (isset($headerMap[$headerName])) {
        $index = $headerMap[$headerName];
        if (isset($row[$index])) {
          $value = $row[$index];
          // Return trimmed string or the value itself
          $trimmed = is_string($value) ? trim($value) : (string)$value;
          // Return null for empty strings, otherwise return the value
          return $trimmed === '' ? null : $trimmed;
        }
      }
      
      // Try case-insensitive match
      foreach ($headerMap as $mapHeader => $index) {
        if (strcasecmp(trim($mapHeader), trim($headerName)) === 0) {
          if (isset($row[$index])) {
            $value = $row[$index];
            $trimmed = is_string($value) ? trim($value) : (string)$value;
            return $trimmed === '' ? null : $trimmed;
          }
        }
      }
      
      return null;
    };

    // Map sheet columns to product data
    $data = [
      'productTitle' => $getValue('Product Name'),
      'productSku' => $getValue('Product Code'),
      'productUnitSku' => $getValue('Product Unit Code'),
      'productPrice' => $getValue('Selling Price'),
      'costPrice' => $getValue('Cost Price') ?: $getValue('Cost Price (Optional)') ?: $getValue('cost price') ?: $getValue('Cost'),
      'walletCredit' => $getValue('Wallet Credit') ?: $getValue('Wallet Credit (Optional)') ?: $getValue('wallet credit') ?: $getValue('Wallet'),
      'weight' => $getValue('Weight (Kg)') ?: $getValue('Weight (Kg) (Optional)') ?: $getValue('weight (kg)') ?: $getValue('Weight') ?: $getValue('Weight(Kg)'),
      'rrp' => $getValue('RRP') ?: $getValue('RRP (Optional)') ?: $getValue('rrp') ?: $getValue('Recommended Retail Price'),
      'expiry_date' => $getValue('Expiry Date') ?: $getValue('Expiry Date (Optional)') ?: $getValue('expiry date') ?: $getValue('Expiry') ?: $getValue('Exp Date'),
      'quantity' => $getValue('Quantity') ?: $getValue('quantity') ?: $getValue('Qty') ?: $getValue('Stock Quantity'),
      'stepQuantity' => $getValue('Step Quantity') ?: $getValue('step quantity') ?: $getValue('Step') ?: $getValue('step'),
      'productUnit' => $getValue('Product Unit') ?: $getValue('product unit') ?: $getValue('Unit'),
      'vatMethod' => $getValue('VAT Method (Optional)') ?: $getValue('VAT Method') ?: $getValue('vat method') ?: $getValue('VAT') ?: $getValue('Tax Method'),
      'productDescription' => $getValue('Description') ?: $getValue('Description (Optional)') ?: $getValue('description') ?: $getValue('Desc'),
      'productStatus' => $getValue('Status'),
      'category' => $getValue('Category'),
      'type' => $getValue('Type ( Sub Category )') ?: $getValue('Type (Sub Category)') ?: $getValue('Type') ?: $getValue('Sub Category') ?: $getValue('SubCategory'),
      'brand' => $getValue('Brand'),
      'brandImage' => $getValue('Brand Image') ?: $getValue('Brand Image URL') ?: $getValue('brand image') ?: $getValue('BrandImage'),
      'brandTags' => $getValue('Brand Tags') ?: $getValue('brand tags') ?: $getValue('Brand Tag') ?: $getValue('brand tag') ?: $getValue('Tags') ?: $getValue('tags'),
      'productImageUrl' => $getValue('Image') ?: $getValue('Image URL') ?: $getValue('image') ?: $getValue('image url') ?: $getValue('Product Image'),
    ];

    // Convert scientific notation to regular number for Product Code
    if ($data['productSku'] && (stripos($data['productSku'], 'E+') !== false || stripos($data['productSku'], 'E-') !== false)) {
      $data['productSku'] = (string)(float)$data['productSku'];
    }
    if ($data['productUnitSku'] && (stripos($data['productUnitSku'], 'E+') !== false || stripos($data['productUnitSku'], 'E-') !== false)) {
      $data['productUnitSku'] = (string)(float)$data['productUnitSku'];
    }

    return $data;
  }

  private function validateProductData($data)
  {
    $errors = [];

    // Required fields
    if (empty($data['productTitle'])) {
      $errors[] = 'Product Name is required';
    }
    if (empty($data['productSku'])) {
      $errors[] = 'Product Code is required';
    } elseif (Product::where('sku', $data['productSku'])->exists()) {
      $errors[] = 'Product Code already exists';
    }
    if (empty($data['productUnitSku'])) {
      $errors[] = 'Product Unit Code is required';
    } elseif (Product::where('product_unit_sku', $data['productUnitSku'])->exists()) {
      $errors[] = 'Product Unit Code already exists';
    }
    if (empty($data['productPrice'])) {
      $errors[] = 'Selling Price is required';
    } elseif (!is_numeric($data['productPrice']) || $data['productPrice'] < 0) {
      $errors[] = 'Selling Price must be a valid number >= 0';
    }
    // Handle multiple brands (comma-separated)
    if (empty($data['brand'])) {
      $errors[] = 'Brand is required';
    } else {
      // Split brands by comma and trim each
      $brandNames = array_map('trim', explode(',', $data['brand']));
      $brandNames = array_filter($brandNames, function($name) { return !empty($name); }); // Remove empty values
      
      if (empty($brandNames)) {
        $errors[] = 'At least one brand is required';
      }
      // Note: Brands will be created if they don't exist, so we don't validate existence here
    }
    
    // Brand Image URL validation (if provided)
    if (!empty($data['brandImage'])) {
      $url = trim($data['brandImage']);
      // Check if it's a valid URL format (starts with http/https or is a valid URL)
      if (!preg_match('/^https?:\/\//i', $url) && !filter_var($url, FILTER_VALIDATE_URL)) {
        // Don't add error if it's just missing http:// prefix - we'll add it in createProductFromImport
        // Only error if it's clearly not a URL
        if (strpos($url, '://') !== false || (strpos($url, '.') === false && strpos($url, '/') === false)) {
          $errors[] = 'Brand Image must be a valid URL';
        }
      }
    }

    // Optional numeric fields
    if (!empty($data['costPrice']) && (!is_numeric($data['costPrice']) || $data['costPrice'] < 0)) {
      $errors[] = 'Cost Price must be a valid number >= 0';
    }
    if (!empty($data['weight']) && !is_numeric($data['weight'])) {
      $errors[] = 'Weight must be a number';
    }
    if (!empty($data['rrp']) && !is_numeric($data['rrp'])) {
      $errors[] = 'RRP must be a number';
    }
    if (!empty($data['walletCredit']) && (!is_numeric($data['walletCredit']) || $data['walletCredit'] < 0)) {
      $errors[] = 'Wallet Credit must be a valid number >= 0';
    }

    // Status validation - allow "0" as valid value (inactive)
    if (isset($data['productStatus']) && $data['productStatus'] !== '' && $data['productStatus'] !== null) {
      $status = trim($data['productStatus']);
      if (!in_array($status, ['0', '1', 'Active', 'Inactive'])) {
        $errors[] = 'Status must be 0, 1, Active, or Inactive';
      }
    } else {
      $errors[] = 'Status is required';
    }

    // Expiry date validation
    if (!empty($data['expiry_date'])) {
      $dateStr = trim($data['expiry_date']);
      // Try to parse date in dd/mm/yyyy or dd-mm-yyyy format
      $date = \DateTime::createFromFormat('d/m/Y', $dateStr);
      if (!$date) {
        $date = \DateTime::createFromFormat('d-m-Y', $dateStr);
      }
      if (!$date) {
        $errors[] = 'Expiry Date must be in dd/mm/yyyy or dd-mm-yyyy format';
      }
    }

    // Image URL validation (more lenient - just check if it's not empty and looks like a URL)
    if (!empty($data['productImageUrl'])) {
      $url = trim($data['productImageUrl']);
      // Check if it's a valid URL format (starts with http/https or is a valid URL)
      if (!preg_match('/^https?:\/\//i', $url) && !filter_var($url, FILTER_VALIDATE_URL)) {
        // Don't add error if it's just missing http:// prefix - we'll add it in createProductFromImport
        // Only error if it's clearly not a URL
        if (strpos($url, '://') !== false || (strpos($url, '.') === false && strpos($url, '/') === false)) {
          $errors[] = 'Image must be a valid URL';
        }
      }
    }

    // Unit validation - removed error, will be created if not exists
    // VAT Method validation - removed error, will be created if not exists

    return [
      'valid' => empty($errors),
      'errors' => $errors
    ];
  }

  private function createProductFromImport($data)
  {
    // Handle Category and Type (Sub Category) - create if not exists
    $categoryId = null;
    $subCategoryId = null;
    
    // Scenario 1: Category is provided
    if (!empty($data['category'])) {
      $categoryName = trim($data['category']);
      $category = Category::where('name', $categoryName)
        ->whereNull('parent_id')
        ->first();
      
      if (!$category) {
        // Create main category with Active status
        $category = Category::create([
          'name' => $categoryName,
          'parent_id' => null,
          'is_active' => 1,
          'sort_order' => 1
        ]);
      }
      $categoryId = $category->id;
      
      // Then, handle subcategory (Type) if provided
      if (!empty($data['type'])) {
        $typeName = trim($data['type']);
        $subCategory = Category::where('name', $typeName)
          ->where('parent_id', $categoryId)
          ->first();
        
        if (!$subCategory) {
          // Create subcategory with Active status
          $subCategory = Category::create([
            'name' => $typeName,
            'parent_id' => $categoryId,
            'is_active' => 1,
            'sort_order' => 1
          ]);
        }
        $subCategoryId = $subCategory->id;
      }
    } 
    // Scenario 2: Category is blank but Type (Sub Category) is provided
    elseif (!empty($data['type'])) {
      $typeName = trim($data['type']);
      // Search for Type as a subcategory (must have a parent)
      $subCategory = Category::where('name', $typeName)
        ->whereNotNull('parent_id')
        ->first();
      
      if (!$subCategory) {
        throw new \Exception("Type (Sub Category) '{$typeName}' does not exist and Category is blank.");
      }
      
      $subCategoryId = $subCategory->id;
      $categoryId = $subCategory->parent_id;
    }
    
    // Determine which category to use for brand (prefer subcategory if available)
    $brandCategoryId = $subCategoryId ?? $categoryId;
    
    // Get brand IDs (support multiple brands comma-separated)
    $brandNames = array_map('trim', explode(',', $data['brand']));
    $brandNames = array_filter($brandNames, function($name) { return !empty($name); }); // Remove empty values
    
    $brandIds = [];
    foreach ($brandNames as $brandName) {
      $brand = Brand::where('name', $brandName)->first();
      
      if (!$brand) {
        // Brand doesn't exist - need to create it
        // But we need a category to bind it to
        
        // Scenario: Category='', Type='', Brand='c' - Error case
        if (empty($brandCategoryId)) {
          throw new \Exception("Brand '{$brandName}' does not exist and cannot be created because no Category or Type (Sub Category) is provided.");
        }
        
        // Create brand if it doesn't exist
        $brandImageUrl = null;
        if (!empty($data['brandImage'])) {
          $url = trim($data['brandImage']);
          // More lenient URL validation - check if it starts with http:// or https://
          if (preg_match('/^https?:\/\//i', $url)) {
            $brandImageUrl = $url;
          } elseif (filter_var($url, FILTER_VALIDATE_URL)) {
            $brandImageUrl = $url;
          } else {
            // If it doesn't start with http, try adding it
            if (strpos($url, '://') === false && !empty($url)) {
              $brandImageUrl = 'https://' . ltrim($url, '/');
            }
          }
        }
        
        // Create brand with Active status
        $brand = Brand::create([
          'name' => $brandName,
          'image' => $brandImageUrl,
          'is_active' => 1
        ]);
        
        // Link brand to category
        if ($brandCategoryId) {
          // Check if link already exists
          $existingLink = BrandCategory::where('brand_id', $brand->id)
            ->where('category_id', $brandCategoryId)
            ->first();
          
          if (!$existingLink) {
            BrandCategory::create([
              'brand_id' => $brand->id,
              'category_id' => $brandCategoryId
            ]);
          }
        }
        
        // Handle brand tags for new brand
        if (!empty($data['brandTags'])) {
          $tagNames = array_map('trim', explode(',', $data['brandTags']));
          $tagNames = array_filter($tagNames, function($name) { return !empty($name); }); // Remove empty values
          
          foreach ($tagNames as $tagName) {
            $tag = Tag::updateOrCreate(
              ['name' => $tagName, 'type' => 'categorical'],
              ['is_active' => 1]
            );
            
            BrandTag::updateOrCreate([
              'brand_id' => $brand->id,
              'tag_id' => $tag->id
            ]);
          }
        }
      } else {
        // Brand exists - ensure it's linked to the category if category exists
        if ($brandCategoryId) {
          $existingLink = BrandCategory::where('brand_id', $brand->id)
            ->where('category_id', $brandCategoryId)
            ->first();
          
          if (!$existingLink) {
            BrandCategory::create([
              'brand_id' => $brand->id,
              'category_id' => $brandCategoryId
            ]);
          }
        }
        
        // Handle brand tags for existing brand - check if tags exist, if not create them
        if (!empty($data['brandTags'])) {
          $tagNames = array_map('trim', explode(',', $data['brandTags']));
          $tagNames = array_filter($tagNames, function($name) { return !empty($name); }); // Remove empty values
          
          foreach ($tagNames as $tagName) {
            $tag = Tag::updateOrCreate(
              ['name' => $tagName, 'type' => 'categorical'],
              ['is_active' => 1]
            );
            
            // Check if brand tag link exists, if not create it
            $existingBrandTag = BrandTag::where('brand_id', $brand->id)
              ->where('tag_id', $tag->id)
              ->first();
            
            if (!$existingBrandTag) {
              BrandTag::create([
                'brand_id' => $brand->id,
                'tag_id' => $tag->id
              ]);
            }
          }
        }
      }
      
      $brandIds[] = $brand->id;
    }
    
    if (empty($brandIds)) {
      throw new \Exception('No valid brands found');
    }

    // Get unit ID if provided, create if doesn't exist
    $unitId = null;
    if (!empty($data['productUnit'])) {
      $unitName = trim($data['productUnit']);
      // Check if unit exists (regardless of status)
      $unit = Unit::where('name', $unitName)->first();
      if (!$unit) {
        // Create new unit with Active status
        $unit = Unit::create([
          'name' => $unitName,
          'status' => 'Active'
        ]);
      }
      $unitId = $unit->id;
    }

    // Parse status - allow "0" as valid value (inactive)
    $status = 0;
    if (isset($data['productStatus']) && $data['productStatus'] !== '' && $data['productStatus'] !== null) {
      $statusStr = trim($data['productStatus']);
      if (in_array($statusStr, ['1', 'Active'])) {
        $status = 1;
      } elseif (in_array($statusStr, ['0', 'Inactive'])) {
        $status = 0;
      }
    }

    // Parse expiry date
    $expiryDate = null;
    if (!empty($data['expiry_date'])) {
      $dateStr = trim($data['expiry_date']);
      $date = \DateTime::createFromFormat('d/m/Y', $dateStr);
      if (!$date) {
        $date = \DateTime::createFromFormat('d-m-Y', $dateStr);
      }
      if ($date) {
        $expiryDate = $date->format('Y-m-d');
      }
    }

    // Set step quantity (default to 1 if not provided or less than 1)
    $step = 1;
    if (!empty($data['stepQuantity'])) {
      $stepValue = is_numeric($data['stepQuantity']) ? (int)$data['stepQuantity'] : 1;
      $step = $stepValue >= 1 ? $stepValue : 1;
    }

    $price = (float)$data['productPrice'];
    
    // Handle VAT Method - create if doesn't exist
    $vatAmount = 0;
    $vatPercentage = 0;
    $vatMethodId = null;
    $vatMethodName = null;
    $vatMethodType = null;
    if (!empty($data['vatMethod'])) {
      $vatMethodName = trim($data['vatMethod']);
      $vatMethod = VatMethod::where('name', $vatMethodName)->first();
      
      if (!$vatMethod) {
        // Extract percentage from name if it contains a number (e.g., "20%", "VAT 15%")
        $vatAmountValue = 0;
        if (preg_match('/(\d+(?:\.\d+)?)\s*%/', $vatMethodName, $matches)) {
          $vatAmountValue = (float)$matches[1];
        }
        
        // Create new VAT method with type "Percentage" always
        $vatMethod = VatMethod::create([
          'name' => $vatMethodName,
          'type' => 'Percentage',
          'amount' => $vatAmountValue,
          'status' => 'Active'
        ]);
      }
      
      $vatMethodId = $vatMethod->id;
      
      // Calculate VAT amount and percentage
      if ($vatMethod->type == 'Percentage') {
        $vatPercentage = $vatMethod->amount;
        $vatAmount = $price * $vatMethod->amount / 100;
      } else {
        // For Fixed type, calculate percentage: (fixed_amount / price) * 100
        $vatAmount = $vatMethod->amount;
        if ($price > 0) {
          $vatPercentage = ($vatMethod->amount / $price) * 100;
        }
      }
      $vatMethodName = $vatMethod->name;
      $vatMethodType = $vatMethod->type;
    }
    
    // Handle numeric fields - convert empty strings to null/0, handle "0" as valid value
    $costPrice = 0;
    if (isset($data['costPrice']) && $data['costPrice'] !== '' && $data['costPrice'] !== null) {
      $costPrice = is_numeric($data['costPrice']) ? (float)$data['costPrice'] : 0;
    }
    
    $quantity = 0;
    if (isset($data['quantity']) && $data['quantity'] !== '' && $data['quantity'] !== null) {
      $quantity = is_numeric($data['quantity']) ? (int)$data['quantity'] : 0;
    }
    
    $walletCredit = 0;
    if (isset($data['walletCredit']) && $data['walletCredit'] !== '' && $data['walletCredit'] !== null) {
      $walletCredit = is_numeric($data['walletCredit']) ? (float)$data['walletCredit'] : 0;
    }
    
    $weight = null;
    if (isset($data['weight']) && $data['weight'] !== '' && $data['weight'] !== null && trim($data['weight']) !== '') {
      $weight = is_numeric($data['weight']) ? (float)$data['weight'] : null;
    }
    
    $rrp = null;
    if (isset($data['rrp']) && $data['rrp'] !== '' && $data['rrp'] !== null && trim($data['rrp']) !== '') {
      $rrp = is_numeric($data['rrp']) ? (float)$data['rrp'] : null;
    }

    // Validate and set image URL
    $imageUrl = null;
    if (!empty($data['productImageUrl'])) {
      $url = trim($data['productImageUrl']);
      // More lenient URL validation - check if it starts with http:// or https://
      if (preg_match('/^https?:\/\//i', $url)) {
        $imageUrl = $url;
      } elseif (filter_var($url, FILTER_VALIDATE_URL)) {
        $imageUrl = $url;
      } else {
        // If it doesn't start with http, try adding it
        if (strpos($url, '://') === false && !empty($url)) {
          $imageUrl = 'https://' . ltrim($url, '/');
        }
      }
    }

    // Note: Transaction is handled at the import level for all-or-nothing behavior
    $product = Product::create([
      'name' => $data['productTitle'],
      'sku' => $data['productSku'],
      'product_unit_sku' => $data['productUnitSku'],
      'step_quantity' => $step,
      'description' => $data['productDescription'] ?? null,
      'price' => $price,
      'cost_price' => $costPrice,
      'wallet_credit' => $walletCredit,
      'weight' => $weight,
      'rrp' => $rrp,
      'expiry_date' => $expiryDate,
      'image_url' => $imageUrl,
        'stock_quantity' => $quantity,
        'vat_percentage' => $vatPercentage,
        'vat_method_id' => $vatMethodId,
        'vat_amount' => $vatAmount,
        'vat_method_name' => $vatMethodName,
        'vat_method_type' => $vatMethodType,
        'unit_id' => $unitId,
      'is_active' => $status,
    ]);

    // Create product brands (multiple brands supported)
    foreach ($brandIds as $brandId) {
      ProductBrand::create([
        'product_id' => $product->id,
        'brand_id' => $brandId
      ]);
    }

    // Sync warehouse product
    WarehouseProductSyncService::sync($product->id, $quantity, $costPrice);
  }

  public function downloadSample()
  {
    $headers = [
      'SR NO.',
      'Product Name',
      'Product Code',
      'Product Unit Code',
      'Selling Price',
      'Cost Price (Optional)',
      'Wallet Credit (Optional)',
      'Weight (Kg) (Optional)',
      'RRP (Optional)',
      'Expiry Date (Optional)',
      'Quantity',
      'Product Unit',
      'VAT Method (Optional)',
      'Description (Optional)',
      'Status (1 = Active / 0 = Inactive)',
      'Image',
      'Category',
      'Type ( Sub Category )',
      'Brand',
      'Brand Image',
      'Step Quantity (Optional)',
      'Brand Tags (Optional)'
    ];

    $sampleData = [
      [
        '1',
        'Hayati Pro Ultra+ 25K Prefilled Pod Kit [Blackcurrant Cotton K - Blue Raspberry / 20mg]',
        '6936330000000',
        '6936330000000',
        '32.00',
        '0.00',
        '0.00',
        '0.00',
        '0.00',
        '26-11-2026',
        '500',
        'Box QTY 5',
        '20%',
        '',
        '1',
        'https://aidemo.in/nwb2b/admin/public/storage/products/jihfqWToNwczA0CK6.JH19x9NSrFCyVAOv07.JtfGv.jpg',
        'Electronics',
        'Vape Pods',
        'Hayati Pro Ultra 25K Prefilled Pods',
        'https://aidemo.in/nwb2b/admin/public/storage/brands/brand-image.jpg',
        '1',
        'Premium, Vape, Pods'
      ]
    ];

    $filename = 'sample-products-import.csv';
    $handle = fopen('php://temp', 'r+');
    
    // Add BOM for UTF-8 Excel compatibility
    fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write headers
    fputcsv($handle, $headers);
    
    // Write sample data
    foreach ($sampleData as $row) {
      fputcsv($handle, $row);
    }
    
    rewind($handle);
    $csv = stream_get_contents($handle);
    fclose($handle);

    return response($csv)
      ->header('Content-Type', 'text/csv; charset=UTF-8')
      ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
  }
}
