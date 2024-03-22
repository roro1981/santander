<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedirectRequest extends FormRequest
{

    public function rules()
    {
        return [
            'IdCarro' => 'required',
            'CodRet' => 'required',
            'Estado' => 'required',
            'mpfin' => 'required'
        ];

        /*  
            IdCarro' => 'required|numeric|digits_between:1,20',
            'CodRet' => 'required|string|in:001,002,003',
            'Estado' => 'required|string|in:ACEPTADO,RECHAZADO,PENDIENTE',
            'mpfin' => 'required|array',
            'mpfin.*.IDTRX' => 'required|numeric|digits_between:1,20',
            'mpfin.*.CODRET' => 'required|string|in:001,002,003',
            'mpfin.*.NROPAGOS' => 'required|numeric|min:1|max:99',
            'mpfin.*.TOTAL' => 'required|numeric|digits_between:1,18',
            'mpfin.*.INDPAGO' => 'required|string|in:S,N',
            'mpfin.*.IDREG' => 'required|numeric|digits_between:1,20'
        */
    }

    public function prepareForValidation()
    {
        $mpfinXml = $this->input('mpfin');
        $mpfinArray = json_decode(json_encode(simplexml_load_string($mpfinXml)), true);
        $this->merge(['mpfin' => $mpfinArray]);
    }

}
