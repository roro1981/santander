<?php

namespace App\Http\Requests;

use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;
use App\Rules\IntegerBetween;
use App\Rules\IsInteger;

class CreateOrderRequest extends CustomFormRequest
{
    public function rules(): array
    {
        $params = ParamUtil::getParams([
            Constants::PARAM_ALLOWED_METHODS,
            Constants::PARAM_ALLOWED_CURRENCIES,
            Constants::PARAM_MIN_AMOUNT,
            Constants::PARAM_MAX_AMOUNT
        ]);

        return [
            'uuid' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'order.id' => $this->getNumericRules(),
            'order.product_id' => $this->getNumericRules(),
            'order.method_id' => array_merge($this->getNumericRules(), ['in:' . $params[Constants::PARAM_ALLOWED_METHODS]]),
            'order.url_confirmation' => self::REQUIRED . '|' . self::STRING . '|' . self::URL . '|' . self::MAX_255,
            'order.url_return' => self::REQUIRED . '|' . self::STRING . '|' . self::URL . '|' . self::MAX_255,
            'order.attempt_number' => $this->getNumericRules(),
            'order.amount' => $this->getAmountRules($params[Constants::PARAM_MIN_AMOUNT], $params[Constants::PARAM_MAX_AMOUNT]),
            'order.subject' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'order.expiration' => [self::REQUIRED, new IsInteger, new IntegerBetween],
            'order.currency' => self::REQUIRED . '|' . self::STRING . '|size:3|in:' . $params[Constants::PARAM_ALLOWED_CURRENCIES],
            'order.email_paid' => self::REQUIRED . '|' . self::STRING . '|' . self::EMAIL . '|' . self::MAX_255,
            'order.extra_params' => 'array|nullable',
            'user.id' => $this->getNumericRules(),
            'user.email' => self::REQUIRED . '|' . self::STRING . '|' . self::EMAIL . '|' . self::MAX_255,
            'user.legal_name' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'user.tax_id' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'user.address' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'user.fantasy_name' => 'nullable|' . self::STRING . '|' . self::MAX_255,
        ];
    }
}
