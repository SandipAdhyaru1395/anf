<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class ProductController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->where('is_active', 1)
            ->with(['childrenRecursive', 'brands.products'])
            ->orderBy('is_special', 'desc')
            ->orderBy('sort_order', 'asc')
            ->get();

        $formatted = $categories->map(fn($category) => $this->formatCategoryRecursive($category))->values();

        return response()->json(['categories' => $formatted]);
    }

    protected function formatCategoryRecursive($category)
    {
        // Ensure children are ordered at every depth: first by is_special (desc), then by sort_order (asc)
        if ($category->relationLoaded('children')) {
            $children = $category->children
                ->filter(fn($c) => (int)($c->is_active ?? 0) === 1)
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
            return [
                'name' => $category->name,
                'is_special' => $category->is_special,
                'subcategories' => $children->map(fn($child) => $this->formatCategoryRecursive($child))->values(),
            ];
        }

        $brands = ($category->brands ?? collect())->filter(fn($b) => (int)($b->is_active ?? 0) === 1);

        return [
            'name' => $category->name,
            'is_special' => $category->is_special,
            'subcategories' => $brands->map(function ($brand) {
                $products = ($brand->products ?? collect())
                    ->filter(fn($p) => (int)($p->is_active ?? 0) === 1)
                    ->sortByDesc('stock_quantity')
                    ->values();
                // Check if image is a full URL or a stored file path
                $imageUrl = null;
                if ($brand->image) {
                    $imageUrl = (filter_var($brand->image, FILTER_VALIDATE_URL)) 
                        ? $brand->image 
                        : asset('storage/'.$brand->image);
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
            'quantity' => isset($product->stock_quantity) ? (int)$product->stock_quantity : 0,
            'price' =>  $setting['currency_symbol'] . number_format($priceNumber, 2),
            'discount' => $discountNumber !== null ? ('£' . number_format($discountNumber, 2)) : null,
            'wallet_credit' => isset($product->wallet_credit) ? (float)$product->wallet_credit : 0,
            'vat_amount' => isset($product->vat_amount) ? (float)$product->vat_amount : 0,
        ];
    }

}
