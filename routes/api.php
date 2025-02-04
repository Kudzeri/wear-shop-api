<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SizeController;



Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/profile', [AuthController::class, 'profile']);

Route::apiResource('colors', ColorController::class);
Route::get('colors/{id}/products', [ColorController::class, 'getProducts']);

Route::apiResource('categories', CategoryController::class);
Route::get('categories/{slug}/parent', [CategoryController::class, 'getParent']);
Route::get('categories/{slug}/children', [CategoryController::class, 'getChildren']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::get('/addresses/primary', [AddressController::class, 'primary']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);
});

Route::prefix('sizes')->group(function () {
    Route::get('/', [SizeController::class, 'index']);
    Route::post('/', [SizeController::class, 'store']);
    Route::get('/{slug}/products', [SizeController::class, 'getProductsBySize']);
    Route::delete('/{id}', [SizeController::class, 'destroy']);
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('{id}', [ProductController::class, 'show']);
    Route::put('{id}', [ProductController::class, 'update']);
    Route::delete('{id}', [ProductController::class, 'destroy']);
    Route::get('size/{size_slug}', [ProductController::class, 'getBySize']);
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('{id}', [UserController::class, 'update']);
    Route::delete('{id}', [UserController::class, 'destroy']);
});


// Для пользователя (обычные действия с заказами)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('orders', [OrderController::class, 'store']); // Создать заказ
    Route::get('orders/{id}', [OrderController::class, 'show']); // Показать заказ
    Route::get('orders', [OrderController::class, 'index']); // Получить все заказы

    Route::put('orders/{id}/status', [OrderController::class, 'updateStatus']); // Обновить статус заказа
});

// Для администратора (CRUD для заказов)
Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin')->group(function () {
        Route::get('admin/orders', [OrderController::class, 'admIndex']); // Получить все заказы
        Route::get('admin/orders/{id}', [OrderController::class, 'admShow']); // Показать конкретный заказ
        Route::post('admin/orders', [OrderController::class, 'admStore']); // Создать заказ (админ)
        Route::put('admin/orders/{id}', [OrderController::class, 'admUpdate']); // Обновить заказ (админ)
        Route::delete('admin/orders/{id}', [OrderController::class, 'admDestroy']); // Удалить заказ (админ)
    });
});
