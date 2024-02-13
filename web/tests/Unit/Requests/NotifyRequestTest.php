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
        $xml = '<MPOUT><CODRET>0000</CODRET><DESCRET>Transaccion OK</DESCRET><IDCOM>7683001403</IDCOM><IDTRX>00000000000168403</IDTRX><TOTAL>1199</TOTAL><MONEDA>CLP</MONEDA><NROPAGOS>0</NROPAGOS><FECHATRX>24/01/2024 14:53:52</FECHATRX><IDTRXREC>168403</IDTRXREC></MPOUT>';

        $request = new NotifyRequest();

        $result = $request->convertXmlToArray($xml);
    
        $expectedArray = [
            "CODRET" => "0000",
            "DESCRET" => "Transaccion OK",
            "IDCOM" => "7683001403",
            "IDTRX" => "00000000000168403",
            "TOTAL" => "1199",
            "MONEDA" => "CLP",
            "NROPAGOS" => "0",
            "FECHATRX" => "24/01/2024 14:53:52",
            "IDTRXREC" => "168403"
          ] ; 

        $this->assertEquals($expectedArray, $result);
    }
    public function testPrepareForValidation()
    {
        $xmlData = 'TX=<MPOUT><CODRET>200</CODRET>
                <DESCRET>OK</DESCRET>
                <IDCOM>123</IDCOM>
                <IDTRX>123456</IDTRX>
                <TOTAL>100.00</TOTAL>
                <MONEDA>CLP</MONEDA>
                <NROPAGOS>1</NROPAGOS>
                <FECHATRX>2022-02-18 12:00:00</FECHATRX>
                <IDTRXREC>789</IDTRXREC>
            </MPOUT>';

        $mpfinRequest = new NotifyRequest();

        $mpfinRequest->prepareForValidation($xmlData);

        $txData = $mpfinRequest->input('TX');

        $this->assertNotNull($txData);
        $this->assertEquals('123456', $txData['IDTRX']); 
        $this->assertIsNumeric($txData['IDTRX']);
        $this->assertEquals('CLP', $txData['MONEDA']);
    }
}