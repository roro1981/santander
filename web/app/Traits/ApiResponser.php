<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait ApiResponser
{
    protected function successResponse($data, $message = null, $code = 200)
	{
		return response()->json([
			'status'=> __('api.success'),
			'message' => $message,
			'data' => $data,
            'error' => null
		], $code);
	}

	protected function errorResponse($error, $message = null, $code = 500)
	{

		return response()->json([
			'status'=> __('api.error'),
			'message' => $message,
			'data' => null,
            'error' => $error
		], $code);
	}
}