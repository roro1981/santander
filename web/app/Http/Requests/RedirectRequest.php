<?php

namespace App\Http\Requests;

use App\Http\Requests\CustomFormRequest;
use Illuminate\Validation\Rule;

class RedirectRequest extends CustomFormRequest
{

    public function rules()
    {
        return [
            'IdCarro' => 'required|numeric|digits_between:1,20',
            'CodRet' => ['required', 'string', Rule::in(['000', '001', '002'])],
            'Estado' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $codRet = request()->input('CodRet');
                    $estadoPermitido = match($codRet) {
                        '000' => 'ACEPTADO',
                        '001' => 'RECHAZADO',
                        '002' => 'PENDIENTE',
                        default => null,
                    };
        
                    if ($value !== $estadoPermitido) {
                        $fail("El {$attribute} no es válido para el código de retorno proporcionado.");
                    }
                },
            ],
            'mpfin' => 'required|array',
            'mpfin.IDTRX' => 'required|numeric|digits_between:1,20',
            'mpfin.CODRET' => 'required|string|in:00,001,002',
            'mpfin.NROPAGOS' => 'required|numeric|min:1|max:99',
            'mpfin.TOTAL' => 'required|numeric|digits_between:1,18',
            'mpfin.INDPAGO' => 'required|string|in:S,N',
            'mpfin.IDREG' => 'required|numeric|digits_between:1,20'
        ];
    }

    public function prepareForValidation()
    {
        $mpfinXml = (!empty($this->input('mpfin')) && $this->input('mpfin') != 'null') ? $this->input('mpfin') : '';
        $mpfinArray = json_decode(json_encode(simplexml_load_string($mpfinXml)), true);
        $this->merge(['mpfin' => $mpfinArray]);
    }
    
}
