<?php

namespace App\Http\Utils;
use GuzzleHttp\Client;


use App\Models\Cart;
use App\Models\Cart_status;

class CartUtil
{
    public static function saveOrder($uuid, $orderRequest, $userRequest)
    {
 
        $order = Cart::create([
            'car_id_transaction' => $uuid,
            'car_flow_currency' => $orderRequest['currency'],
            'car_flow_amount' => $orderRequest['amount'],
            'car_url' => $orderRequest['url_confirmation'],
            'car_items_number' => 1,
            'car_status' => Constants::STATUS_CREATED,
            'car_url_return' => $orderRequest['url_return'],
            'car_sent_kafka' => 0,
            'car_flow_id' => $orderRequest['id'],
            'car_flow_attempt_number' => $orderRequest['attempt_number'],
            'car_flow_product_id' => $orderRequest['product_id'],
            'car_flow_email_paid' => $userRequest['email'],
            'car_flow_subject' => $orderRequest['subject'],
            'car_created_at' => now()
        ]);
     
        Cart_status::saveCurrentStatus($order);
        return $order;
    }

    public static function getNotifyUrl($orderUuid)
    {
        $baseUrl = rtrim(ParamUtil::getParam(Constants::PARAM_WEBHOOK_URL), "/");
        return $baseUrl . "/v1/order/$orderUuid/notify";
    }

    public static function getCancelUrl($orderUuid)
    {
        $baseUrl = rtrim(ParamUtil::getParam(Constants::PARAM_WEBHOOK_URL), "/");
        return $baseUrl . "/v1/order/$orderUuid/cancel";
    }

}
