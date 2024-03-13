<?php

namespace Tests\Unit\Models;
namespace App\Models;;

use App\Models\Cart;
use App\Models\CartStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;


class CartStatusModelTest extends TestCase
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
    public function testCartStatusModel()
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

        $cartStatus = CartStatus::saveCurrentStatus($cart);

        $this->assertInstanceOf(CartStatus::class, $cartStatus);

        $this->assertEquals($cart->car_id, $cartStatus->car_id);
        $this->assertEquals($cart->car_status, $cartStatus->cas_status);

        $this->assertEquals($cart->car_id, $cartStatus->car_id);
        $this->assertEquals('CREATED', $cartStatus->cas_status);
        
        $this->assertContains('car_id', $cartStatus->getFillable());
        $this->assertContains('cas_status', $cartStatus->getFillable());
   
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCartRelationship()
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

        $cartStatus = CartStatus::create([
            'car_id' => $cart->car_id,
            'cas_status' => 'CREATED',
            ]);

        $cartRelationship = $cartStatus->cart();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $cartRelationship);
    }
    public function tearDown(): void
    {
        parent::tearDown();

    }
}
