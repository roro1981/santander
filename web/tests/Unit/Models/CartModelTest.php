<?php

namespace Tests\Unit\Models;
namespace App\Models;


use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;

class CartModelTest extends TestCase
{
    use RefreshDatabase;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCartModel()
    {
        $cart = Cart::create([
        'car_id_transaction' => '00100',
        'car_flow_currency' => 'CLP',
        'car_flow_amount' => '100.00',
        'car_agreement' => '9570',
        'car_url' => 'https://ejemplo.com/pago',
        'car_expires_at' => 1706292011,
        'car_items_number' => 3,
        'car_collector' => '958488',
        'car_status' => 'CREATED',
        'car_url_return' => 'https://ejemplo.com/retorno',
        'car_authorization_uuid' => 'a9bc8b5a-c019-4acf-9b54-c08260d04f6a',
        'car_sent_kafka' => 0,
        'car_fail_code' => 'FALLO',
        'car_fail_motive' => 'Motivo del fallo',
        'car_flow_id' => '000100',
        'car_flow_attempt_number' => 1,
        'car_flow_method_id' => 160,
        'car_flow_product_id' => '00200',
        'car_flow_subject' => 'Subject test',
        'car_flow_email_paid' => 'rpanes@ejemplo.com'
        ]);
       
        $this->assertInstanceOf(Cart::class, $cart);

        $this->assertEquals('00100', $cart->car_id_transaction);
        $this->assertEquals('CLP', $cart->car_flow_currency);
        $this->assertEquals('100.00', $cart->car_flow_amount);
        $this->assertEquals('9570', $cart->car_agreement);
        $this->assertEquals('https://ejemplo.com/pago', $cart->car_url);
        $this->assertEquals(1706292011, $cart->car_expires_at);
        $this->assertEquals(3, $cart->car_items_number);
        $this->assertEquals('958488', $cart->car_collector);
        $this->assertEquals('CREATED', $cart->car_status);
        $this->assertEquals('https://ejemplo.com/retorno', $cart->car_url_return);
        $this->assertEquals('a9bc8b5a-c019-4acf-9b54-c08260d04f6a', $cart->car_authorization_uuid);
        $this->assertEquals(0, $cart->car_sent_kafka);
        $this->assertEquals('FALLO', $cart->car_fail_code);
        $this->assertEquals('Motivo del fallo', $cart->car_fail_motive);
        $this->assertEquals('000100', $cart->car_flow_id);
        $this->assertEquals(1, $cart->car_flow_attempt_number);
        $this->assertEquals('00200', $cart->car_flow_product_id);
        $this->assertEquals('Subject test', $cart->car_flow_subject);
        $this->assertEquals('rpanes@ejemplo.com', $cart->car_flow_email_paid);

        $this->assertContains('car_uuid', $cart->getFillable());
        $this->assertContains('car_id_transaction', $cart->getFillable());
        $this->assertContains('car_flow_currency', $cart->getFillable());
        $this->assertContains('car_flow_amount', $cart->getFillable());
        $this->assertContains('car_description', $cart->getFillable());
        $this->assertContains('car_agreement', $cart->getFillable());
        $this->assertContains('car_url', $cart->getFillable());
        $this->assertContains('car_expires_at', $cart->getFillable());
        $this->assertContains('car_items_number', $cart->getFillable());
        $this->assertContains('car_collector', $cart->getFillable());
        $this->assertContains('car_status', $cart->getFillable());
        $this->assertContains('car_url_return', $cart->getFillable());
        $this->assertContains('car_authorization_uuid', $cart->getFillable());
        $this->assertContains('car_sent_kafka', $cart->getFillable());
        $this->assertContains('car_fail_code', $cart->getFillable());
        $this->assertContains('car_fail_motive', $cart->getFillable());
        $this->assertContains('car_flow_id', $cart->getFillable());
        $this->assertContains('car_flow_attempt_number', $cart->getFillable());
        $this->assertContains('car_flow_product_id', $cart->getFillable());
        $this->assertContains('car_flow_subject', $cart->getFillable());
        $this->assertContains('car_flow_email_paid', $cart->getFillable());
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCartStatusMethod()
    {
        $cart = new Cart();
        $cart->car_id_transaction = Uuid::uuid4();
        $cart->car_flow_currency = 'CLP';
        $cart->car_flow_amount = 1199;
        $cart->car_url = 'https://flow.cl/retorno.php';
        $cart->car_expires_at = '1699569123';
        $cart->car_items_number = 1;
        $cart->car_status = 'CREATED';
        $cart->car_url_return = 'https://tebi4tbxq0.execute-api.us-west-2.amazonaws.com/QA/santander/v1/redirect';
        $cart->car_sent_kafka = 0;
        $cart->car_flow_id = 1;
        $cart->car_flow_attempt_number = 1;
        $cart->car_flow_method_id = 160;
        $cart->car_flow_product_id = 1;
        $cart->car_flow_email_paid = 'rpanes@tuxpan.com';
        $cart->car_flow_subject = 'Test integracion';
        $cart->car_created_at = now();
        
        $cart->save();

        $cartStatus1 = new CartStatus();
        $cartStatus1->car_id=1;
        $cartStatus1->cas_status = 'CREATED';
         
        $cart->cartStatus()->save($cartStatus1);
        $resultados = $cart->cartStatus;

         $this->assertEquals(1, $resultados->count());
         $this->assertEquals('CREATED', $resultados[0]->cas_status);
    }
    public function tearDown(): void
    {
        parent::tearDown();
    }
    
}
