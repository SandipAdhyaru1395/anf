<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\BrandCategory;
use App\Models\BrandTag;
use App\Models\Tag;
use App\traits\BulkDeletes;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Setting;
use App\Jobs\SyncPlanufacProductsJob;
use App\Services\Planufac\PlanufacProductSyncService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
  use BulkDeletes;
  
  protected $model = Brand::class;
  
  public function index()
  {
    $data['total_brands_count'] = Brand::all()->count();
    $data['active_brands_count'] = Brand::where('is_active', 1)->count();
    $data['inactive_brands_count'] = Brand::where('is_active', 0)->count();

    return view('content.brand.list', $data);
  }

  public function ajaxList(Request $request)
  {
    $query = Brand::select([
      'brands.id',
      'brands.name as brand',
      'brands.image',
      'brands.is_active',
      'brands.erp_brand_id',
    ])->with('categories');

    return DataTables::eloquent($query)
      ->filter(function ($query) use ($request) {
        $searchValue = $request->get('search')['value'] ?? '';
        if (!empty($searchValue)) {
          $query->where(function ($q) use ($searchValue) {
            $q->where('brands.name', 'like', "%{$searchValue}%")
              ->orWhereHas('categories', function ($categoryQuery) use ($searchValue) {
                $categoryQuery->where('categories.name', 'like', "%{$searchValue}%");
              });
          });
        }
      })
      ->filterColumn('brand', function ($query, $keyword) {
        $query->where('brands.name', 'like', "%{$keyword}%");
      })
      ->order(function ($query) use ($request) {
        // Only Brand column sortable
        if ($request->has('order')) {
          $colIndex = $request->order[0]['column'];
          $dir = $request->order[0]['dir'];

          if ($colIndex == 2) {
            $query->orderBy('brands.name', $dir);
          }else if($colIndex == 4){
            $query->orderBy('brands.is_active', $dir);
          } else {
            $query->orderBy('brands.id', 'desc'); // default for others
          }
        } else {
          $query->orderBy('brands.id', 'desc'); // default latest by id
        }
      })
      ->addColumn('categories', function ($brand) {
        return $brand->categories && $brand->categories->isNotEmpty()
          ? $brand->categories->pluck('name')->implode(', ')
          : '-';
      })
      ->toJson();
  }

  public function syncPlanufacBrands(Request $request)
  {
    try {
      $validated = $request->validate([
        'brand_id' => ['required', 'string', 'max:2000'],
      ]);

      $missing = [];
      $settings = Setting::whereIn('key', ['planufac_base_url', 'planufac_email', 'planufac_password'])
        ->get(['key', 'value'])
        ->pluck('value', 'key');

      $baseUrl = trim((string) ($settings->get('planufac_base_url') ?? ''));
      $email = trim((string) ($settings->get('planufac_email') ?? ''));

      $passwordEnc = $settings->get('planufac_password');
      $password = '';
      if (is_string($passwordEnc) && $passwordEnc !== '') {
        try {
          $password = trim((string) Crypt::decryptString($passwordEnc));
        } catch (\Throwable $e) {
          $password = '';
        }
      }

      if ($baseUrl === '') {
        $missing[] = 'Base URL';
      }
      if ($email === '') {
        $missing[] = 'Email';
      }
      if ($password === '') {
        $missing[] = 'Password';
      }

      if (!empty($missing)) {
        return response()->json([
          'queued' => false,
          'message' => 'Planufac ERP is not configured. Missing: ' . implode(', ', $missing) . '. Please set it in Settings → Planufac ERP.',
        ], 422);
      }

      $parts = array_values(array_filter(array_map('trim', explode(',', $validated['brand_id'])), static fn ($s) => $s !== ''));
      if (count($parts) === 0) {
        return response()->json([
          'queued' => false,
          'message' => 'No valid ERP brand IDs in brand_id.',
        ], 422);
      }

      foreach ($parts as $p) {
        if (!ctype_digit($p)) {
          return response()->json([
            'queued' => false,
            'message' => 'Each brand_id value must be a numeric ERP brand ID.',
          ], 422);
        }
      }

      $brandIdCsv = implode(',', $parts);

      SyncPlanufacProductsJob::dispatch($brandIdCsv);

      return response()->json([
        'queued' => true,
        'message' => 'Sync started in background for the selected ERP brands. Refresh the product list shortly to see updates.',
        'last_sync' => Cache::get(PlanufacProductSyncService::CACHE_LAST_SYNC_KEY),
      ], 202);
    } catch (\Throwable $e) {
      return response()->json([
        'queued' => false,
        'message' => $e->getMessage(),
      ], 500);
    }
  }

  public function changeStatus($id)
  {
    $product = Product::findOrFail($id);
    $product->is_published = !$product->is_published;
    $product->save();

    Toastr::success('Product status changed successfully!');
    return redirect()->back();
  }

  public function add()
  {

    $data['brands'] = Brand::orderBy('id', 'desc')->get();
    $data['categories'] = Category::with('children')->whereNull('parent_id')->orderBy('id', 'desc')->get();

    return view('content.brand.add', $data);
  }

  public function create(Request $request)
  {

    $validated = $request->validate([
      'brandTitle' => ['required'],
      'brandImage' => [
        'nullable',
        function ($attribute, $value, $fail) use ($request) {
          $imageUrl = trim($request->input('brandImageUrl', ''));
          // If no file uploaded and no URL provided, require at least one
          if (!$request->hasFile('brandImage') && empty($imageUrl)) {
            $fail('Either an image file or image URL is required.');
          }
          // If file is provided, validate it
          if ($request->hasFile('brandImage')) {
            $file = $request->file('brandImage');
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
      'brandImageUrl' => [
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
          if (empty($trimmedValue) && !$request->hasFile('brandImage')) {
            $fail('Either an image file or image URL is required.');
          }
        },
      ],
      'categories' => ['required'],
    ], [
      'brandTitle.required' => 'Name is required',
      'brandImageUrl.max' => 'Image URL must not exceed 2048 characters',
      'categories.required' => 'Category is required',
    ]);

    // Determine image URL: use uploaded file if provided, otherwise use URL input
    $imageUrl = null;
    if ($request->hasFile('brandImage')) {
      $path = $request->file('brandImage')->store('brands', 'public');
      $imageUrl = asset('storage/' . $path);
    } elseif (!empty($request->brandImageUrl)) {
      $imageUrl = $request->brandImageUrl;
    }

    $brand = Brand::create([
      'name' => $validated['brandTitle'],
      'image' => $imageUrl,
      'is_active' => $request->brandStatus
    ]);

    if ($request->brandTags != null) {

      $brandTags = json_decode($request->brandTags, true); // decode as array

      foreach ($brandTags as $tag) {

        $tag = Tag::updateOrCreate([
          'name' => $tag['value'],
          'type' => 'categorical'
        ]);

        BrandTag::updateOrCreate([
          'tag_id' => $tag->id,
          'brand_id' => $brand->id
        ]);
      }
    }

    foreach ($request->categories as $category) {
      BrandCategory::create([
        'brand_id' => $brand->id,
        'category_id' => $category
      ]);
    }


    Toastr::success('Brand added successfully!');
    return redirect()->route('brand.list');
  }

  public function edit($id)
  {

    $data['brand'] = Brand::findOrFail($id);

    $data['categories'] = Category::with('children')->whereNull('parent_id')->orderBy('id', 'desc')->get();

    $data['brands'] = Brand::orderBy('id', 'desc')->get();

    $data['brandCategories'] = BrandCategory::where('brand_id', $id)->pluck('category_id')->toArray();

    $tag_ids = BrandTag::where('brand_id', $id)->pluck('tag_id')->toArray();

    $brandTags = Tag::select('id', 'name')->whereIn('id', $tag_ids)->get();

    $data['brandTags'] = '';

    if ($brandTags->isNotEmpty()) {
      $data['brandTags'] = $brandTags->implode('name', ', ');
    }

    return view('content.brand.edit', $data);
  }

  public function update(Request $request)
  {

    $brand = Brand::findOrFail($request->id);
    $hasExistingImage = !empty($brand->image);

    $validated = $request->validate([
      'brandTitle' => ['required'],
      'brandImage' => [
        'nullable',
        function ($attribute, $value, $fail) use ($request, $hasExistingImage) {
          $imageUrl = trim($request->input('brandImageUrl', ''));
          // If no file uploaded, no URL provided, and no existing image, require at least one
          if (!$request->hasFile('brandImage') && empty($imageUrl) && !$hasExistingImage) {
            $fail('Either an image file or image URL is required.');
          }
          // If file is provided, validate it
          if ($request->hasFile('brandImage')) {
            $file = $request->file('brandImage');
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
      'brandImageUrl' => [
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
          if (empty($trimmedValue) && !$request->hasFile('brandImage') && !$hasExistingImage) {
            $fail('Either an image file or image URL is required.');
          }
        },
      ],
      'categories' => ['required'],
    ], [
      'brandTitle.required' => 'Name is required',
      'brandImageUrl.max' => 'Image URL must not exceed 2048 characters',
      'categories.required' => 'Category is required',
    ]);

    // Determine image URL: prioritize uploaded file, then URL input, then keep existing
    $imageUrl = $brand->image; // Default to existing image

    if ($request->hasFile('brandImage')) {
      // Delete old file if it was a stored file (not a URL)
      $oldImage = $brand->image;
      if ($oldImage && !filter_var($oldImage, FILTER_VALIDATE_URL)) {
        // It's a stored file path, delete it
        if (Storage::disk('public')->exists($oldImage)) {
          Storage::disk('public')->delete($oldImage);
        }
      }

      $path = $request->file('brandImage')->store('brands', 'public');
      $imageUrl = asset('storage/' . $path);
    } elseif (!empty($request->brandImageUrl)) {
      // Delete old file if it was a stored file (not a URL)
      $oldImage = $brand->image;
      if ($oldImage && !filter_var($oldImage, FILTER_VALIDATE_URL)) {
        // It's a stored file path, delete it
        if (Storage::disk('public')->exists($oldImage)) {
          Storage::disk('public')->delete($oldImage);
        }
      }
      $imageUrl = $request->brandImageUrl;
    }

    if ($request->brandTags != null) {

      $brandTags = json_decode($request->brandTags, true);

      BrandTag::where('brand_id', $request->id)->delete();

      foreach ($brandTags as $tag) {

        $tag = Tag::updateOrCreate([
          'name' => $tag['value'],
          'type' => 'categorical'
        ]);

        BrandTag::create([
          'tag_id' => $tag->id,
          'brand_id' => $request->id
        ]);
      }
    }

    BrandCategory::where('brand_id', $request->id)->delete();

    foreach ($request->categories as $category) {

      BrandCategory::create([
        'brand_id' => $request->id,
        'category_id' => $category
      ]);
    }

    $brand->update([
      'name' => $validated['brandTitle'],
      'image' => $imageUrl,
      'is_active' => $request->brandStatus
    ]);

    Toastr::success('Brand updated successfully!');
    return redirect()->route('brand.list');
  }

  public function delete($id)
  {
    $brand = Brand::findOrFail($id);
    $brand->delete();
    Toastr::success('Brand deleted successfully!');
    return redirect()->back();
  }
}
