<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class CustomFormRequest extends FormRequest
{
    const REQUIRED = 'required';
    const MAX_255 = 'max:255';
    const MAX_1024 = 'max:1024';
    const STRING = 'string';
    const NUMERIC = 'numeric';
    const INTEGER = 'integer';
    const EMAIL = 'email:rfc,dns';
    const URL = 'url:http,https';

   
}
