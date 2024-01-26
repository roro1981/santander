<?php

namespace App\Http\Requests;


class NotifyRequest extends CustomFormRequest
{
    public function rules(): array
    {
        
        return [
            'TX.CODRET' => 'required|numeric',
            'TX.DESCRET' => 'required|string',
            'TX.IDCOM' => 'required|string',
            'TX.IDTRX' => 'required|numeric',
            'TX.TOTAL' => 'required|numeric',
            'TX.MONEDA' => 'required|string',
            'TX.NROPAGOS' => 'required|numeric',
            'TX.FECHATRX' => 'required|date_format:d/m/Y H:i:s',
            'TX.IDTRXREC' => 'required|string',
        ];
    }

    protected function prepareForValidation()
    {
        $rawBody = file_get_contents("php://input");
        $body=str_replace("TX=","",$rawBody);
        $bodyArray = $this->convertXmlToArray($body);
        
        $this->merge(['TX' => $bodyArray]);
        $request = $this->request->all();
        $txData = $request['TX'];
        $idTrx = (int)ltrim($txData['IDTRX'], '0');
        $bodyArray['IDTRX'] = $idTrx;
        $this->merge(['TX' => $bodyArray]);
    }
    private function convertXmlToArray($xml)
    {
        $decodedXml = html_entity_decode($xml, ENT_QUOTES, 'UTF-8');

        libxml_use_internal_errors(true);
        $xmlObject = simplexml_load_string($decodedXml, "SimpleXMLElement", LIBXML_NOCDATA);
        libxml_use_internal_errors(false); 

        if ($xmlObject === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new \Exception("Error al cargar la cadena XML. Detalles: " . print_r($errors, true));
        }

        $json = json_encode($xmlObject);
        $array = json_decode($json, true);

        return $array;
    }
}