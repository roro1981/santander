<?php

use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\ParameterSeeder;
use App\Http\Responses\CreateOrderResponse;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Ramsey\Uuid\Uuid;


class CreateOrderResponseTest extends TestCase
{
    use RefreshDatabase;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    public function testGenerateResponse()
    {
        $cart = new Cart([
            'car_id_transaction' => Uuid::uuid4(),
            'car_flow_currency' => ParamUtil::getParam(Constants::PARAM_CURRENCY),
            'car_flow_amount' => '100.00',
            'car_url' => 'https://flow.com/return',
            'car_expires_at' => strtotime('+1 hour'),
        ]);

        $response = CreateOrderResponse::generate($cart);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('uuid', $content);
        $this->assertArrayHasKey('payment_uuid', $content);
        $this->assertArrayHasKey('provider_id', $content);
        $this->assertArrayHasKey('type', $content);
        $this->assertArrayHasKey('amount', $content);
        $this->assertArrayHasKey('currency', $content);
        $this->assertArrayHasKey('url', $content);
        $this->assertArrayHasKey('expire_time', $content);
        $this->assertArrayHasKey('expire_date', $content);
        $this->assertArrayHasKey('fields', $content);

        $this->assertEquals(200, $response->getStatusCode());
    }
}