<?php

namespace App\Http\Requests;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;
use App\Rules\IntegerMaxLength;


class CreateOrderRequest extends CustomFormRequest
{
    public function rules(): array
    {
        $params = ParamUtil::getParams([
            Constants::PARAM_ORDER_MIN_AMOUNT,
            Constants::PARAM_ORDER_MAX_AMOUNT
        ]);
        return [
            'uuid' => self::REQUIRED . '|' . self::STRING . '|' . self::UUID,
            'order.id' => $this->getNumericIdRules(),
            'order.product_id' => $this->getNumericIdRules(),
            'order.method_id' => self::REQUIRED . '|' . self::NUMERIC,
            'order.url_confirmation' => self::REQUIRED . '|' . self::STRING . '|' . self::URL . '|' . self::MAX_255,
            'order.url_return' => self::REQUIRED . '|' . self::STRING . '|' . self::URL . '|' . self::MAX_255,
            'order.attempt_number' => $this->getNumericIdRules(),
            'order.amount' => $this->getAmountRules($params[Constants::PARAM_ORDER_MIN_AMOUNT], $params[Constants::PARAM_ORDER_MAX_AMOUNT]) ,
            'order.subject' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'order.expiration' => self::REQUIRED . '|'. self::NUMERIC . '|' . '| between:1,2147483647',
            'order.email_paid' => 'nullable|' . self::STRING . '|' . self::EMAIL . '|' . self::MAX_255,
            'order.currency' => self::REQUIRED . '|' . self::STRING. '|' . self::MAX_3 . '|in:' . ParamUtil::getParam(Constants::PARAM_CURRENCY),
            'order.extra_params' => 'array|nullable',
            'order.extra_params.*.key' => 'nullable|' . self::STRING,
            'order.extra_params.*.value' => 'nullable|' . self::STRING,
            'order.email_paid' => 'nullable|' . self::STRING . '|' . self::EMAIL . '|' . self::MAX_255,
            'user.id' => "|nullable|digits_between:1,11",
            'user.email' => self::REQUIRED . '|' . self::STRING . '|' . self::EMAIL . '|' . self::MAX_255,
            'user.legal_name' => 'nullable|' . self::STRING . '|' . self::MAX_255,
            'user.tax_id' => 'nullable|' . self::STRING . '|' . self::MAX_255,
            'user.address' => 'nullable|' . self::STRING . '|' . self::MAX_255,
            'user.fantasy_name' => 'nullable|' . self::STRING . '|' . self::MAX_255,
        ];
    }
}
