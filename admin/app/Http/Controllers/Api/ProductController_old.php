<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CustomerGroup;
use App\Models\CustomerGroupable;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
{
    $customerGroupId = auth()->user()->customer_group_id ?? null;

    // If no group → show everything (no restriction)
    if (!$customerGroupId) {
        return response()->json([
            'categories' => $this->loadCategories()
        ]);
    }

    $restriction = CustomerGroup::where('id', $customerGroupId)
        ->value('restrict_categories');

    // If restriction disabled → show everything
    if ((int)$restriction === 0) {
        return response()->json([
            'categories' => $this->loadCategories()
        ]);
    }

    // Load permissions only if restriction enabled
    $groupables = CustomerGroupable::where('customer_group_id', $customerGroupId)->get();

    if ($groupables->isEmpty()) {
        return response()->json(['categories' => []]);
    }

    $allowedCategoryIds = $groupables
        ->where('customer_groupable_type', Category::class)
        ->pluck('customer_groupable_id')
        ->toArray();

    $allowedBrandIds = $groupables
        ->where('customer_groupable_type', Brand::class)
        ->pluck('customer_groupable_id')
        ->toArray();

    $categories = Category::whereNull('parent_id')
        ->where('is_active', 1)
        ->with(['childrenRecursive', 'brands.products', 'brands.tags'])
        ->orderByDesc('is_special')
        ->orderBy('sort_order')
        ->get();

    $formatted = $categories
        ->map(fn($category) =>
            $this->formatCategoryRecursive(
                $category,
                $allowedCategoryIds,
                $allowedBrandIds,
                true
            )
        )
        ->filter()
        ->values();

    return response()->json(['categories' => $formatted]);
}

private function loadCategories()
{
    return Category::whereNull('parent_id')
        ->where('is_active', 1)
        ->with(['childrenRecursive', 'brands.products', 'brands.tags'])
        ->orderByDesc('is_special')
        ->orderBy('sort_order')
        ->get()
        ->map(fn($category) =>
            $this->formatCategoryRecursive($category)
        )
        ->values();
}


    protected function formatCategoryRecursive($category, $allowedCategoryIds = [], $allowedBrandIds = [],$parent = false, $base = null)
    {
       
        // Ensure children are ordered at every depth: first by is_special (desc), then by sort_order (asc)
        if ($category->relationLoaded('children')) {
            $children = $category->children
                ->filter(fn($c) => (int) ($c->is_active ?? 0) === 1)
                ->sortBy([
                    ['is_special', 'desc'],
                    ['sort_order', 'asc'],
                ])->values();
        } else {
            $children = $category->children()
                ->where('is_active', 1)
                ->orderBy('is_special', 'desc')
                ->orderBy('sort_order', 'asc')
                ->get();
        }
        $hasChildren = $children && $children->isNotEmpty();

        if ($hasChildren) {
            
            if($parent || $base == 'category'){
                $base = 'category';
                $children = $children->filter(function ($child) use ($allowedCategoryIds) {
                    return in_array($child->id, $allowedCategoryIds);
                });
            }
            
            return [
                'name' => $category->name,
                'is_special' => $category->is_special,
                'subcategories' => $children->map(fn($child) => $this->formatCategoryRecursive($child, $allowedCategoryIds, $allowedBrandIds,false, $base))->values(),
            ];
        }else{
            if($parent && $base == null){
                $base = 'brand';
            }
        }

        $brands = ($category->brands ?? collect())->filter(fn($b) => (int) ($b->is_active ?? 0) === 1);

        if($base == 'brand'){
            $brands = $brands->filter(function ($brand) use ($allowedBrandIds) {
                return in_array($brand->id, $allowedBrandIds);
            });
        }
        
        return [
            'name' => $category->name,
            'is_special' => $category->is_special,
            'subcategories' => $brands->map(function ($brand) {
                $products = ($brand->products ?? collect())
                    ->filter(fn($p) => (int) ($p->is_active ?? 0) === 1)
                    ->sortByDesc('stock_quantity')
                    ->values();
                // Check if image is a full URL or a stored file path
                $imageUrl = null;
                if ($brand->image) {
                    $imageUrl = (filter_var($brand->image, FILTER_VALIDATE_URL))
                        ? $brand->image
                        : asset('storage/' . $brand->image);
                }

                return [
                    'name' => $brand->name,
                    'image' => $imageUrl,
                    'tags' => $brand->tags->pluck('name')->implode(', '),
                    'products' => $products->map(fn($product) => $this->formatProduct($product))->values(),
                ];
            })->values(),
        ];
    }

    protected function formatProduct($product)
    {

        $imageUrl = $product->image_url ?? null;

        $priceNumber = is_numeric($product->price) ? (float) $product->price : 0;
        $discountNumber = isset($product->discount) && is_numeric($product->discount) ? (float) $product->discount : null;

        $setting = Helpers::setting();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'image' => $imageUrl,
            'step_quantity' => $product->step_quantity,
            // Expose available quantity for frontend cache consumers
            'quantity' => isset($product->stock_quantity) ? (int) $product->stock_quantity : 0,
            'price' => $setting['currency_symbol'] . number_format($priceNumber, 2),
            'discount' => $discountNumber !== null ? ('£' . number_format($discountNumber, 2)) : null,
            'wallet_credit' => isset($product->wallet_credit) ? (float) $product->wallet_credit : 0,
            'vat_amount' => isset($product->vat_amount) ? (float) $product->vat_amount : 0,
        ];
    }

}
