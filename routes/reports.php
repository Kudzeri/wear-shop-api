<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report1CController;

Route::get('/report/customers', [Report1CController::class, 'exportCustomers']);
Route::get('/report/products', [Report1CController::class, 'exportProducts']);
Route::get('/report/promos', [Report1CController::class, 'exportPromos']);
Route::get('/report/orders', [Report1CController::class, 'exportOrders']);
