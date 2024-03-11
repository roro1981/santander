<?php

namespace App\Http\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Parameter;

class ParamUtil
{
    public static function getParam(String $code)
    {
        
        $parameter = Cache::remember('parameter_' . $code, now()->addHours(6), function () use ($code) {
            return DB::table(Constants::PARAMETER_TABLE)->where('par_code', $code)->value('par_value');
        });
    
        return $parameter;
    }
    public static function getParams(array $codes)
    {
        return Parameter::whereIn('par_code', $codes)
            ->get()
            ->pluck('par_value', 'par_code');
    }

}
