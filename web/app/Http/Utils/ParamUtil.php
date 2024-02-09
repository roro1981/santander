<?php

namespace App\Http\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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
        return Cache::remember('parameters_' . implode('_', $codes), now()->addHours(6), function () use ($codes) {
            return DB::table(Constants::PARAMETER_TABLE)->whereIn('par_code', $codes)
                ->pluck('par_value', 'par_code');
        });
    }
}
