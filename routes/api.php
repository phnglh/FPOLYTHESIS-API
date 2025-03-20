<?php

use App\Http\Controllers\API\V1\ApiController;
use App\Http\Controllers\API\V1\AttributeController;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\BrandController;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\OrderController;
use App\Http\Controllers\API\V1\PaymentController;
use App\Http\Controllers\API\V1\ProductController;
use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\API\V1\ReviewController;
use App\Http\Controllers\API\V1\UserController;
use App\Http\Controllers\API\V1\VoucherController;
use App\Http\Controllers\API\V1\WishListController;
use App\Http\Controllers\API\V2\CategoryController as V2CategoryController;
use App\Http\Controllers\API\V1\CartController;
use App\Http\Controllers\API\V1\UserAddressController;
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
        Route::get('/users/me', [UserController::class, 'me']);
        // User có thể cập nhật thông tin cá nhân
        Route::put('/users/profile', [UserController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/check-user', fn (Request $request) => response()->json($request->user()));
        Route::post('/change-password', [AuthController::class, 'changePassword']);


        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::get('/reviews/{id}', [ReviewController::class, 'show']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::put('/reviews/{id}', [ReviewController::class, 'update']);
        Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

        // order
        Route::post('/orders/create', [OrderController::class, 'createOrder']); // Tạo đơn hàng
        Route::get('/orders', [OrderController::class, 'getOrders']); // Xem đơn hàng của mình
        Route::delete('/orders/{orderId}', [OrderController::class, 'cancelOrder']); // Hủy đơn hàng của mình

        // voucher
        Route::post('/vouchers/apply', [VoucherController::class, 'apply']);

        // payment
        Route::post('/payment/pay', [PaymentController::class, 'payOrder']);
        Route::get('/payment/vnpay-return', [PaymentController::class, 'vnpayReturn']);

        // Cart
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::put('/cart/{itemId}', [CartController::class, 'update']);
        Route::delete('/cart/{itemId}', [CartController::class, 'destroy']);
        Route::delete('/cart', [CartController::class, 'clear']);

        // user_address
        Route::apiResource('user-addresses', UserAddressController::class);


        // -------------------------
        // Role-based API (Admin Only)
        // -------------------------
        // Route::middleware('role:admin')->group(function () {
        // Users
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

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


        // Orders


        Route::get('/orders/all', [OrderController::class, 'getOrders']); // Xem tất cả đơn hàng
        Route::put('/orders/{orderId}/status', [OrderController::class, 'updateStatus']); // Cập nhật trạng thái đơn

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
        // });


    });
});
