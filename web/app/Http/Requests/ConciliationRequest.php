<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConciliationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idCarro' => 'required|numeric',
            'idConvenio' => 'required|numeric',
            'numeroProducto' => 'string|max:12',
            'numeroCliente' => 'string|max:12',
            'expiracionProducto' => 'date_format:d/m/Y H:i:s',
            'descProducto' => 'required|string|max:256',
            'montoProducto' => 'required|numeric',
            'numeroOperacion' => 'numeric',
            'fechaOperacion' => 'required|date_format:d/m/Y H:i:s',
        ];
    }
}
