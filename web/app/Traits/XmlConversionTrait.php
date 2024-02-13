<?php

namespace App\Traits;

trait XmlConversionTrait
{
    public function convertXmlToArray(String $xml)
    {
        $decodedXml = html_entity_decode($xml, ENT_QUOTES, 'UTF-8');

        libxml_use_internal_errors(true);
        $xmlObject = simplexml_load_string($decodedXml, "SimpleXMLElement", LIBXML_NOCDATA);
        libxml_use_internal_errors(false);

        if ($xmlObject === false) {
            /*$errors = libxml_get_errors();
            libxml_clear_errors();
            throw new \Exception("Error al cargar la cadena XML. Detalles: " . print_r($errors, true));*/
            return null;
        }

        $json = json_encode($xmlObject);
        $array = json_decode($json, true);

        return $array;
    }
}