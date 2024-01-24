<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use App\Http\Utils\CartUtil;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Responses\CreateOrderResponse;
use App\Jobs\KafkaNotification;


class OrderController extends Controller
{
    public function create(CreateOrderRequest $request)
    {
        $validated = $request->validated();
        $uuid = $validated['uuid'];
        $orderRequest = $validated['order'];
        $user = $validated['user'];

        $responseIdp = $this->idempotencyResponse($uuid);
        if ($responseIdp) {
            return $responseIdp;
        }
        
        try { 
            $cart = CartUtil::saveOrder($uuid, $orderRequest, $user);
            $response=CreateOrderResponse::generate($cart);
            KafkaNotification::dispatch($cart,'first_topic')->onQueue('kafkaNotification');
        } catch (\Exception $e) {
            Log::error("Error al crear orden " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

    $this->saveIdempotency($uuid, 'response', 'htt');
        return $response;
    }

}
