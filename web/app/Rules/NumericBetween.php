<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NumericBetween implements ValidationRule
{
    private $minAmount;
    private $maxAmount;

    public function __construct(float $minAmount, float $maxAmount)
    {
        $this->minAmount = $minAmount;
        $this->maxAmount = $maxAmount;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ((is_int($value) || is_float($value)) && ($value < $this->minAmount || $value > $this->maxAmount))
        {
            $fail("The $attribute field must be between $this->minAmount and $this->maxAmount.");
        }
    }
}
