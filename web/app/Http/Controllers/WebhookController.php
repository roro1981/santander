<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Http\Requests\NotifyRequest;
use App\Http\Requests\RedirectRequest;
use App\Jobs\KafkaNotification;
use App\Models\ApiLog;
use App\Models\Cart;
use App\Models\CartStatus;
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
              
                if($cart->car_sent_kafka == 1){
                    throw new \Exception("Carro ya fue notificado", true);
                }
                $apiLog = ApiLog::storeLog(
                    $cart->car_id,
                    $urlActual,
                    $validated
                );
                $montoFormateado = (int) number_format($cart->car_flow_amount, 0, '.', '');
                if((int)$validated['TOTAL'] != $montoFormateado){
                    throw new \Exception("Monto total pagado inconsistente", true);
                }

                $notKafka=KafkaNotification::dispatch($cart)->onQueue('kafkaNotification');

                if($notKafka){
                    $cart->update(['car_status' => 'AUTHORIZED','car_sent_kafka' => 1 ,'car_authorization_uuid' =>$validated['IDREG']]);
                    CartStatus::saveCurrentStatus($cart);
                }

                $response = response()->json([
                    'code' => $validated['CODRET'],
                    'dsc' => $validated['DESCRET']
                ]);
                $apiLog->updateLog($response, 200);
            }elseif($codRet != "0000"){
                throw new \Exception("Codigo de retorno: ".$codRet." ".$validated['DESCRET'], true);    
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
                $apiLog = ApiLog::storeLog(
                    $cart->car_flow_id,
                    $urlActual,
                    $validated
                );
                $montoFormateado = (int) number_format($cart->car_flow_amount, 0, '.', '');

                if((int)$mpfin['TOTAL'] != $montoFormateado){
                    throw new \Exception("Monto total pagado inconsistente", true);
                }

                $cart->update(['car_authorization_uuid' => $mpfin['IDTRX']]);
                
                /*$response = response()->json([
                    'message' => 'Recepcion exitosa',
                    'url_return' => $cart->car_url_return
                ]); */
                $message = htmlspecialchars('Recepción exitosa');
                $urlReturn = htmlspecialchars($cart->car_url_return);

                $htmlResponse = "<html><head><title>Recepción Exitosa</title></head><body>";
                $htmlResponse .= "<h1>{$message}</h1>";
                $htmlResponse .= "<p><a href='{$urlReturn}'>Volver</a></p>";
                $htmlResponse .= "</body></html>";

                $response= response($htmlResponse)->header('Content-Type', 'text/html');
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
