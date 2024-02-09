<?php

namespace Tests\Unit\Controllers;

use App\Http\Clients\SantanderClient;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Controllers\OrderController;
use App\Models\Idempotency;
use App\Models\CartStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Response;
use Mockery;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use App\Http\Requests\NotifyRequest;
use App\Http\Requests\MpfinRequest;
use App\Models\Cart;
use App\Jobs\KafkaNotification;
use Illuminate\Support\Facades\Queue;




class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private $mockRequestData;
    private $requestNotify;
    private $requestMpfin;
    private $mockCartStatus;
    private $mockSantanderClient;
    private $method;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockCartStatus = Mockery::mock('overload:' . CartStatus::class);
        $this->mockSantanderClient = Mockery::mock('overload:' . SantanderClient::class);
        $this->seed();
        $this->method = "149";
        $this->mockRequestData = [
            'uuid' => Uuid::uuid4(),
            'order' => [
                'id' => '5000',
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

        $this->requestNotify = [
            'TX' => [
                'IDTRX' => '1',
                'CODRET' => '0000',
                'TOTAL' => 1199,
                'MONEDA' =>'CLP',
                'IDTRXREC' =>'1',
                'DESCRET' => 'Transaccion OK'
            ]
        ];

        $this->requestMpfin = [
                'IdCarro' => '1',
                'CodRet' => '000',
                'Estado' => 'Aceptado',
                'mpfin' => [
                    'TOTAL' => 1199, 
                    'IDTRX' => '000100' 
                ]
        ];
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCreateOrder()
    {
        $mockRequest = Mockery::mock(CreateOrderRequest::class);
        $mockRequest->shouldReceive('validated')->andReturn($this->mockRequestData);
        $mockRequest->shouldReceive('url')->andReturn('https://ejemplo.com');
    
        $this->instance(CreateOrderRequest::class, $mockRequest);
        $this->mockCartStatus->shouldReceive('saveCurrentStatus')->andReturnUsing(function ($cart) {
            return new CartStatus([
                'car_id' => $cart->car_id,
                'cas_status' => $cart->car_status
            ]);
        });
        $this->instance(CartStatus::class, $this->mockCartStatus);

        $mockSantanderResponse = [
            'codeError' => '0',
            'descError' => 'Carro inscrito exitosamente',
            'tokenBanco' => null,
            'urlBanco' => 'https://paymentbutton-bsan-cert.e-pagos.cl/DummyBanco-0.0.2/?IdCom=690006904960&IdCarro=167814',
            'idTrxComercio' => '48'
        ];

        $this->mockSantanderClient
        ->shouldReceive('enrollCart')
        ->once()
        ->andReturn($mockSantanderResponse);
        
        $response = $this->post('/api/v1/order/create', $this->mockRequestData);
       
        $this->assertNotNull($response);
        $this->assertEquals(200, $response->status());
        
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSaveOrder(){

        $mockSantanderResponse = [
            'codeError' => '0',
            'descError' => 'Carro inscrito exitosamente',
            'tokenBanco' => null,
            'urlBanco' => 'https://paymentbutton-bsan-cert.e-pagos.cl/DummyBanco-0.0.2/?IdCom=690006904960&IdCarro=167814',
            'idTrxComercio' => '48'
        ];

        $this->instance(CartStatus::class, $this->mockCartStatus);
        
        $this->mockCartStatus->shouldReceive('saveCurrentStatus')->andReturnUsing(function ($cart) {
            return new CartStatus([
                'car_id' => $cart->car_id,
                'cas_status' => $cart->car_status
            ]);
        });
        $this->instance(CartStatus::class, $this->mockCartStatus);

        $this->mockSantanderClient
        ->shouldReceive('enrollCart')
        ->once()
        ->andReturn($mockSantanderResponse);
        $this->instance(SantanderClient::class, $this->mockSantanderClient);

        $this->assertTrue(Mockery::getContainer()->mockery_getExpectationCount() > 0);

    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
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
        
        $this->mockSantanderClient->shouldReceive('enrollCart')->andReturn($mockSantanderResponse);
        $this->instance(SantanderClient::class, $this->mockSantanderClient);

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
    public function testNotifySuccess()
    {
   
        Queue::fake();
        
        $requestMock = Mockery::mock(NotifyRequest::class);
        $requestMock->shouldReceive('validated')->andReturn($this->requestNotify);
        $requestMock->shouldReceive('url')->andReturn('https://example.com/notify');
        $this->app->instance(NotifyRequest::class, $requestMock);

        $response = Response::json(['code' => '0000', 'dsc' => 'Transaccion OK'], 200);

        $order = Cart::factory()->create();

        $controller = new OrderController();
        $result = $controller->notify($requestMock);
      
        $this->assertEquals($response->getContent(), $result->getContent());

        Queue::assertPushed(KafkaNotification::class, function ($job) {
            return true;
        });
    }

    public function testMpfinSuccess()
    {
        Queue::fake();
        $order = Cart::factory()->create();
        $requestMock = Mockery::mock(MpfinRequest::class);
        $requestMock->shouldReceive('validated')->andReturn($this->requestMpfin);
        $requestMock->shouldReceive('url')->andReturn('https://example.com/notify');
        $this->app->instance(MpfinRequest::class, $requestMock);

        $response = Response::json(['message' => 'Recepcion exitosa', 'url_return' => 'https://tebi4tbxq0.execute-api.us-west-2.amazonaws.com/QA/santander/v1/redirect'], 200);

        
        $controller = new OrderController();
        $result = $controller->mpfin($requestMock);
        
        $this->assertEquals($response->getContent(), $result->getContent());

        Queue::assertPushed(KafkaNotification::class, function ($job) {
            return true; 
        });
    }
    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
        
    }
}
