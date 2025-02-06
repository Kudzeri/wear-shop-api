<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
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
Route::get('/categories/{id}/products', [CategoryController::class, 'getAllProducts']);

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
    Route::middleware('auth:sanctum')->post('/', [ProductController::class, 'store']);
    Route::get('{id}', [ProductController::class, 'show']);
    Route::middleware('auth:sanctum')->put('{id}', [ProductController::class, 'update']);
    Route::middleware('auth:sanctum')->delete('{id}', [ProductController::class, 'destroy']);
    Route::get('size/{size_slug}', [ProductController::class, 'getBySize']);
    Route::get('color/{color_id}', [ProductController::class, 'getByColor']);
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::middleware('auth:sanctum')->post('/', [UserController::class, 'store']);
    Route::middleware('auth:sanctum')->put('{id}', [UserController::class, 'update']);
    Route::middleware('auth:sanctum')->delete('{id}', [UserController::class, 'destroy']);
});


// Для пользователя (обычные действия с заказами)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('orders', [OrderController::class, 'store']); // Создать заказ
    Route::post('orders/{orderId}/pay', [OrderController::class, 'payOrder']);
    Route::get('orders/{id}', [OrderController::class, 'show']); // Показать заказ
    Route::get('orders', [OrderController::class, 'index']); // Получить все заказы
    Route::post('orders/{order}/complete', [OrderController::class, 'completeOrder']);
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

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('wishlist', WishlistController::class)
        ->only(['index', 'store', 'destroy'])
        ->parameters(['wishlist' => 'product_id']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/loyalty/use-points', [LoyaltyController::class, 'usePoints']);
    Route::get('/loyalty/points', [LoyaltyController::class, 'getUserPoints']);
    Route::get('/loyalty/level', [LoyaltyController::class, 'getUserLevel']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/pay', [PaymentController::class, 'pay']);
    Route::post('/webhook', [PaymentController::class, 'webhook']);
});

Route::get('/order/payment/success/{orderId}', [OrderController::class, 'confirmPayment'])->name('order.payment.success');
Route::post('/yookassa/webhook', [OrderController::class, 'yookassaWebhook']);
