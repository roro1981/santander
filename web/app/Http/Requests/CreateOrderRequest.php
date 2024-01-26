<?php

namespace App\Http\Requests;

use App\Rules\IntegerBetween;
use App\Rules\IsInteger;

class CreateOrderRequest extends CustomFormRequest
{
    public function rules(): array
    {

        return [
            'uuid' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'order.id' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'order.product_id' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'order.method_id' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'order.url_confirmation' => self::REQUIRED . '|' . self::STRING . '|' . self::URL . '|' . self::MAX_255,
            'order.url_return' => self::REQUIRED . '|' . self::STRING . '|' . self::URL . '|' . self::MAX_255,
            'order.attempt_number' => self::REQUIRED. '|' . self::NUMERIC,
            'order.amount' => self::REQUIRED. '|' . self::NUMERIC,
            'order.subject' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'order.expiration' => [self::REQUIRED, new IsInteger, new IntegerBetween],
            'order.currency' => self::REQUIRED . '|' . self::STRING,
            'order.extra_params' => 'array|nullable',
            'user.id' => self::REQUIRED . '|' . self::STRING,
            'user.email' => self::REQUIRED . '|' . self::STRING . '|' . self::EMAIL . '|' . self::MAX_255,
            'user.legal_name' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'user.tax_id' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'user.address' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'user.fantasy_name' => 'nullable|' . self::STRING . '|' . self::MAX_255,
        ];
    }
}
