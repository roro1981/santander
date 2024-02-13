<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\MpfinRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MpfinRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testMpfinValidation()
    {
        $xmlData = '<MPFIN><IDTRX>000100</IDTRX><CODRET>000</CODRET><NROPAGOS>1</NROPAGOS><TOTAL>1234</TOTAL><INDPAGO>S</INDPAGO><IDREG>1523</IDREG></MPFIN>';
        
        $requestData = [
            'IdCarro' => '1523',
            'CodRet' => '000',
            'Estado' => 'ACEPTADO',
            'mpfin' => $xmlData,
        ];

        $request = new MpfinRequest($requestData);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertFalse($validator->fails());

        $this->assertEquals([
            'mpfin' => '<MPFIN><IDTRX>000100</IDTRX><CODRET>000</CODRET><NROPAGOS>1</NROPAGOS><TOTAL>1234</TOTAL><INDPAGO>S</INDPAGO><IDREG>1523</IDREG></MPFIN>',
            'IdCarro' => '1523',
            'CodRet' => '000',
            'Estado' => 'ACEPTADO',
        ], $request->all());
    }

    public function testPrepareForValidation()
    {
        $mpfinRequest = new MpfinRequest();
        $mpfinRequest->prepareForValidation();

        $this->assertFalse(is_array($mpfinRequest->input('mpfin')));
    }

}
