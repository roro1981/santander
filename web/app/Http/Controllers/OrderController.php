<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\NotifyRequest;
use App\Http\Requests\MpfinRequest;
use App\Jobs\KafkaNotification;
use App\Models\ApiLog;
use App\Models\Cart;
use Ramsey\Uuid\Uuid;
use App\Models\CartStatus;
use App\Http\Clients\SantanderClient;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function create(CreateOrderRequest $request)
    {
        $validated = $request->validated();
        $uuid = $validated['uuid'];
        $orderRequest = $validated['order'];
        $user = $validated['user'];
        $apiLog = new ApiLog();

        $responseIdp = $this->idempotencyResponse($uuid);
        if ($responseIdp) {
            return $responseIdp;
        }
        
        try { 
            $urlActual = $request->url();

            $apiLog = ApiLog::storeLog(
                $orderRequest['id'],
                $urlActual,
                $orderRequest
            );

            $cart = $this->saveOrder($uuid, $orderRequest, $user);

            $response=response()->json(['uuid' => Uuid::uuid4(),
            'payment_uuid' => $cart->car_id_transaction,
            'provider_id' => $cart->car_id, 
            'type' => 'REDIRECT',
            'amount' => $cart->car_flow_amount,
            'currency' => $cart->car_flow_currency,
            'url' => $cart->car_url,
            'expire_time' => $cart->car_expires_at,
            'expire_date' => date('c', $cart->car_expires_at),
            'fields' => []]);

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
                $notKafka=KafkaNotification::dispatch($cart, $id_trx_rec)->onQueue('kafkaNotification');

                if($notKafka){
                    $cart->update(['car_status' => 'AUTHORIZED','car_sent_kafka' => 1 ,'car_authorization_uuid' =>$txData['IDTRXREC']]);
                }
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
            $mpfin   =  $validated['mpfin'];

            $cart = Cart::find($idCarro);
            
            if($cart->car_sent_kafka == 1){
                throw new \Exception("Carro ya fue notificado", true);
            }

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
    private function saveOrder($uuid, $orderRequest, $userRequest){
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
