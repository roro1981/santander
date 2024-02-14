<?php

namespace App\Http\Requests;


class NotifyRequest extends CustomFormRequest
{

    public function rules(): array
    {
        
        return [
            'TX' => 'required|array',
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
    public function prepareForValidation($data=null)
    {   
        if (isset($data)) {
            $rawBody = $data;
        }else{    
            $rawBody = file_get_contents("php://input");
        }
        
        $body=str_replace("TX=","",$rawBody);
        
        $bodyArray = $this->convertXmlToArray($body);
        
        $this->merge(['TX' => $bodyArray]);
        $request = $this->request->all();
        
        if ($request != []) {
            $txData = $request['TX'];          
            $idTrx = (int)ltrim($txData['IDTRX'], '0');
            $bodyArray['IDTRX'] = $idTrx;
            $this->merge(['TX' => $bodyArray]);
        }    
    }
    public function convertXmlToArray($xml)
    {
        if (empty($xml) || !is_string($xml)) {
            return "La cadena XML está vacía o no es válida.";
        }

        $decodedXml = html_entity_decode($xml, ENT_QUOTES, 'UTF-8');

        libxml_use_internal_errors(true);
        $xmlObject = simplexml_load_string($decodedXml, "SimpleXMLElement", LIBXML_NOCDATA);
        libxml_use_internal_errors(false); 

        if ($xmlObject === false) {
            return null;
        }

        $json = json_encode($xmlObject);
        $array = json_decode($json, true);

        return $array;
    }
}