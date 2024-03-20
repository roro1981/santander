<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DecimalsInZero implements Rule
{
    public function passes($attribute, $value)
    {
        // Verifica que el valor sea numérico y termine en .00
        return is_numeric($value) && preg_match('/^[\d]+(\.0+)?$/', $value);
    }

    public function message()
    {
        // Mensaje de error cuando la validación falla
        return 'The :attribute must be a number with .00 as the decimal part.';
    }
}