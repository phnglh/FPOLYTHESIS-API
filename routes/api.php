<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\API\AttributeController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\WishListController;
use App\Http\Controllers\Api\PromotionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/user/{id}/update-role', [UserController::class, 'updateRole'])->middleware('auth:sanctum');


Route::get('/', [ApiController::class, 'index']);
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::prefix('attributes')->group(function () {
    Route::get('/', [AttributeController::class, 'index']);
    Route::post('/', [AttributeController::class, 'store']);
    Route::put('/{id}', [AttributeController::class, 'update']);
    Route::delete('/{id}', [AttributeController::class, 'destroy']);

    Route::get('/{attributeId}/values', [AttributeController::class, 'getAttributeValues']);
    Route::post('/{attributeId}/values', [AttributeController::class, 'storeAttributeValue']);
    Route::put('/values/{id}', [AttributeController::class, 'updateAttributeValue']);
    Route::delete('/values/{id}', [AttributeController::class, 'destroyAttributeValue']);
});

Route::middleware('auth:sanctum')->get('/check-user', function (Request $request) {
    return response()->json($request->user());
});


Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::patch('/products/{id}', [ProductController::class, 'updatePartial']);
        Route::patch('/{id}/publish', [ProductController::class, 'togglePublish']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::put('/cart/{id}', [CartController::class, 'update']);
        Route::delete('/cart/{id}', [CartController::class, 'destroy']);

        Route::post('/payment', [PaymentController::class, 'createPayment']);
        Route::get('/payment/vnpay/callback', [PaymentController::class, 'handleVNPayCallback']);
    });
});

Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{id}', [BrandController::class, 'show']);


Route::post('/order/create', [OrderController::class, 'createOrder']);
Route::get('/order', [OrderController::class, 'listOrders']);
Route::get('/order/{id}', [OrderController::class, 'getOrderDetails']);
Route::get('/order/{id}/history', [OrderController::class, 'getOrderHistory']);
Route::patch('/order/{id}/cancel', [OrderController::class, 'cancelOrder']);

Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'store']);
Route::put('/cart/{id}', [CartController::class, 'update']);
Route::delete('/cart/{id}', [CartController::class, 'destroy']);

Route::apiResource('promotions', PromotionController::class);
Route::apiResource('reviews', ReviewController::class);
Route::apiResource('wishlist', WishListController::class);
