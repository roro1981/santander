<?php

namespace App\Http\Clients;

use Illuminate\Support\Facades\Http;
use App\Http\Utils\Constants;
use App\Traits\ApiResponser;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class SantanderClient
{
    use ApiResponser;

    private $baseUrl;
    private $bearerToken;

    public function __construct()
    {
        $this->baseUrl = Constants::PARAM_SANTANDER_URL;
        
    }

    /**
     * Obtener el Bearer Token de la API de Santander
     *
     * @return string|null
     */
    public function getBearerToken()
    {
        try {
            $credentials = [
                'company' =>  Constants::PARAM_SANTANDER_TOKEN_COMPANY,
                'username' => Constants::PARAM_SANTANDER_TOKEN_USERNAME,
                'password' => Constants::PARAM_SANTANDER_TOKEN_PASSWORD,
            ];
            $response = Http::post($this->baseUrl."/auth/basic/token", $credentials);
            
            if ($response->successful()) {
                return $response->json('token_type')." ".$response->json('access_token');
            }

        } catch (Exception $e) {
            return $this->errorResponse($e,'Error al obtener el Bearer Token: ' . $e->getMessage());
        }
    }

    /**
     * Inscribir un carro en la API de Santander
     *
     * @param array $cartData Datos del carro a inscribir
     * @return array
     */
    public function enrollCart(array $cartData)
    {

        $authorizationToken = $this->getBearerToken();
        
        try {
           
            $client = new Client();

            $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $authorizationToken
            ];
       
            $body = '{
            "idTransaction": '.$cartData['car_id'].',
            "currency": "'.$cartData['car_flow_currency'].'",
            "amount": "'.$cartData['car_flow_amount'].'",
            "agreement": "9570",
            "url": "'.$cartData['car_url_return'].'",
            "itemsNumber": 1,
            "additionalData": [],
            "details": [
                {
                "description": "'.$cartData['car_flow_subject'].'",
                "amount": "'.$cartData['car_flow_amount'].'",
                "number": 1
                }
            ],
            "collector": "7683001403"
            }';
          
            $request = new Request('POST', $this->baseUrl.'/auth/apiboton/carro/inscribir', $headers, $body);
          
            $res = $client->sendAsync($request)->wait();
            $jsonContent = $res->getBody()->getContents();
            $arrayContent = json_decode($jsonContent, true);
            return $arrayContent;
            
        } catch (Exception $e) {
            throw new Exception('Error al inscribir el carro: ' . $e->getMessage());
        }
    }

}
