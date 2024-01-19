<?php

namespace App\Http\Responses;

use App\Http\Utils\Constants;
use App\Models\Cart;
use Ramsey\Uuid\Uuid;

class CreateOrderResponse
{
    public static function generate(Cart $cart)
    {
        return response()->json([
            'uuid' => Uuid::uuid4(),
            'payment_uuid' => $cart->car_id,
            'provider_id' => Constants::PROVIDER_ID,
            'type' => 'REDIRECT',
            'amount' => $cart->car_flow_amount,
            'currency' => $cart->car_flow_currency,
            'url' => $cart->car_url_return,
            'expire_time' => $cart->car_expires_at,
            'expire_date' => date('c', $cart->car_expires_at),
            'fields' => []
        ], 200);
    }
}