<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\ApiController;
use App\Http\Controllers\API\V1\AttributeController;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\OrderController;
use App\Http\Controllers\API\V1\ProductController;
use App\Http\Controllers\API\V1\UserController;
use App\Http\Controllers\API\V1\BrandController;
use App\Http\Controllers\API\V1\CartController;
use App\Http\Controllers\API\V1\ReviewController;
use App\Http\Controllers\API\V2\CategoryController as V2CategoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

JsonApiRoute::server('v2')
    ->prefix('v2')
    ->resources(function ($server) {
        $server->resource('categories', V2CategoryController::class);
    });


Route::prefix('v1')->group(function () {
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

        // Quản lý giá trị thuộc tính
        Route::get('/{attributeId}/values', [AttributeController::class, 'getAttributeValues']);
        Route::post('/{attributeId}/values', [AttributeController::class, 'storeAttributeValue']);
        Route::put('/values/{id}', [AttributeController::class, 'updateAttributeValue']);
        Route::delete('/values/{id}', [AttributeController::class, 'destroyAttributeValue']);
    });

    Route::middleware('auth:sanctum')->get('/check-user', function (Request $request) {
        return response()->json($request->user());
    });


    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

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

            Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']); // Admin cập nhật trạng thái đơn
            Route::delete('/orders/{id}', [OrderController::class, 'deleteOrder']); // Admin xóa đơn hàng

            Route::post('/order/create', [OrderController::class, 'store']);
            Route::get('/order', [OrderController::class, 'listOrders']);
            Route::get('/order/{id}', [OrderController::class, 'getOrderDetails']);
            Route::get('/order/{id}/history', [OrderController::class, 'getOrderHistory']);
            Route::patch('/order/{id}/cancel', [OrderController::class, 'cancelOrder']);
        });
    });

    Route::get('/brands', [BrandController::class, 'index']);
    Route::get('/brands/{id}', [BrandController::class, 'show']);



    Route::apiResource('reviews', ReviewController::class);
});
