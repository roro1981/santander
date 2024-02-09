<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\FtpConciliationController;


Route::get('/v1/health', function () {
    return response()->json(['status' => 'OK'], 200);
});

Route::post('/v1/order/create', [OrderController::class, 'create']);
Route::post('/v1/santander/notify', [OrderController::class, 'notify']);
Route::get('/v1/santander/redirect', [OrderController::class, 'mpfin']);


/*Route::get('/v1/santander/conciliation', [FtpConciliationController::class, 'conciliation']);
namespace App\Http\Controllers;
use App\Jobs\FtpConciliationJob;
use Illuminate\Http\Request;

class FtpConciliationController extends Controller
{
    public function conciliation(){
        FtpConciliationJob::dispatch();
    }
}*/

