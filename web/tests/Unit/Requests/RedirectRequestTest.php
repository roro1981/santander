<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\RedirectRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class RedirectRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testRedirectValidation()
    {
        $xmlData = '<MPFIN><IDTRX>000100</IDTRX><CODRET>000</CODRET><NROPAGOS>1</NROPAGOS><TOTAL>1234</TOTAL><INDPAGO>S</INDPAGO><IDREG>1523</IDREG></MPFIN>';
        
        $requestData = [
            'IdCarro' => '1523',
            'CodRet' => '000',
            'Estado' => 'ACEPTADO',
            'mpfin' => $xmlData,
        ];

        $request = new RedirectRequest($requestData);
        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);
        $validator = $this->app['validator']->make($request->all(), $request->rules());
        
        $this->assertFalse($validator->fails());
        
        $this->assertEquals([
            'IdCarro' => '1523',
            'CodRet' => '000',
            'Estado' => 'ACEPTADO',
            'mpfin' => [
                "IDTRX" => "000100",
                "CODRET" => "000",
                "NROPAGOS" => "1",
                "TOTAL" => "1234",
                "INDPAGO" => "S",
                "IDREG" => "1523"
            ]
        ], $request->all());
    }

    public function testPrepareForValidation()
    {
        $redirectRequest = new RedirectRequest();
        $redirectRequest->prepareForValidation();

        $this->assertFalse(is_array($redirectRequest->input('mpfin')));
    }

}
