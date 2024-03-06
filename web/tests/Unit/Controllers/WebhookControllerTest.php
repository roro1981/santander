<?php

namespace Tests\Unit\Controllers;

use App\Http\Clients\SantanderClient;
use App\Http\Controllers\WebhookController;
use App\Models\Idempotency;
use App\Models\CartStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Response;
use Mockery;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use App\Http\Requests\NotifyRequest;
use App\Http\Requests\RedirectRequest;
use App\Models\Cart;
use App\Jobs\KafkaNotification;
use Illuminate\Support\Facades\Queue;




class WebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    private $mockRequestData;
    private $requestNotify;
    private $requestNotifyError;
    private $requestNotifyCodRetError;
    private $requestRedirect;
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
        
        $this->requestNotifyError = [
            'TX' => [
                'IDTRX' => '1',
                'CODRET' => '0000',
                'TOTAL' => 1199,
                'MONEDA' =>'USD',
                'IDTRXREC' =>'1',
                'DESCRET' => 'Transaccion OK'
            ]
        ];

        $this->requestNotifyCodRetError = [
            'TX' => [
                'IDTRX' => '1',
                'CODRET' => '0077',
                'TOTAL' => 1199,
                'MONEDA' =>'CLP',
                'IDTRXREC' =>'1',
                'DESCRET' => 'Transaccion 77'
            ]
        ];

        $this->requestRedirect = [
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
    
    public function testNotifySuccess()
    {
   
        Queue::fake();
        
        $requestMock = Mockery::mock(NotifyRequest::class);
        $requestMock->shouldReceive('validated')->andReturn($this->requestNotify);
        $requestMock->shouldReceive('url')->andReturn('https://example.com/notify');
        $this->app->instance(NotifyRequest::class, $requestMock);

        $response = Response::json(['code' => '0000', 'dsc' => 'Transaccion OK'], 200);

        $order = Cart::factory()->create();

        $controller = new WebhookController();
        $result = $controller->notify($requestMock);
      
        $this->assertEquals($response->getContent(), $result->getContent());

        Queue::assertPushed(KafkaNotification::class, function ($job) {
            return true;
        });
    }
    public function testCarroYaNotificado()
    {
        $requestMock = Mockery::mock(NotifyRequest::class);
        $requestMock->shouldReceive('validated')->andReturn($this->requestNotify);
        $requestMock->shouldReceive('url')->andReturn('https://example.com/notify');
        $this->app->instance(NotifyRequest::class, $requestMock);
        
        $cart = Cart::factory()->create(['car_sent_kafka' => 1]);
        $response = Response::json(['error' => 500, 'message' => 'Carro ya fue notificado']);

        $controller = new WebhookController();
    
        $result = $controller->notify($requestMock);

        $this->assertEquals($response->getContent(), $result->getContent());
    }
    public function testMontoInconsistente()
    {
        $requestMock = Mockery::mock(NotifyRequest::class);
        $requestMock->shouldReceive('validated')->andReturn($this->requestNotify);
        $requestMock->shouldReceive('url')->andReturn('https://example.com/notify');
        $this->app->instance(NotifyRequest::class, $requestMock);
        
        $cart = Cart::factory()->create(['car_flow_amount' => 7777]);
        $response = Response::json(['error' => 500, 'message' => 'Monto total pagado inconsistente']);

        $controller = new WebhookController();
    
        $result = $controller->notify($requestMock);

        $this->assertEquals($response->getContent(), $result->getContent());
    }
    public function testMonedaInconsistente()
    {
        $requestMock = Mockery::mock(NotifyRequest::class);
        $requestMock->shouldReceive('validated')->andReturn($this->requestNotifyError);
        $requestMock->shouldReceive('url')->andReturn('https://example.com/notify');
        $this->app->instance(NotifyRequest::class, $requestMock);
      
        $cart = Cart::factory()->create();
        
        $response = Response::json(['error' => 500, 'message' => 'Moneda total pagado inconsistente']);

        $controller = new WebhookController();
        
        $result = $controller->notify($requestMock);

        $this->assertEquals($response->getContent(), $result->getContent());
    }
    public function testCodigoRetornoError()
    {
        $requestMock = Mockery::mock(NotifyRequest::class);
        $requestMock->shouldReceive('validated')->andReturn($this->requestNotifyCodRetError);
        $requestMock->shouldReceive('url')->andReturn('https://example.com/notify');
        $this->app->instance(NotifyRequest::class, $requestMock);
       
        $cart = Cart::factory()->create();
        
        $response = Response::json(['error' => 500, 'message' => 'Codigo de retorno: '.$this->requestNotifyCodRetError['TX']['CODRET']." ".$this->requestNotifyCodRetError['TX']['DESCRET']]);

        $controller = new WebhookController();
        
        $result = $controller->notify($requestMock);

        $this->assertEquals($response->getContent(), $result->getContent());
    }
    public function testCarroInexistente()
    {
        $this->requestNotify['TX']['IDTRX']=5;
        $requestMock = Mockery::mock(NotifyRequest::class);
        $requestMock->shouldReceive('validated')->andReturn($this->requestNotify);
        $requestMock->shouldReceive('url')->andReturn('https://example.com/notify');
        $this->app->instance(NotifyRequest::class, $requestMock);
       
        $cart = Cart::factory()->create();
        
        $response = Response::json(['error' => 500, 'message' => 'Id de carro inexistente']);

        $controller = new WebhookController();
        
        $result = $controller->notify($requestMock);

        $this->assertEquals($response->getContent(), $result->getContent());
    }
    public function testRedirectSuccess()
    {
        Queue::fake();
        $order = Cart::factory()->create();
        $requestMock = Mockery::mock(RedirectRequest::class);
        $requestMock->shouldReceive('validated')->andReturn($this->requestRedirect);
        $requestMock->shouldReceive('url')->andReturn('https://example.com/notify');
        $this->app->instance(RedirectRequest::class, $requestMock);

        $response = Response::json(['message' => 'Recepcion exitosa', 'url_return' => 'https://tebi4tbxq0.execute-api.us-west-2.amazonaws.com/QA/santander/v1/redirect'], 200);

        
        $controller = new WebhookController();
        $result = $controller->redirect($requestMock);
        
        $this->assertEquals($response->getContent(), $result->getContent());

    }
    
    public function testMontoInconsistenteRedirect()
    {
        $requestMock = Mockery::mock(RedirectRequest::class);
        $requestMock->shouldReceive('validated')->andReturn($this->requestRedirect);
        $requestMock->shouldReceive('url')->andReturn('https://example.com/notify');
        $this->app->instance(NotifyRequest::class, $requestMock);
        
        $cart = Cart::factory()->create(['car_flow_amount' => 7777]);
        $response = Response::json(['error' => 500, 'message' => 'Monto total pagado inconsistente']);

        $controller = new WebhookController();
    
        $result = $controller->redirect($requestMock);

        $this->assertEquals($response->getContent(), $result->getContent());
    }
    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
        
    }
}
