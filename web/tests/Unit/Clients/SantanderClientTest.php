<?php

namespace Tests\Unit;
namespace App\Http\Utils;
use App\Http\Clients\SantanderClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Mockery;
use Database\Seeders\ParameterSeeder;

class SantanderClientTest extends TestCase
{
    public $token;

    public function testGetBearerToken()
    {
        $this->seed(ParameterSeeder::class);
        Http::fake([
            '*/auth/basic/token' => Http::response(['token_type' => 'Bearer', 'access_token' => 'test_token'], 200),
        ]);

        $mockSantanderResponse="Bearer test_token";
        
        $mockSantanderClient = Mockery::mock('overload:' . SantanderClient::class);
        $mockSantanderClient->shouldReceive('getBearerToken')->andReturn($mockSantanderResponse);
        $this->instance(SantanderClient::class, $mockSantanderClient);
        $santanderClient = new SantanderClient();
        $token = $santanderClient->getBearerToken();
        $this->assertEquals('Bearer test_token', $token);
       
    }

    public function testEnrollCart()
    {
        Http::fake([
            '*' => Http::response(['your_response_data'], 200),
        ]);

        $mockSantanderResponse='{
            "codigo": "000",
            "descripcion": "Transaccion Ok",
            "errorInterno": false
        }';
        $order = [
            'car_id' => 1800,
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
        $mockSantanderClient = Mockery::mock('overload:' . SantanderClient::class);
        $mockSantanderClient->shouldReceive('enrollCart')->andReturn($mockSantanderResponse);
        $this->instance(SantanderClient::class, $mockSantanderClient);
        $santanderClient = new SantanderClient();
        $response = $santanderClient->enrollCart($order);
        $this->assertEquals($mockSantanderResponse, $response);
    }

}
