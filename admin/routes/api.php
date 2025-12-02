<?php

use App\Http\Controllers\Api\BranchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\CartController;


// Public auth endpoints

// Public endpoints
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/settings', [SettingController::class, 'show']);
// Protected endpoints (blocked when store maintenance is on)
Route::middleware(['store.maintenance','auth:sanctum'])->group(function () {
    
    Route::get('/products',[ProductController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Authenticated customer profile
    Route::get('/customer', [CustomerController::class, 'me']);
    Route::put('/customer', [CustomerController::class, 'updateCompanyDetails']);

    // Checkout API endpoint
    Route::post('/checkout',[OrderController::class, 'store']);

    // Recent Orders API endpoint
    Route::get('/orders',[OrderController::class, 'index']);
    Route::get('/orders/{orderNumber}',[OrderController::class, 'show']);
    Route::post('/orders/{orderNumber}/reorder',[OrderController::class, 'reorder']);

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/add', [FavoriteController::class, 'add']);
    Route::delete('/favorites/remove', [FavoriteController::class, 'remove']);

    //Branch
    Route::apiResource('branches', BranchController::class);
    Route::get('/delivery-methods', [SettingController::class, 'deliveryMethods']);

    // Cart
    Route::get('/cart', [CartController::class, 'get']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::post('/cart/decrement', [CartController::class, 'decrement']);
    Route::post('/cart/set', [CartController::class, 'set']);
    Route::post('/cart/clear', [CartController::class, 'clear']);
});
