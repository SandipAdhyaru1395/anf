<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FavoriteProduct;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $ids = FavoriteProduct::where('customer_id', $user->id)
            ->pluck('product_id')
            ->map(fn ($v) => (int) $v)
            ->values();

        return response()->json([
            'success' => true,
            'product_ids' => $ids,
        ]);
    }
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $fav = FavoriteProduct::firstOrCreate([
            'customer_id' => $user->id,
            'product_id' => (int) $request->input('product_id'),
        ]);

        return response()->json([
            'success' => true,
            'favorite' => [
                'id' => $fav->id,
                'product_id' => $fav->product_id,
            ],
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        FavoriteProduct::where('customer_id', $user->id)
            ->where('product_id', (int) $request->input('product_id'))
            ->delete();

        return response()->json([
            'success' => true,
            'removed' => true,
        ]);
    }
}



