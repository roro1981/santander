<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Responses\CreateOrderResponse;
use App\Models\Cart;
use App\Models\CartStatus;
use App\Http\Clients\SantanderClient;

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

            $cart = $this->saveOrder($uuid, $orderRequest, $user);
            $body = Cart::getBody($cart);
            CartStatus::saveCurrentStatus($cart);

            $cartInscription = new SantanderClient();
            $register_cart = $cartInscription->post('/auth/apiboton/carro/inscribir',$body,$orderRequest['id'],0);
            
            if($register_cart['codeError']=="0"){
                $cart->update(['car_url' => $register_cart['urlBanco'],'car_status' =>'REGISTERED-CART']);
                CartStatus::saveCurrentStatus($cart);
            }    
            
            $response = CreateOrderResponse::generate($cart);

        } catch (\Exception $e) {
            Log::error("Error al crear orden " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

    $this->saveIdempotency($uuid, $response->getData(), $response->status());
        return $response;
    }

    private function saveOrder(String $uuid, Array $orderRequest, Array $userRequest){
        return Cart::storeCart($uuid, $orderRequest, $userRequest);
    }

    
}
