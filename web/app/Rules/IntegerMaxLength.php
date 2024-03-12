<?php

namespace App\Rules;

use App\Http\Utils\Constants;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IntegerMaxLength implements ValidationRule
{
    private $maxLength;

    public function __construct(int $maxLength)
    {
        $this->maxLength = $maxLength;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_int($value) && strlen(str($value)) > $this->maxLength)
        {
            $fail("The $attribute field must not have more than $this->maxLength digits.");
        }
    }
}
