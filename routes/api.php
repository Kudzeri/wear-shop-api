<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RussianPostController;
use App\Http\Controllers\SdekController;
use App\Http\Controllers\StoriesController;
use App\Http\Controllers\StylistController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\Report1CController;
use App\Http\Controllers\DeliveryServiceController;
use App\Http\Controllers\SubscriptionController;


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


Route::middleware('auth:sanctum')->group(function () {
    // Заказы для авторизованных пользователей
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{orderId}/pay', [OrderController::class, 'payOrder']);
    Route::post('/orders/webhook', [OrderController::class, 'webhook']);
    Route::post('/orders/{orderId}/cancel', [OrderController::class, 'cancelOrder']);
    Route::post('/orders/{orderId}/confirm-payment', [OrderController::class, 'confirmPayment']);

    // Маршруты СДЭК
    Route::prefix('sdek')->group(function () {
        Route::post('/calculate-delivery', [SdekController::class, 'calculateDelivery']);
        Route::post('/create-shipment', [SdekController::class, 'createShipment']);
        Route::get('/track-shipment', [SdekController::class, 'trackShipment']);
        Route::get('/shipment-by-uuid', [SdekController::class, 'getShipmentByUuid']);
    });

    // Маршруты Почты России
    Route::prefix('russian-post')->group(function () {
        Route::post('/calculate-delivery', [RussianPostController::class, 'calculateDelivery']);
        Route::post('/create-shipment', [RussianPostController::class, 'createShipment']);
        Route::get('/track-shipment', [RussianPostController::class, 'trackShipment']);
        Route::get('/search-order', [RussianPostController::class, 'searchOrder']);
    });
});

// Новые роуты для служб доставки
Route::get('/delivery-services', [DeliveryServiceController::class, 'index']);
Route::post('/delivery-services', [DeliveryServiceController::class, 'store']);
Route::get('/delivery-services/{id}', [DeliveryServiceController::class, 'show']);
Route::put('/delivery-services/{id}', [DeliveryServiceController::class, 'update']);
Route::delete('/delivery-services/{id}', [DeliveryServiceController::class, 'destroy']);

// Новые роуты для пунктов самовывоза (PickUpPoint)
Route::get('/pickup-points', [\App\Http\Controllers\PickUpPointController::class, 'index']);
Route::post('/pickup-points', [\App\Http\Controllers\PickUpPointController::class, 'store']);
Route::get('/pickup-points/{id}', [\App\Http\Controllers\PickUpPointController::class, 'show']);
Route::put('/pickup-points/{id}', [\App\Http\Controllers\PickUpPointController::class, 'update']);
Route::delete('/pickup-points/{id}', [\App\Http\Controllers\PickUpPointController::class, 'destroy']);

// Маршруты для администратора (пример с использованием middleware проверки роли "admin")
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/orders', [OrderController::class, 'admIndex']);
    Route::get('/orders/{id}', [OrderController::class, 'admShow']);
    Route::post('/orders', [OrderController::class, 'admStore']);
    Route::put('/orders/{id}', [OrderController::class, 'admUpdate']);
    Route::delete('/orders/{id}', [OrderController::class, 'admDestroy']);
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
    Route::get('/loyalty/points-history', [LoyaltyController::class, 'getPointsHistory']);
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


Route::get('/stories', [StoriesController::class, 'index']);
Route::post('/stories', [StoriesController::class, 'store']);
Route::get('/stories/{id}', [StoriesController::class, 'show']);
Route::delete('/stories/{id}', [StoriesController::class, 'destroy']);
Route::post('/stories/{story}/products', [StoriesController::class, 'addProduct']);
Route::delete('/stories/{story}/products', [StoriesController::class, 'removeProduct']);

Route::get('/stylists', [StylistController::class, 'index']);
Route::post('/stylists', [StylistController::class, 'store']);
Route::get('/stylists/{id}', [StylistController::class, 'show']);
Route::delete('/stylists/{id}', [StylistController::class, 'destroy']);
Route::post('/stylists/{stylist}/products', [StylistController::class, 'addProduct']);
Route::delete('/stylists/{stylist}/products', [StylistController::class, 'removeProduct']);

Route::get('/reports/customers', [Report1CController::class, 'exportCustomers']);
Route::get('/reports/products', [Report1CController::class, 'exportProducts']);
Route::get('/reports/promos', [Report1CController::class, 'exportPromos']);
Route::get('/reports/orders', [Report1CController::class, 'exportOrders']);

Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
