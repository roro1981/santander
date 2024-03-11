<?php

namespace App\Http\Utils;

class Util
{
    public static function validateExpirationTime(Int $expiration)
    {
        $defaultExpirationTime = Constants::PARAM_EXPIRATION_TIME;
        if ($expiration > time() + $defaultExpirationTime && $expiration < time() + Constants::MAX_ORDER_EXPIRATION)
        {
            return $expiration;
        }
        else
        {
            return time() + $defaultExpirationTime;
        }
    }

}