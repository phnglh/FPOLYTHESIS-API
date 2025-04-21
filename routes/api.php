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
use App\Http\Controllers\API\V1\ReportController;
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
    Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);

    // Review, Promotion (Public access)
    Route::apiResource('promotions', PromotionController::class);
    Route::apiResource('wishlist', WishListController::class);





    // Attribute (Public access to GET)
    Route::prefix('attributes')->group(function () {
        Route::get('/', [AttributeController::class, 'index']);
        Route::get('/{attributeId}/values', [AttributeController::class, 'getAttributeValues']);
    });

    Route::get('/payment/vnpay-return', [PaymentController::class, 'vnPayCallback']);

    // -------------------------
    // Private API (Cần đăng nhập)
    // -------------------------
    Route::middleware('auth:sanctum')->group(function () {
        // User
        Route::get('/users/profile', [UserController::class, 'me']);
        // User có thể cập nhật thông tin cá nhân
        Route::put('/users/profile', [UserController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/check-user', fn (Request $request) => response()->json($request->user()));
        Route::post('/change-password', [AuthController::class, 'changePassword']);


        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

        // order
        Route::post('/orders/create', [OrderController::class, 'createOrder']); // Tạo đơn hàng
        Route::get('/orders', [OrderController::class, 'index']); // Xem đơn hàng của mình
        Route::get('/orders/{order_id}', [OrderController::class, 'show']); // xem chi tiết đơn hàng
        Route::delete('/orders/{orderId}', [OrderController::class, 'cancelOrder']); // Hủy đơn hàng của mình

        //voucher
        Route::post('/voucher/check', [OrderController::class, 'checkVoucher']);

        // payment
        Route::post('/payment/pay', [PaymentController::class, 'payOrder']);
        Route::post('/payment/retry/{orderId}', [PaymentController::class, 'retryPayment']); // thanh toán lại

        // Cart
        Route::get('/cart', [CartController::class, 'index']); // Lấy giỏ hàng
        Route::post('/cart', [CartController::class, 'store']); // Thêm sản phẩm vào giỏ hàng
        Route::put('/cart/{itemId}', [CartController::class, 'updateQuantity']); // update số lượng sản phẩm
        Route::patch('/cart/increment/{itemId}', [CartController::class, 'increment']); // Tăng 1 sản phẩm
        Route::patch('/cart/decrement/{itemId}', [CartController::class, 'decrement']); // Giảm 1 sản phẩm
        Route::delete('/cart/{itemId}', [CartController::class, 'destroy']); // Xóa sản phẩm khỏi giỏ hàng
        Route::delete('/cart', [CartController::class, 'clear']); // Xóa toàn bộ giỏ hàng

        // user_address
        Route::apiResource('user-addresses', UserAddressController::class);
        Route::patch('/user-addresses/{id}/set-default', [UserAddressController::class, 'setDefault']);




        // wishlist
        Route::get('/wishlist', [WishListController::class, 'getWishList']);
        Route::post('/wishlist', [WishListController::class, 'addWishList']);
        Route::delete('/wishlist/{id}', [WishListController::class, 'deleteWishList']);


        // -------------------------
        // Role-based API (Admin Only)
        // -------------------------
        Route::middleware('role:admin')->group(function () {
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
            Route::patch('products/{id}/publish', [ProductController::class, 'togglePublish']);
            Route::delete('/products/{id}', [ProductController::class, 'destroy']);

            // Route cho quản lý SKU
            Route::get('/{product_id}/skus', [ProductController::class, 'getSkus']); // Lấy danh sách SKU của sản phẩm
            Route::post('/{product_id}/skus', [ProductController::class, 'createSku']); // Thêm SKU mới
            Route::put('/skus/{sku_id}', [ProductController::class, 'updateSku']); // Cập nhật SKU
            Route::delete('/skus/{sku_id}', [ProductController::class, 'deleteSku']); // Xóa SKU


            // Orders
            Route::get('/orders/all', [OrderController::class, 'getOrders']);
            Route::get('/orders/{orderId}', [OrderController::class, 'getOrderDetail']);
            Route::put('/orders/{orderId}/status', [OrderController::class, 'updateStatus']);

            // Voucher
            Route::get('/vouchers', [VoucherController::class, 'index']);
            Route::post('/vouchers/apply', [VoucherController::class, 'apply']);
            Route::post('/vouchers', [VoucherController::class, 'store']);
            Route::put('/vouchers/{voucher}', [VoucherController::class, 'update']);
            Route::delete('/vouchers/{voucher}', [VoucherController::class, 'destroy']);
            Route::patch('/vouchers/{id}', [VoucherController::class, 'toggleActive']);

            // Attribute (CRUD)
            Route::post('/attributes', [AttributeController::class, 'store']);
            Route::put('/attributes/{id}', [AttributeController::class, 'update']);
            Route::delete('/attributes/{id}', [AttributeController::class, 'destroy']);
            Route::post('/attributes/{attributeId}/values', [AttributeController::class, 'storeAttributeValue']);
            Route::put('/attributes/values/{id}', [AttributeController::class, 'updateAttributeValue']);
            Route::delete('/attributes/values/{id}', [AttributeController::class, 'destroyAttributeValue']);

            Route::prefix('reports')->group(function () {
                Route::get('/revenue', [ReportController::class, 'getRevenueReport']);
                Route::get('/orders', [ReportController::class, 'getOrderReport']);
                Route::get('/cancel-rate', [ReportController::class, 'getCancelRate']);
                Route::get('/revenue-by-category', [ReportController::class, 'getRevenueByCategory']);
                Route::get('/top-products', [ReportController::class, 'getTopProductReport']);
                Route::get('/top-customers', [ReportController::class, 'getTopCustomerReport']);
                Route::get('/revenue-statistics', [ReportController::class, 'index']);
            });


        });
    });
});
