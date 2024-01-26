<?php

namespace App\Http\Responses;

use App\Models\Cart;
use Ramsey\Uuid\Uuid;

class CreateOrderResponse
{
    public static function generate(Cart $cart)
    {
        return response()->json([
            'uuid' => Uuid::uuid4(),
            'payment_uuid' => $cart->car_id_transaction,
            'provider_id' => $cart->car_id, 
            'type' => 'REDIRECT',
            'amount' => $cart->car_flow_amount,
            'currency' => $cart->car_flow_currency,
            'url' => $cart->car_url,
            'expire_time' => $cart->car_expires_at,
            'expire_date' => date('c', $cart->car_expires_at),
            'fields' => []
        ], 200);
    }
}