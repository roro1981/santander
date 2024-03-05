<?php

namespace App\Http\Controllers;
use App\Http\Requests\NotifyRequest;
use App\Http\Requests\RedirectRequest;
use App\Models\Cart;
use App\Models\ApiLog;
use App\Jobs\KafkaNotification;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function notify(NotifyRequest $request)
    {

        $validated = $request->validated();
       
        try { 
            $txData=$validated['TX'];
            $cartId = $txData['IDTRX'];
            $codRet = $txData['CODRET'];
            $cart = Cart::find($cartId);
            
            $urlActual = $request->url();
            
            if($cart && $codRet == "0000"){
              
                if($cart->car_sent_kafka == 1){
                    throw new \Exception("Carro ya fue notificado", true);
                }
                $apiLog = ApiLog::storeLog(
                    $cart->car_flow_id,
                    $urlActual,
                    $txData
                );
                $montoFormateado = (int) number_format($cart->car_flow_amount, 0, '.', '');
                if((int)$txData['TOTAL'] != $montoFormateado){
                    throw new \Exception("Monto total pagado inconsistente", true);
                }elseif($txData['MONEDA'] != "CLP"){
                    throw new \Exception("Moneda total pagado inconsistente", true);
                }

                $notKafka=KafkaNotification::dispatch($cart)->onQueue('kafkaNotification');

                if($notKafka){
                    $cart->update(['car_status' => 'AUTHORIZED','car_sent_kafka' => 1 ,'car_authorization_uuid' =>$txData['IDTRXREC']]);
                }

                $response = response()->json([
                    'code' => $txData['CODRET'],
                    'dsc' => $txData['DESCRET']
                ]);
                $apiLog->updateLog($response, 200);
            }elseif($codRet != "0000"){
                throw new \Exception("Codigo de retorno: ".$codRet." ".$txData['DESCRET'], true);
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

    public function redirect(RedirectRequest $request)
    {
        $validated = $request->validated();
        
        try { 
            $idCarro =  $validated['IdCarro'];
            $mpfin   =  $validated['mpfin'];

            $cart = Cart::find($idCarro);
          
            $urlActual = $request->url();

            if($cart){
                if($cart->car_sent_kafka == 1){
                    throw new \Exception("Carro ya fue notificado", true);
                }
                $apiLog = ApiLog::storeLog(
                    $cart->car_flow_id,
                    $urlActual,
                    $validated
                );
                $montoFormateado = (int) number_format($cart->car_flow_amount, 0, '.', '');

                if((int)$mpfin['TOTAL'] != $montoFormateado){
                    throw new \Exception("Monto total pagado inconsistente", true);
                }

                $notKafka=KafkaNotification::dispatch($cart)->onQueue('kafkaNotification');
                if($notKafka){
                    $cart->update(['car_status' => 'AUTHORIZED','car_sent_kafka' => 1 ,'car_authorization_uuid' => $mpfin['IDTRX']]);
                }
                
                $response = response()->json([
                    'message' => 'Recepcion exitosa',
                    'url_return' => $cart->car_url_return
                ]); 
                $apiLog->updateLog($response, 200);
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
