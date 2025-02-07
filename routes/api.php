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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/avatar', [AuthController::class, 'updateAvatar']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);
    Route::get('/user/loyalty', [AuthController::class, 'getLoyaltyInfo']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
});


Route::apiResource('colors', ColorController::class);
Route::get('colors/{id}/products', [ColorController::class, 'getProducts']);

Route::get('/categories/on-sale', [CategoryController::class, 'getSaleCategories']);
Route::get('categories/{slug}/parent', [CategoryController::class, 'getParent']);
Route::get('categories/{slug}/children', [CategoryController::class, 'getChildren']);
Route::get('/categories/{id}/products', [CategoryController::class, 'getAllProducts']);
Route::apiResource('categories', CategoryController::class);

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
    Route::get('popular', [ProductController::class, 'getPopularProducts']);
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


Route::middleware(['auth:sanctum'])->group(function () {
    // Пользовательские маршруты
    Route::post('/orders', [OrderController::class, 'store']); // Создание заказа
    Route::get('/orders', [OrderController::class, 'index']); // Получение всех заказов пользователя
    Route::get('/orders/{id}', [OrderController::class, 'show']); // Получение конкретного заказа
    Route::post('/orders/{orderId}/pay', [OrderController::class, 'payOrder']); // Оплата заказа
    Route::post('/orders/{orderId}/cancel', [OrderController::class, 'cancelOrder']); // Отмена заказа

    // Webhook от YooKassa (без аутентификации)
    Route::post('/orders/webhook', [OrderController::class, 'webhook']);
});

// Маршруты для администраторов
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/orders', [OrderController::class, 'admIndex']); // Получение всех заказов
    Route::get('/admin/orders/{id}', [OrderController::class, 'admShow']); // Получение конкретного заказа
    Route::post('/admin/orders', [OrderController::class, 'admStore']); // Создание заказа от имени пользователя
    Route::put('/admin/orders/{id}', [OrderController::class, 'admUpdate']); // Обновление заказа
    Route::delete('/admin/orders/{id}', [OrderController::class, 'admDestroy']); // Удаление заказа
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
    Route::get('/loyalty/apply-discount', [LoyaltyController::class, 'applyDiscount']);
});


Route::middleware(['auth:sanctum'])->group(function () {
    // Создание платежа
    Route::post('/payments', [PaymentController::class, 'pay']);

    // Проверка статуса платежа
    Route::get('/payments/status', [PaymentController::class, 'checkPaymentStatus']);

    // Отмена платежа
    Route::post('/payments/cancel', [PaymentController::class, 'cancelPayment']);
});

// Webhook от YooKassa (без аутентификации)
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

Route::get('/order/payment/success/{orderId}', [OrderController::class, 'confirmPayment'])->name('order.payment.success');
