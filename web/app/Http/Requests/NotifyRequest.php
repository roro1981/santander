<?php

namespace App\Http\Requests;

use App\Rules\FloatMaxDecimals;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;

class NotifyRequest extends CustomFormRequest
{

    public function rules(): array
    { 
        $params = ParamUtil::getParams([
            Constants::PARAM_ORDER_MIN_AMOUNT,
            Constants::PARAM_ORDER_MAX_AMOUNT
        ]);
        return [
            'CODRET' => 'required|numeric|regex:/^\d{4}$/',
            'DESCRET' => 'required|string|max:200',
            'IDCOM' => 'required|string|max:20',
            'IDTRX' => 'required|numeric|digits_between:1,20',
            'TOTAL' => $this->getAmountRules($params[Constants::PARAM_ORDER_MIN_AMOUNT], $params[Constants::PARAM_ORDER_MAX_AMOUNT]),
            'NROPAGOS' => 'required|numeric|min:1|max:99',
            'FECHATRX' => 'required|date_format:YmdHis',
            'FECHACONT' => 'date_format:Ymd',
            'NUMCOMP' => 'numeric|digits_between:1,20',
            'IDREG' => 'required|string|max:20',
        ];
    }
    
}