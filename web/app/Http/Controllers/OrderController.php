<?php

namespace App\Http\Controllers;

use App\Http\Utils\CartUtil;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\CreateOrderRequest;

class OrderController extends Controller
{
    public function create(CreateOrderRequest $request)
    {
        $validated = $request->validated();
        $uuid = $validated['uuid'];
        $orderRequest = $validated['order'];

        $responseIdp = $this->idempotencyResponse($uuid);
        if ($responseIdp) {
            return $responseIdp;
        }

        try { 
            $cart = CartUtil::saveOrder($uuid, $orderRequest);
            //$response = CreateOrderResponse::generate($cart);
        } catch (\Exception $e) {
            Log::error("Error al crear orden " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => "Error al crear orden " . $e->getMessage()
            ], 500);
        }

    $this->saveIdempotency($uuid, 'response'/*$response->getData()*/, 'http_code'/*$response->status()*/);
        return $response;
    }

}
