<?php

namespace App\Http\Controllers;

use App\Models\Idempotency;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Get an Idempotency response
     *
     * @param $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    protected function idempotencyResponse($uuid)
    {
        $response = null;
        $idempotency = Idempotency::find($uuid);
        if ($idempotency)
        {
            $response = response(json_decode($idempotency->idp_response, true), $idempotency->idp_httpcode);
        }
        return $response;
    }

    /**
     * Save a new Idempotency
     *
     * @param $idempotencyKey
     * @param $response
     * @param $httpCode
     */
    protected function saveIdempotency($uuid, $response, $httpCode)
    {
        $idempotency = Idempotency::find($uuid);
        if (!$idempotency) {
            Idempotency::create([
                'idp_uuid' => $uuid,
                'idp_response' => $response !== "" ? json_encode($response) : "",
                'idp_httpcode' => $httpCode
            ]);
        }
    }

}
