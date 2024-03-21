<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Http\Requests\NotifyRequest;
use App\Http\Requests\RedirectRequest;
use App\Jobs\KafkaNotification;
use App\Models\ApiLog;
use App\Models\Cart;
use App\Models\CartStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function notify(NotifyRequest $request)
    {

        $validated = $request->validated();
 
        try { 
            $cartId = (int)ltrim($validated['IDTRX'], '0');
            $codRet = $validated['CODRET'];
            $cart = Cart::find($cartId);
    
            $urlActual = $request->url();

            if($cart && $codRet == "0000"){
                
                if($cart->car_status != "REGISTERED-CART" && $cart->car_sent_kafka == 0){
                    return response()->json([
                        'code' => 409,
                        'dsc' => "Carro se encuentra con status ".$cart->car_status." debiendo estar en status REGISTERED-CART"
                    ],409);
                }

                if($cart->car_sent_kafka == 1){
                    return response()->json([
                        'code' => 422,
                        'dsc' => "Carro ya fue notificado"
                    ],422);
                }
                $apiLog = ApiLog::storeLog(
                    $cart->car_id,
                    $urlActual,
                    $validated
                );
                $montoFormateado = (int) number_format($cart->car_flow_amount, 0, '.', '');
                if((int)$validated['TOTAL'] != $montoFormateado){
                    return response()->json([
                        'code' => 401,
                        'dsc' => "Monto total pagado inconsistente"
                    ],401);
                }

                $notKafka=KafkaNotification::dispatch($cart)->onQueue('kafkaNotification');

                if($notKafka){
                    $fechaNotify = Carbon::createFromFormat('YmdHis', $validated['FECHATRX']);
                    $fechaFormateada = $fechaNotify->format('Y-m-d H:i:s');
                    $cart->update(['car_status' => 'AUTHORIZED','car_sent_kafka' => 1 ,'car_authorization_uuid' =>$validated['IDREG'],'car_transaction_date' =>$fechaFormateada]);
                    CartStatus::saveCurrentStatus($cart);
                }

                $response = response()->json([
                    'code' => $validated['CODRET'],
                    'dsc' => $validated['DESCRET']
                ],200);
                $apiLog->updateLog($response, 200);
            }elseif($codRet != "0000"){
                $response = response()->json([
                    'code' => 401,
                    'error' => "Codigo de retorno: ".$codRet." ".$validated['DESCRET']
                ], 401);
            }else{  
                $response = response()->json([
                    'code' => 404,
                    'error' => "Id de carro inexistente"
                ], 404);
            }
        } catch (\Exception $e) {
            $apiLog = ApiLog::storeLog(
                $cartId,
                $urlActual,
                $validated
            );
            Log::error("Error recepcion de MPOUT" . $e->getMessage());
            $response = response()->json([
                'error' => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
            $apiLog->updateLog($response, $e->getCode());
        } 
        
        return $response;
    }

    public function redirect(RedirectRequest $request)
    {
        $validated = $request->validated();
        
        try { 
            $idCarro =  $validated['IdCarro'];
            $mpfin   =  $validated['mpfin'];

            $cart = Cart::find($idCarro);
          
            $urlActual = $request->url();

            if($cart){
                $apiLog = ApiLog::storeLog(
                    $cart->car_id,
                    $urlActual,
                    $validated
                );
                $montoFormateado = (int) number_format($cart->car_flow_amount, 0, '.', '');

                if((int)$mpfin['TOTAL'] != $montoFormateado){
                    throw new \Exception("Monto total pagado inconsistente", 401);
                }

                $cart->update(['car_authorization_uuid' => $mpfin['IDTRX']]);
                
                $response = response()->json([
                    'message' => 'Recepcion exitosa',
                    'url_return' => $cart->car_url_return
                ]); 
            
                $apiLog->updateLog($response, 200);
            }else{
                $apiLog = ApiLog::storeLog(
                    $idCarro,
                    $urlActual,
                    $validated
                );
                throw new \Exception("Id de carro inexistente", 404);
            }
        } catch (\Exception $e) {
            Log::error("Error recepcion de MPFIN" . $e->getMessage());
            $response = response()->json([
                'error' => $e->getCode(),
                'message' => $e->getMessage()
            ], 500);
            $apiLog->updateLog($response, $e->getCode());
        } 
        
        return $response;
    }
}
