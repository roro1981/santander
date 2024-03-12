<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\CreateOrderRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOrderRequestTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function testCreateOrderValidation()
    {
        $request = new CreateOrderRequest([
            'uuid' => '80b9cbcb-6080-41ba-9492-8b1d886d5904',
            'order' => [
                'id' => 1,
                'product_id' => 1,
                'method_id' => 160,
                'url_confirmation' => 'https://flow.cl/confirmacion.php',
                'url_return' => 'https://flow.cl/retorno.php',
                'attempt_number' => 1,
                'amount' => 3.00,
                'currency' => '999',
                'subject' => 'Unit Test',
                'email_paid' => 'test@flow.cl',
                'expiration' => time() + 86400,
            ],
            'user' => [
                'id' => 1,
                'email' => 'test@flow.cl',
                'legal_name' => 'Testing',
                'tax_id' => '11111111-1',
                'address' => 'Santiago',
                'fantasy_name' => 'Testing',
            ],
        ]);

        $validator = $this->app['validator']->make($request->all(), $request->rules());
        if ($validator->fails()) {
            dd($validator->messages());
        }
        $this->assertFalse($validator->fails());
        
    }

    public function testCreateOrderValidationRequired()
    {
        $request = new CreateOrderRequest();

        $validator = $this->app['validator']->make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertEquals('The uuid field is required.', $validator->errors()->get('uuid')[0]);
        $this->assertEquals('The order.id field is required.', $validator->errors()->get('order.id')[0]);
        $this->assertEquals('The user.id field is required.', $validator->errors()->get('user.id')[0]);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
