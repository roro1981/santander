<?php

namespace App\Http\Requests;

use App\Http\Utils\Constants;
use App\Rules\FloatMaxDecimals;
use App\Rules\IsNumeric;
use App\Rules\NumericBetween;
use App\Rules\IntegerMaxLength;
use App\Rules\IntegerPositive;
use App\Rules\IsInteger;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;


class CustomFormRequest extends FormRequest
{
    const REQUIRED = 'required';
    const MAX_3 = 'max:3';
    const MAX_255 = 'max:255';
    const MAX_1024 = 'max:1024';
    const STRING = 'string';
    const NUMERIC = 'numeric';
    const INTEGER = 'integer';
    const EMAIL = 'email:rfc,dns';
    const URL = 'url:http,https';
    const UUID = 'uuid';


    static function getNumericIdRules()
    {
        return [
            self::REQUIRED,
            new IsInteger,
            new IntegerMaxLength(Constants::MAX_INTEGER_LENGTH),
            new IntegerPositive
        ];
    }

    static function getAmountRules(float $minAmount, float $maxAmount)
    {
        return [
            self::REQUIRED,
            new IsNumeric,
            new FloatMaxDecimals,
            new NumericBetween($minAmount, $maxAmount)
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
