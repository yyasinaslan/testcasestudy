<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'listOrders']);
Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'addOrder']);
Route::delete('/orders/{id}', [\App\Http\Controllers\OrderController::class, 'deleteOrder']);

Route::get('/products', [\App\Http\Controllers\ProductController::class, 'listProducts']);

Route::get('/discounts', [\App\Http\Controllers\DiscountController::class, 'getAll']);
Route::post('/discounts', [\App\Http\Controllers\DiscountController::class, 'addDiscount']);
Route::get('/discounts/{order_id}', [\App\Http\Controllers\DiscountController::class, 'calculateDiscount']);
