<?php

namespace App\Http\Requests;

use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomFormRequest extends FormRequest
{
    const REQUIRED = 'required';
    const MAX_255 = 'max:255';
    const MAX_1024 = 'max:1024';
    const STRING = 'string';
    const NUMERIC = 'numeric';
    const INTEGER = 'integer';
    const EMAIL = 'email:rfc,dns';
    const URL = 'url:http,https';

    public function getNumericRules()
    {
        return [
            self::REQUIRED,
            function ($attribute, $value, $fail) {
                if (!is_int($value))
                {
                    $fail("The $attribute field must be an integer.");
                }
            },
            function ($attribute, $value, $fail) {
                if (is_int($value))
                {
                    $maxLength = ParamUtil::getParam(Constants::MAX_INTEGER_LENGTH);
                    if(strlen(str($value)) > $maxLength)
                    {
                        $fail("The $attribute field must not have more than $maxLength digits.");
                    }
                }
            },
            function ($attribute, $value, $fail) {
                if (is_int($value) && $value < 1)
                {
                    $fail("The $attribute field must be greater than 0.");
                }
            },
        ];
    }

    public function getAmountRules($minAmount, $maxAmount)
    {
        return [
            self::REQUIRED,
            function ($attribute, $value, $fail) {
                if (!is_int($value) && !is_float($value))
                {
                    $fail("The $attribute field must be numeric.");
                }
            },
            function ($attribute, $value, $fail) {
                if (is_float($value))
                {
                    $decimal = explode('.' , str($value));
                    if (isset($decimal[1]) && strlen($decimal[1]) > 2)
                    {
                        $fail("The $attribute field must have 0-2 decimal places.");
                    }
                }
            },
            function ($attribute, $value, $fail) use ($minAmount, $maxAmount){
                if ((is_int($value) || is_float($value)) && ($value < $minAmount || $value > $maxAmount))
                {
                    $fail("The $attribute field must be between $minAmount and $maxAmount.");
                }
            }
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $failedRules = $validator->failed();

        $errors = collect($validator->errors())->map(function ($error, $field) use ($failedRules) {
            return collect($error)->map(function ($message) use ($field, $failedRules) {
                $rule = array_keys($failedRules[$field])[0];

                return [
                    'rule' => $rule,
                    'field' => $field,
                    'message' => $message
                ];
            });
        })->flatten(1);

        throw new HttpResponseException(response()->json([
            'code' => 400,
            'message' => 'Bad Request',
            'errors' => $errors
        ], 400));
    }
}
