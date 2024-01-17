<?php

namespace App\Http\Clients;

use Illuminate\Support\Facades\Http;
use App\Http\Utils\Constants;
use Exception;

class SantanderClient
{
    private $baseUrl;
    private $bearerToken;

    public function __construct()
    {
        // Configurar la URL base de la API de Santander
        $this->baseUrl = Constants::PARAM_SANTANDER_TOKEN_URL;

        // Obtener el Bearer Token al inicializar la clase
        $this->bearerToken = $this->getBearerToken();
    }

    /**
     * Obtener el Bearer Token de la API de Santander
     *
     * @return string|null
     */
    private function getBearerToken()
    {
        try {
            // Parámetros para la solicitud de token
            $headers = ['Content-Type' => 'application/json'];
            $body = '{"company": "768300143", "username": "768300143", "password": "Ax4o5idb_h"}';
            $response = Http::withHeaders($headers)->get('https://paymentbutton-bsan-cert.e-pagos.cl/auth/basic/token', json_decode($body, true));
            dd($headers, $body, json_decode($body, true), $response);
            // $credentials = [
            //     'company' =>  Constants::PARAM_SANTANDER_TOKEN_COMPANY,
            //     'username' => Constants::PARAM_SANTANDER_TOKEN_USERNAME,
            //     'password' => Constants::PARAM_SANTANDER_TOKEN_PASSWORD,
            // ];
            // // Realizar la solicitud para obtener el token
            // $response = Http::get($this->baseUrl."/auth/basic/token", $credentials);
            // dd($response,$credentials,$this->baseUrl."/auth/basic/token");
            // // Verificar si la solicitud fue exitosa
            // if ($response->successful()) {
            //     return $response->json('access_token');
            // }

            // Manejar el caso en que la solicitud no fue exitosa
            throw new Exception('Error al obtener el Bearer Token: ' . $response->status());
        } catch (Exception $e) {
            dd($e);
            throw new Exception('Error al obtener el Bearer Token: ' . $e->getMessage());
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
        try {
            // Realizar la solicitud para inscribir el carro
            $response = Http::withToken($this->bearerToken)
                ->post("{$this->baseUrl}/enroll-cart", $cartData);

            // Verificar si la solicitud fue exitosa
            if ($response->successful()) {
                return $response->json();
            }

            // Manejar el caso en que la solicitud no fue exitosa
            throw new Exception('Error al inscribir el carro: ' . $response->status());
        } catch (Exception $e) {
            // Manejar errores
            throw new Exception('Error al inscribir el carro: ' . $e->getMessage());
        }
    }

    private static function createSantanderClient($bankId)
    {
        return new SantanderClient($bankId);
    }
}
