<?php

namespace App\Http\Controllers;

use App\Http\Utils\CartUtil;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\NotifyRequest;
use App\Http\Requests\MpfinRequest;
use App\Http\Responses\CreateOrderResponse;
use App\Jobs\KafkaNotification;
use App\Models\Api_log;
use App\Models\Cart;

use Illuminate\Http\Request;

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
            $urlActual = $request->url();

            $apiLog = Api_log::storeLog(
                $orderRequest['id'],
                $urlActual,
                $orderRequest
            );

            $cart = CartUtil::saveOrder($uuid, $orderRequest, $user);
            $response=CreateOrderResponse::generate($cart);
            $apiLog->updateLog((array) $response, 200);
            
        } catch (\Exception $e) {
            Log::error("Error al crear orden " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
            $apiLog->updateLog($e, 500);
        }

    $this->saveIdempotency($uuid, 'response', 'htt');
        return $response;
    }

    public function notify(NotifyRequest $request)
    {

        $validated = $request->validated();
       
        try { 
            $txData=$validated['TX'];
            $cartId = $txData['IDTRX'];
            $codRet = $txData['CODRET'];
            $cart = Cart::find($cartId);
            $id_trx_rec = $txData['IDTRXREC'];

            if($cart && $codRet == "0000"){
                $montoFormateado = (int) number_format($cart->car_flow_amount, 0, '.', '');
                if((int)$txData['TOTAL'] != $montoFormateado){
                    throw new \Exception("Monto total pagado inconsistente", true);
                }elseif($txData['MONEDA'] != "CLP"){
                    throw new \Exception("Moneda total pagado inconsistente", true);
                }
                $cart->update(['car_status' => 'AUTHORIZED','car_sent_kafka' => 1 ,'car_authorization_uuid' =>$txData['IDTRXREC']]);
                KafkaNotification::dispatch($cart, $id_trx_rec)->onQueue('kafkaNotification');
                
                $response = response()->json([
                    'code' => $txData['CODRET'],
                    'dsc' => $txData['DESCRET']
                ]);
            }else{
                throw new \Exception("Id de carro inexistente", true);
            }
        } catch (\Exception $e) {
            Log::error("Error recepcion de MPOUT" . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        } 
        
        return $response;
    }
    public function mpfin(MpfinRequest $request)
    {
        $validated = $request->validated();
        
        try { 
            $idCarro =  $validated['IdCarro'];
            $codRet  =  $validated['CodRet'];
            $estado  =  $validated['Estado'];
            $mpfin   =  $validated['mpfin'];

            $cart = Cart::find($idCarro);
            
            if($cart){
                $montoFormateado = (int) number_format($cart->car_flow_amount, 0, '.', '');

                if((int)$mpfin['TOTAL'] != $montoFormateado){
                    throw new \Exception("Monto total pagado inconsistente", true);
                }

                $cart->update(['car_status' => 'AUTHORIZED','car_sent_kafka' => 1 ,'car_authorization_uuid' => $mpfin['IDTRX']]);
                KafkaNotification::dispatch($cart, $mpfin['IDTRX'])->onQueue('kafkaNotification');
                
                $response = response()->json([
                    ['message' => 'Recepcion exitosa']
                ]); 
            }else{
                throw new \Exception("Id de carro inexistente", true);
            }
        } catch (\Exception $e) {
            Log::error("Error recepcion de MPFIN" . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        } 
        
        return $response;
    }
}
