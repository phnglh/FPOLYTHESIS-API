<?php

use App\Http\Controllers\API\V1\ApiController;
use App\Http\Controllers\API\V1\AttributeController;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\BrandController;
use App\Http\Controllers\API\V1\CartController;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\OrderController;
use App\Http\Controllers\API\V1\ProductController;
use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\API\V1\ReviewController;
use App\Http\Controllers\API\V1\VoucherController;
use App\Http\Controllers\API\V1\WishListController;
use App\Http\Controllers\API\V2\CategoryController as V2CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;

// -------------------------
// Public API (Không cần đăng nhập)
// -------------------------
JsonApiRoute::server('v2')
    ->prefix('v2')
    ->resources(function ($server) {
        $server->resource('categories', V2CategoryController::class);
    });

Route::prefix('v1')->group(function () {
    Route::get('/', [ApiController::class, 'index']);
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');

    // Brand
    Route::get('/brands', [BrandController::class, 'index']);
    Route::get('/brands/{id}', [BrandController::class, 'show']);

    // Category
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // Product
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Review, Promotion, Wishlist (Public access)
    Route::apiResource('reviews', ReviewController::class);
    Route::apiResource('promotions', PromotionController::class);
    Route::apiResource('wishlist', WishListController::class);

    // Attribute (Public access to GET)
    Route::prefix('attributes')->group(function () {
        Route::get('/', [AttributeController::class, 'index']);
        Route::get('/{attributeId}/values', [AttributeController::class, 'getAttributeValues']);
    });

    // -------------------------
    // Private API (Cần đăng nhập)
    // -------------------------
    Route::middleware('auth:sanctum')->group(function () {

        // User
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/check-user', fn (Request $request) => response()->json($request->user()));
        Route::post('/change-password', [AuthController::class, 'changePassword']);

        // -------------------------
        // Role-based API (Admin Only)
        // -------------------------
        Route::middleware('role:admin')->group(function () {
            // Brand
            Route::post('/brands', [BrandController::class, 'store']);
            Route::put('/brands/{id}', [BrandController::class, 'update']);
            Route::delete('/brands/{id}', [BrandController::class, 'destroy']);

            // Category
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{id}', [CategoryController::class, 'update']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

            // Product
            Route::post('/products', [ProductController::class, 'store']);
            Route::put('/products/{id}', [ProductController::class, 'update']);
            Route::patch('/products/{id}', [ProductController::class, 'updatePartial']);
            Route::patch('/{id}/publish', [ProductController::class, 'togglePublish']);
            Route::delete('/products/{id}', [ProductController::class, 'destroy']);

            // Cart
            Route::get('/cart', [CartController::class, 'index']);
            Route::post('/cart', [CartController::class, 'store']);
            Route::put('/cart/{id}', [CartController::class, 'update']);
            Route::delete('/cart/{id}', [CartController::class, 'destroy']);

            // Orders
            Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
            Route::delete('/orders/{id}', [OrderController::class, 'deleteOrder']);
            Route::post('/order/create', [OrderController::class, 'store']);
            Route::get('/order', [OrderController::class, 'listOrders']);
            Route::get('/order/{id}', [OrderController::class, 'getOrderDetails']);
            Route::get('/order/{id}/history', [OrderController::class, 'getOrderHistory']);
            Route::patch('/order/{id}/cancel', [OrderController::class, 'cancelOrder']);

            // Voucher
            Route::get('/vouchers', [VoucherController::class, 'index']);
            Route::post('/vouchers/apply', [VoucherController::class, 'apply']);
            Route::post('/vouchers', [VoucherController::class, 'store']);
            Route::put('/vouchers/{voucher}', [VoucherController::class, 'update']);
            Route::delete('/vouchers/{voucher}', [VoucherController::class, 'destroy']);

            // Attribute (CRUD)
            Route::post('/attributes', [AttributeController::class, 'store']);
            Route::put('/attributes/{id}', [AttributeController::class, 'update']);
            Route::delete('/attributes/{id}', [AttributeController::class, 'destroy']);
            Route::post('/attributes/{attributeId}/values', [AttributeController::class, 'storeAttributeValue']);
            Route::put('/attributes/values/{id}', [AttributeController::class, 'updateAttributeValue']);
            Route::delete('/attributes/values/{id}', [AttributeController::class, 'destroyAttributeValue']);

        });
    });
});
