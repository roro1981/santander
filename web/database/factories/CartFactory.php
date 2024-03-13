<?php

namespace Database\Factories;
use Ramsey\Uuid\Uuid;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;

use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'car_id' => 1,
            'car_id_transaction' => Uuid::uuid4(),
            'car_flow_currency' => 'CLP',
            'car_flow_amount' => '1199',
            'car_url' => 'https://flow.cl/retorno.php',
            'car_expires_at' => '1699569123',
            'car_items_number' => 1,
            'car_status' => 'CREATED',
            'car_url_return' => 'https://tebi4tbxq0.execute-api.us-west-2.amazonaws.com/QA/santander/v1/redirect',
            'car_sent_kafka' => 0,
            'car_flow_id' => 1,
            'car_flow_attempt_number' => 1,
            'car_flow_method_id' => 160,
            'car_flow_product_id' => 1,
            'car_flow_email_paid' => 'rpanes@tuxpan.com',
            'car_flow_subject' => 'Test integracion',
            'car_created_at' => now()
        ];
    }
}
