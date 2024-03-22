<?php

namespace App\Http\Requests;

use App\Rules\FloatMaxDecimals;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;
use App\Rules\IntegerMaxLength;

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
            'TOTAL' => 'required|numeric|decimalsinzero',
            'NROPAGOS' => 'required|numeric|min:1|max:99',
            'FECHATRX' => 'required|date_format:YmdHis',
            'FECHACONT' => 'date_format:Ymd',
            'NUMCOMP' => 'numeric|digits_between:1,20',
            'IDREG' => 'required|string|max:20',
        ];
    }
    
}