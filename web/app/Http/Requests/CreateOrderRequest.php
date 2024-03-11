<?php

namespace App\Http\Requests;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;

class CreateOrderRequest extends CustomFormRequest
{
    public function rules(): array
    {
        $params = ParamUtil::getParams([
            Constants::PARAM_ORDER_MIN_AMOUNT,
            Constants::PARAM_ORDER_MAX_AMOUNT
        ]);
        return [
            'uuid' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'order.id' => self::REQUIRED . '|' . self::NUMERIC,
            'order.product_id' => self::REQUIRED . '|' . self::NUMERIC,
            'order.method_id' => self::REQUIRED . '|' . self::NUMERIC,
            'order.url_confirmation' => self::REQUIRED . '|' . self::STRING . '|' . self::URL . '|' . self::MAX_255,
            'order.url_return' => self::REQUIRED . '|' . self::STRING . '|' . self::URL . '|' . self::MAX_255,
            'order.attempt_number' => self::REQUIRED. '|' . self::NUMERIC,
            'order.amount' => $this->getAmountRules($params[Constants::PARAM_ORDER_MIN_AMOUNT], $params[Constants::PARAM_ORDER_MAX_AMOUNT]),
            'order.subject' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'order.expiration' => self::REQUIRED. '|' . self::NUMERIC,
            'order.currency' => self::REQUIRED . '|' . self::STRING,
            'order.extra_params' => 'array|nullable',
            'user.id' => self::REQUIRED . '|' . self::NUMERIC,
            'user.email' => self::REQUIRED . '|' . self::STRING . '|' . self::EMAIL . '|' . self::MAX_255,
            'user.legal_name' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'user.tax_id' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'user.address' => self::REQUIRED . '|' . self::STRING . '|' . self::MAX_255,
            'user.fantasy_name' => 'nullable|' . self::STRING . '|' . self::MAX_255,
        ];
    }
}
