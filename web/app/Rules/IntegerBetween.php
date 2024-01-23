<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IntegerBetween implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ((is_int($value) || is_float($value)) && ($value < 1 || $value > 2147483647))
        {
            $fail("The $attribute field must be between 1 and 2147483647.");
        }
    }
}
