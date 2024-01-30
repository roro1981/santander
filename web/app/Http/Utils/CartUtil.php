<?php

namespace App\Http\Utils;
use App\Models\Cart;
use App\Models\CartStatus;
use App\Http\Clients\SantanderClient;

class CartUtil
{
    public static function saveOrder($uuid, $orderRequest, $userRequest)
    {
        $order = Cart::create([
            'car_id_transaction' => $uuid,
            'car_flow_currency' => $orderRequest['currency'],
            'car_flow_amount' => $orderRequest['amount'],
            'car_url' => $orderRequest['url_confirmation'],
            'car_expires_at' => self::validateExpirationTime($orderRequest['expiration']),
            'car_items_number' => 1,
            'car_status' => Constants::STATUS_CREATED,
            'car_url_return' => ParamUtil::getParam(Constants::PARAM_URL_RETORNO),
            'car_sent_kafka' => 0,
            'car_flow_id' => $orderRequest['id'],
            'car_flow_attempt_number' => $orderRequest['attempt_number'],
            'car_flow_product_id' => $orderRequest['product_id'],
            'car_flow_email_paid' => $userRequest['email'],
            'car_flow_subject' => $orderRequest['subject'],
            'car_created_at' => now()
        ]);

        CartStatus::saveCurrentStatus($order);
        $cartInscription = new SantanderClient();
        $response = $cartInscription->enrollCart($order->toArray());
        
        if($response['codeError']=="0"){
            $order->update(['car_url' => $response['urlBanco'],'car_status' =>'PRE-AUTHORIZED']);
            CartStatus::saveCurrentStatus($order);
        }    
        return $order;
    }

    public static function validateExpirationTime(int $expiration)
    {
        $defaultExpirationTime = Constants::PARAM_EXPIRATION_TIME;
        if ($expiration > time() + $defaultExpirationTime && $expiration < time() + Constants::MAX_ORDER_EXPIRATION)
        {
            return $expiration;
        }
        else
        {
            return time() + $defaultExpirationTime;
        }
    }
}
