<?php

namespace Tests\Unit\Controllers;

use App\Http\Clients\SantanderClient;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Utils\Constants;
use App\Models\Idempotency;
use App\Models\CartStatus;
use App\Models\ApiLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;


class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private $mockRequestData;
    private $mockCartStatus;
    private $method;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockCartStatus = Mockery::mock('overload:' . CartStatus::class);
        $this->seed();
        $this->method = 149;
        $this->mockRequestData = [
            'uuid' => Uuid::uuid4(),
            'order' => [
                'id' => '999',
                'product_id' => '1',
                'method_id' => $this->method,
                'url_confirmation' => 'https://flow.cl/confirmacion.php',
                'url_return' => 'https://flow.cl/retorno.php',
                'attempt_number' => '1',
                'amount' => 10.200,
                'currency' => '999',
                'subject' => 'Unit Test',
                'email_paid' => 'test@flow.cl',
                'expiration' => time() + 86400,
            ],
            'user' => [
                'id' => '1',
                'email' => 'test@flow.cl',
                'legal_name' => 'Testing',
                'tax_id' => '11111111-1',
                'address' => 'Santiago',
                'fantasy_name' => 'Testing',
            ],
        ];

        Idempotency::create([
            'idp_uuid' => '75ed0a39-5037-40d2-aa7f-5bc1742a7d1f',
            'idp_response' => json_encode([
                'uuid' => '924fb7dd-f362-47c4-997f-8b7c0925686a',
                'payment_uuid' => 'oca1k8nhhigb',
                'provider_id' => '01',
                'type' => 'REDIRECT',
                'amount' => 10.2300,
                'currency' => 'PEN',
                'url' => 'https://santander.com/payment/info/oca1k8nhhigb',
                'expire_time' => '70076723',
                'expire_date' => '2023-11-23T19:20:30+00:00',
            ]),
            'idp_httpcode' => 200
        ]);
    }

    public function testCreateOrder()
    {
        $mockRequest = Mockery::mock(CreateOrderRequest::class);
        $mockRequest->shouldReceive('validated')->andReturn($this->mockRequestData);
        $mockRequest->shouldReceive('url')->andReturn('https://ejemplo.com');
    
        $this->instance(CreateOrderRequest::class, $mockRequest);
        $mockSantanderResponse = [
            'codeError' => '0',
            'descError' => 'Carro inscrito exitosamente',
            'tokenBanco' => null,
            'urlBanco' => 'https://paymentbutton-bsan-cert.e-pagos.cl/DummyBanco-0.0.2/?IdCom=690006904960&IdCarro=167814',
            'idTrxComercio' => '48'
        ];

        $this->mockCartStatus->shouldReceive('saveCurrentStatus')->andReturnUsing(function ($cart) {
            return new CartStatus([
                'car_id' => $cart->car_id,
                'cas_status' => $cart->car_status
            ]);
        });
        $this->instance(CartStatus::class, $this->mockCartStatus);

        $mockSantanderClient = Mockery::mock('overload:' . SantanderClient::class);
        $mockSantanderClient->shouldReceive('enrollCart')->andReturn($mockSantanderResponse);
        $this->instance(SantanderClient::class, $mockSantanderClient);

        $response = $this->post('/api/v1/order/create', $this->mockRequestData);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->status());
    }

    public function testCreateOrderException()
    {
        $mockRequest = Mockery::mock(CreateOrderRequest::class);
        $mockRequest->shouldReceive('validated')->andReturn($this->mockRequestData);
        $this->instance(CreateOrderRequest::class, $mockRequest);

        $mockSantanderResponse = [
            'codError' => '0',
            'descError' => 'Carro inscrito exitosamente',
            'tokenBanco' => null,
            'urlBanco' => 'https://paymentbutton-bsan-cert.e-pagos.cl/DummyBanco-0.0.2/?IdCom=690006904960&IdCarro=167814',
            'idTrxComercio' => '48'
        ];
        $mockSantanderClient = Mockery::mock('overload:' . SantanderClient::class);
        $mockSantanderClient->shouldReceive('enrollCart')->andReturn($mockSantanderResponse);
        $this->instance(SantanderClient::class, $mockSantanderClient);

        $this->mockCartStatus->shouldReceive('saveCurrentStatus')->andThrow('Exception', 'Test error');

        $response = $this->post('/api/v1/order/create', $this->mockRequestData);
        $this->assertNotNull($response);
        $this->assertEquals(500, $response->status());
    }

    public function testCreateOrderIdempotency()
    {
        $this->mockRequestData['uuid'] = '75ed0a39-5037-40d2-aa7f-5bc1742a7d1f';
        $mockRequest = Mockery::mock(CreateOrderRequest::class);
        $mockRequest->shouldReceive('validated')->andReturn($this->mockRequestData);
        $this->instance(CreateOrderRequest::class, $mockRequest);

        $response = $this->post('/api/v1/order/create', $this->mockRequestData);
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('924fb7dd-f362-47c4-997f-8b7c0925686a', $response['uuid']);
        $this->assertEquals('oca1k8nhhigb', $response['payment_uuid']);
        $this->assertEquals(10.23, $response['amount']);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
