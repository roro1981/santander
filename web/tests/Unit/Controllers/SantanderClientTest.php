<?php

namespace Tests\Unit;
namespace App\Http\Utils;
use App\Http\Clients\SantanderClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Mockery;
use Database\Seeders\ParameterSeeder;

class SantanderClientTest extends TestCase
{
    public $token;
    private $flow_id=000100;
    private $mockSantanderClient;


    public function setUp(): void
    {
        parent::setUp();
        $this->mockSantanderClient = Mockery::mock('overload:' . SantanderClient::class);
        //$this->santanderClient->shouldReceive('getBearerToken')->withArgs([$this->flow_id]);
    }

    public function testGetBearerToken()
    {
        $this->seed(ParameterSeeder::class);

        Http::fake([
            '*/auth/basic/token' => Http::response(['token_type' => 'Bearer', 'access_token' => 'test_token'], 200)
        ]);
        
        $this->mockSantanderClient
            ->shouldReceive('getBearerToken')
            ->with($this->flow_id)
            ->andReturn(['token_type' => 'Bearer', 'access_token' => 'test_token']);

        $this->app->instance(SantanderClient::class, $this->mockSantanderClient);

        $token = $this->mockSantanderClient->getBearerToken($this->flow_id);

        $this->assertEquals('Bearer', $token['token_type']);
        $this->assertEquals('test_token', $token['access_token']);
       
    }

    public function testEnrollCart()
    {
        $cart_id=random_int(168500,300000);
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

        $this->mockSantanderClient
            ->shouldReceive('enrollCart')
            ->with($order, $this->flow_id)
            ->andReturn(['codeError' => '0', 'descError' => 'Carro inscrito exitosamente', 'tokenBanco' => null, 'urlBanco' => 'https://paymentbutton-bsan-cert.e-pagos.cl/DummyBanco-0.0.2/?IdCom=690006904960&IdCarro=168400]', 'idTrxComercio' => $cart_id]);

        $this->app->instance(SantanderClient::class, $this->mockSantanderClient);
      
        $response = $this->mockSantanderClient->enrollCart($order, $this->flow_id);
        
        $this->assertEquals('0', $response['codeError']);
        $this->assertEquals('Carro inscrito exitosamente', $response['descError']);
        $this->assertEquals(null, $response['tokenBanco']);
        $this->assertEquals($cart_id, $response['idTrxComercio']);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

}
