<?php

namespace App\Http\Requests;

use App\Rules\FloatMaxDecimals;

class NotifyRequest extends CustomFormRequest
{

    public function rules(): array
    { 
        return [
            'CODRET' => 'required|numeric|regex:/^\d{4}$/',
            'DESCRET' => 'required|string|max:200',
            'IDCOM' => 'required|string|max:20',
            'IDTRX' => 'required|numeric|digits_between:0,21',
            'TOTAL' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'NROPAGOS' => 'required|numeric|min:1|max:999',
            'FECHATRX' => 'required|date_format:YmdHis',
            'IDREG' => 'required|string|max:20',
        ];
    }
    
}