<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\API\ProductController;

Route::apiResource('products', ProductController::class);
Route::apiResource('product-variants', ProductVariantController::class);
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

Route::middleware('auth:sanctum')->get('/check-user', function (Request $request) {
    return response()->json($request->user());
});


Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });
});
