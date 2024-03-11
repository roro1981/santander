<?php

namespace App\Http\Controllers;
namespace App\Http\Controllers;
use App\Jobs\FtpConciliationJob;
use Illuminate\Http\Request;

class FtpConciliationController extends Controller
{
    public function conciliation(){
        FtpConciliationJob::dispatch();
    }
}
