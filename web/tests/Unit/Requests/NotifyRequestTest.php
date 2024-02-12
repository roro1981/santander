<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\NotifyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Traits\XmlConversionTrait;

class NotifyRequestTest extends TestCase
{
    use RefreshDatabase;
    use XmlConversionTrait;

    public function testValidationPasses()
    {

        $requestData = [
            "TX" => [
                "CODRET" => "0000",
                "DESCRET" => "Transaccion OK",
                "IDCOM" => "7683001403",
                "IDTRX" => 1508,
                "TOTAL" => "1234",
                "MONEDA" => "CLP",
                "NROPAGOS" => "0",
                "FECHATRX" => "24/01/2024 14:53:52",
                "IDTRXREC" => "167896"
              ]
        ];

        $request = new NotifyRequest($requestData);

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertFalse($validator->fails(), 'La validación falló: ' . implode(', ', $validator->errors()->all()));

        $this->assertEquals(['TX' => [
            'CODRET' => '0000',
            'DESCRET' => 'Transaccion OK',
            'IDCOM' => '7683001403',
            'IDTRX' => 1508,
            'TOTAL' => '1234',
            'MONEDA' => 'CLP',
            'NROPAGOS' => "0",
            'FECHATRX' => '24/01/2024 14:53:52',
            'IDTRXREC' => '167896',
        ]], $request->all());
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

public function testConvertXmlToArray()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <root>
                <item>
                    <id>1</id>
                    <name>valor1</name>
                </item>
                <item>
                    <id>2</id>
                    <name>valor2</name>
                </item>
            </root>';

        $result = $this->convertXmlToArray($xml);

        $expectedResult = [
            'item' => [
                ['id' => '1', 'name' => 'valor1'],
                ['id' => '2', 'name' => 'valor2']
            ]
        ];

        $this->assertEquals($expectedResult, $result);
    }
}