<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FloatMaxDecimals implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_float($value))
        {
            $decimal = explode('.' , str($value));
            if (isset($decimal[1]) && strlen($decimal[1]) > 2)
            {
                $fail("The $attribute field must have 0-2 decimal places.");
            }
        }
    }
}
