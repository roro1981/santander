<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MpfinRequest extends FormRequest
{

    public function rules()
    {
        return [
            'IdCarro' => 'required',
            'CodRet' => 'required',
            'Estado' => 'required',
            'mpfin' => 'required',
        ];
    }

    public function prepareForValidation()
    {
        $mpfinXml = $this->input('mpfin');
        $mpfinArray = json_decode(json_encode(simplexml_load_string($mpfinXml)), true);
        $this->merge(['mpfin' => $mpfinArray]);
    }

}
