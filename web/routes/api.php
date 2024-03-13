<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\FtpConciliationController;


Route::get('/v1/health', function () {
    return response()->json(['status' => 'OK'], 200);
});

Route::post('/v1/order/create', [OrderController::class, 'create']);
Route::post('/v1/webhook/notify', [WebhookController::class, 'notify']);
Route::post('/v1/redirect', [WebhookController::class, 'redirect']);

Route::get('/v1/refund/isrefundable', function () {
    return false;
});

Route::get('/v1/santander/conciliation', [FtpConciliationController::class, 'conciliation']);


