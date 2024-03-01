<?php

namespace Tests\Unit;

namespace App\Http\Utils;

use App\Models\ApiLog;
use App\Http\Clients\SantanderClient;
use Exception;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Mockery;
use Database\Seeders\ParameterSeeder;
use Database\Seeders\ParameterSeeder3;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;

class SantanderClientTest extends TestCase
{

    use RefreshDatabase;

    public $token;
    private $flow_id = 000100;
    private $mockSantanderClient;


    public function setUp(): void
    {
        parent::setUp();

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testGetBearerToken()
    {
        $this->seed(ParameterSeeder::class);
        $santanderClient = new SantanderClient();

        $reflectionClass = new ReflectionClass($santanderClient);
        $intentosMax = $reflectionClass->getProperty('intentosMaximos');
        $intentosMax->setValue($santanderClient, 3);

        $tiempo = $reflectionClass->getProperty('intervaloTiempo');
        $tiempo->setValue($santanderClient, 5);

        Http::fake(['*/auth/basic/token' => Http::response(['token_type' => 'Bearer', 'access_token' => 'test_token'])
        ]);

        $token = $santanderClient->getBearerToken($this->flow_id);

        $this->assertEquals('Bearer', $token['token_type']);
        $this->assertEquals('test_token', $token['access_token']);

    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */

    public function testGetBearerToken_ThreeAttempts()
    {
        $santanderClient = new SantanderClient();
        try {
            $result = $santanderClient->getBearerToken($this->flow_id);
        } catch (\Exception $e) {
            $this->assertEquals('Error al obtener el Bearer Token después de 3 intentos', $e->getMessage());
        }
        
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testGetBearerToken_Exception()
    {
        $this->seed(ParameterSeeder::class);

        $service = new SantanderClient();

        Http::fake(['*' => Http::response([], 500)]);

        $reflectionClass = new ReflectionClass($service);
        $intentosMax = $reflectionClass->getProperty('intentosMaximos');
        $intentosMax->setValue($service, 3);

        $tiempo = $reflectionClass->getProperty('intervaloTiempo');
        $tiempo->setValue($service, 5);

        $mockApiLog = Mockery::mock(ApiLog::class);
        $mockApiLog->shouldReceive('storeLog')->andThrow(Exception::class);
        $this->instance(ApiLog::class, $mockApiLog);

        $response = $service->getBearerToken(123, 3);

        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Error al obtener el Bearer Token', $responseData['message']);
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testEnrollCart()
    {
        $this->seed(ParameterSeeder::class);
        $cart_id = random_int(300000,900000);
        $santanderClient = new SantanderClient();

        $reflectionClass = new ReflectionClass($santanderClient);
        $intentosMax = $reflectionClass->getProperty('intentosMaximos');
        $intentosMax->setValue($santanderClient, 3);

        $tiempo = $reflectionClass->getProperty('intervaloTiempo');
        $tiempo->setValue($santanderClient, 5);

        Http::fake([
            '*/auth/apiboton/carro/inscribir' => Http::response(['codeError' => '0', 'descError' => 'Carro inscrito exitosamente', 'tokenBanco' => null, 'urlBanco' => 'https://paymentbutton-bsan-cert.e-pagos.cl/DummyBanco-0.0.2/?IdCom=690006904960&IdCarro=168400]', 'idTrxComercio' => $cart_id], 200),
        ]);

        $order = [
            'car_id' => $cart_id,
            'car_id_transaction' => Uuid::uuid4(),
            'car_flow_currency' => ParamUtil::getParam(Constants::PARAM_CURRENCY),
            'car_flow_amount' => '100.1',
            'car_url' => 'www.flow.cl',
            'car_expires_at' => 1693418602,
            'car_items_number' => 1,
            'car_status' => Constants::STATUS_CREATED,
            'car_url_return' => ParamUtil::getParam(Constants::PARAM_URL_RETORNO),
            'car_sent_kafka' => 0,
            'car_flow_id' => '000100',
            'car_flow_attempt_number' => 0,
            'car_flow_product_id' => '100',
            'car_flow_email_paid' => 'rpanes@tuxpan.com',
            'car_flow_subject' => 'subject test',
            'car_created_at' => now()
        ];

        $response = $santanderClient->enrollCart($order, $this->flow_id, 0);

        $this->assertEquals('0', $response['codeError']);
        $this->assertEquals('Carro inscrito exitosamente', $response['descError']);
        $this->assertEquals(null, $response['tokenBanco']);
        $this->assertEquals($cart_id, $response['idTrxComercio']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testEnrollCart_ExceptionToken()
    {
        $this->seed(ParameterSeeder::class);
        $cart_id = random_int(168500, 300000);

        $serviceMock = Mockery::mock(SantanderClient::class)->makePartial();

        $serviceMock->shouldReceive('getBearerToken')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);

        $this->instance(SantanderClient::class, $serviceMock);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error al inscribir el carro después de 3 intentos');

        $order = [
            'car_id' => $cart_id,
            'car_id_transaction' => Uuid::uuid4(),
            'car_flow_currency' => ParamUtil::getParam(Constants::PARAM_CURRENCY),
            'car_flow_amount' => '100.1',
            'car_url' => 'www.flow.cl',
            'car_expires_at' => 1693418602,
            'car_items_number' => 1,
            'car_status' => Constants::STATUS_CREATED,
            'car_url_return' => ParamUtil::getParam(Constants::PARAM_URL_RETORNO),
            'car_sent_kafka' => 0,
            'car_flow_id' => '000100',
            'car_flow_attempt_number' => 0,
            'car_flow_product_id' => '100',
            'car_flow_email_paid' => 'rpanes@tuxpan.com',
            'car_flow_subject' => 'subject test',
            'car_created_at' => now()
        ];

        $serviceMock->enrollCart($order, $this->flow_id);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testEnrollCartExceptionCatch()
    {
        $this->seed(ParameterSeeder::class);
        $serviceMock = Mockery::mock(SantanderClient::class)->makePartial();

        $authorizationToken = [
            'token_type' => 'bearer token',
            'access_token' => '123'
        ];

        $serviceMock->shouldReceive('getBearerToken')
            ->once()
            ->withAnyArgs()
            ->andReturn($authorizationToken);

        $this->instance(SantanderClient::class, $serviceMock);

        Http::fake(['*/auth/apiboton/carro/inscribir' => function () {
            throw new Exception("Error simulado");
        }]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error al inscribir el carro después de 3 intentos');

        $serviceMock->enrollCart((array)null, $this->flow_id, 3);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testEnrollCart_Exception()
    {
        $this->seed(ParameterSeeder::class);
        $cart_id = random_int(168500, 300000);
        $serviceMock = new SantanderClient();

        $reflectionClass = new ReflectionClass($serviceMock);
        $intentosMax = $reflectionClass->getProperty('intentosMaximos');
        $intentosMax->setValue($serviceMock, 3);

        $tiempo = $reflectionClass->getProperty('intervaloTiempo');
        $tiempo->setValue($serviceMock, 5);

        Http::fake([
            '*/auth/apiboton/carro/inscribir' => Http::response(['codeError' => '20', 'descError' => 'Error interno'], 500),
        ]);

        $order = [
            'car_id' => $cart_id,
            'car_id_transaction' => Uuid::uuid4(),
            'car_flow_currency' => ParamUtil::getParam(Constants::PARAM_CURRENCY),
            'car_flow_amount' => '100.1',
            'car_url' => 'www.flow.cl',
            'car_expires_at' => 1693418602,
            'car_items_number' => 1,
            'car_status' => Constants::STATUS_CREATED,
            'car_url_return' => ParamUtil::getParam(Constants::PARAM_URL_RETORNO),
            'car_sent_kafka' => 0,
            'car_flow_id' => '000100',
            'car_flow_attempt_number' => 0,
            'car_flow_product_id' => '100',
            'car_flow_email_paid' => 'rpanes@tuxpan.com',
            'car_flow_subject' => 'subject test',
            'car_created_at' => now()
        ];

        $response = $serviceMock->enrollCart($order, $this->flow_id, 3);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Error al inscribir el carro', $responseData['message']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    
    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

}
