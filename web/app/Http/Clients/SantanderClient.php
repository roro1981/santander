<?php

namespace App\Http\Clients;

use Illuminate\Support\Facades\Http;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;
use Exception;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Log;


class SantanderClient
{

    private $baseUrl;
    private $intentosMaximos=3;
    private $intervaloTiempo = 5;
    private $credentials=array();

    public function __construct()
    {
        $this->baseUrl = ParamUtil::getParam(Constants::PARAM_SANTANDER_URL);

    }

    public function getBearerToken(Int $orderId, $intentos=0)
    {
        do {

            try {
                $this->credentials = [
                    'company' =>  ParamUtil::getParam(Constants::PARAM_SANTANDER_TOKEN_COMPANY),
                    'username' => ParamUtil::getParam(Constants::PARAM_SANTANDER_TOKEN_USERNAME),
                    'password' => ParamUtil::getParam(Constants::PARAM_SANTANDER_TOKEN_PASSWORD),
                ];
                if (empty($this->credentials['company']) || empty($this->credentials['username']) || empty($this->credentials['password'])) {
                    echo "Error al obtener credenciales";
                    exit;
                } 

                $response = Http::post($this->baseUrl."/auth/basic/token", $this->credentials);
                
           
            $apiLog = ApiLog::storeLog(
                $orderId,
                $this->baseUrl."/auth/basic/token",
                $this->credentials
            );
            if ($response->successful()) {
                $responseToken = [
                    'token_type' => $response->json('token_type'),
                    'access_token' => $response->json('access_token'),
                ];
                $apiLog->updateLog((array) $responseToken, 200);
                return $responseToken;
            }

            } catch (Exception $e) {
                $intentos++;
                if ( $intentos < $this->intentosMaximos) {
                    sleep($this->intervaloTiempo);
                } else {
                    throw new \Exception('Error al obtener el Bearer Token después de '.$this->intentosMaximos.' intentos', 500);
                }
            }

        } while ($intentos < $this->intentosMaximos);

        $response = response()->json([
            'error' => 500,
            'message' => 'Error al obtener el Bearer Token'
        ], 500);
        $apiLog->updateLog((array) $response, 500);
        return $response;
    }

    public function enrollCart(array $cartData,Int $orderId, $intentos=0)
    {
        $authorizationToken = $this->getBearerToken($orderId, 0);
       
        
        do {
            try {
          
                $url=$this->baseUrl.'/auth/apiboton/carro/inscribir';
                if (empty($url)) {
                    echo "Error al obtener url de servicio";
                    exit;
                } 
                $headers = [
                    'Content-Type' => 'application/json',
                    'Authorization' => $authorizationToken['token_type'] . ' ' . $authorizationToken['access_token'],
                ];
                
                $body=['idTransaction' => $cartData['car_id'],
                'currency' => $cartData['car_flow_currency'],
                'amount' => $cartData['car_flow_amount'],
                'agreement' => '9570',
                'url' => $cartData['car_url_return'],
                'itemsNumber' => 1,
                'additionalData' => [],
                'details' => [
                    [
                        'description' => $cartData['car_flow_subject'],
                        'amount' => $cartData['car_flow_amount'],
                        'number' => 1,
                    ],
                ],
                'collector' => '7683001403'];
                
                
                $response = Http::withHeaders($headers)->post($url,$body);
                
                $apiLog = ApiLog::storeLog(
                    $orderId,
                    $url,
                    $body
                );
                
                if($response->successful()){
                    $apiLog->updateLog((array) $response, 200);
                    return $response;
                }
            } catch (Exception $e) {
                $intentos++;
                if ($intentos < $this->intentosMaximos) {
                    sleep($this->intervaloTiempo);
                } else {
                    throw new \Exception('Error al inscribir el carro después de '.$this->intentosMaximos.' intentos', 500);
                }
            }
        } while ($intentos < $this->intentosMaximos);
        $response = response()->json([
            'error' => 500,
            'message' => 'Error al inscribir el carro'
        ], 500);
        $apiLog->updateLog((array) $response, 500);
        return $response;
    }

}
