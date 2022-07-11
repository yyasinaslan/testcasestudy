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
