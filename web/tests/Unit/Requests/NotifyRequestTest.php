<?php

namespace Tests\Unit\Requests;
namespace App\Http\Requests;
use App\Http\Requests\NotifyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Traits\XmlConversionTrait;
use ReflectionMethod;
use Mockery;

class NotifyRequestTest extends TestCase
{
    use RefreshDatabase;
    use XmlConversionTrait;

    public function testValidationPasses()
    {
        $requestData = [
            "CODRET"=> "0000",
            "DESCRET"=> "Transaccion OK",
            "IDCOM"=> "7683001403",
            "IDTRX"=> "00000000001",
            "TOTAL"=> 1199,
            "NROPAGOS"=> "1",
            "FECHATRX"=> "20240314110848",
            "FECHACONT"=> "20240314",
            "NUMCOMP"=> "1710425328291",
            "IDREG"=> "202053"
        ];

        $request = new NotifyRequest($requestData);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertFalse($validator->fails(), 'La validación falló: ' . implode(', ', $validator->errors()->all()));

        $this->assertEquals([
            "CODRET"=> "0000",
            "DESCRET"=> "Transaccion OK",
            "IDCOM"=> "7683001403",
            "IDTRX"=> "00000000001",
            "TOTAL"=> "1199",
            "NROPAGOS"=> "1",
            "FECHATRX"=> "20240314110848",
            "FECHACONT"=> "20240314",
            "NUMCOMP"=> "1710425328291",
            "IDREG"=> "202053"
        ], $request->all());
    }

    public function testValidationFailsWithoutTxPrefix()
    {
        $requestData = [
            'CODRET' => '0000',
            'DESCRET' => 'Transaccion OK',
            'IDCOM' => '7683001403',
            'IDTRX' => 1508,
            'TOTAL' => '1234',
            'MONEDA' => 'CLP',
            'NROPAGOS' => '0',
            'FECHATRX' => '24/01/2024 14:53:52',
            'IDTRXREC' => '167896',
        ];

        $request = new NotifyRequest($requestData);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertTrue($validator->fails(), 'La validación debería fallar sin el prefijo TX');
    }

}