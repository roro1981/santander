<?php
namespace App\Exceptions;

use Exception;

class CodigoRetornoException extends Exception
{
    public function render($request)
    {
        return response()->json(['error' => $this->getMessage()], $this->getCode());
    }
}
