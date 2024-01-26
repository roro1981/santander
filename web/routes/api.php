<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OrderController;


Route::get('/v1/health', function () {
    return response()->json(['status' => 'OK'], 200);
});

Route::post('/v1/order/create', [OrderController::class, 'create']);
Route::post('/v1/santander/notify', [OrderController::class, 'notify']);
Route::get('/v1/santander/redirect', [OrderController::class, 'mpfin']);

