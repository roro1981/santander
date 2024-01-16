<?php

namespace App\Http\Utils;

use Illuminate\Support\Facades\DB;

class ParamUtil
{
    public static function getParam($code)
    {
        $parameter = DB::table(Constants::PARAMETER_TABLE)->where('par_code', $code)->first();
        return ($parameter) ? $parameter->par_value : null;
    }

    public static function getBankCredentials($bankId)
    {
        return DB::table(Constants::PARAMETER_TABLE)->whereIn('par_code', [
            $bankId . Constants::PARAM_BANK_RECEIVER,
            $bankId . Constants::PARAM_BANK_SECRET,
        ])->get();
    }

    public static function getParams(array $codes)
    {
        return DB::table(Constants::PARAMETER_TABLE)->whereIn('par_code', $codes)
            ->get()
            ->pluck('par_value', 'par_code');
    }
}
