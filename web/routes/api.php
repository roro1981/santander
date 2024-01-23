<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;


Route::get('/v1/health', function () {
    return response()->json(['status' => 'OK'], 200);
});

Route::post('/v1/order/create', [OrderController::class, 'create']);

