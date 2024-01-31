<?php

namespace App\Traits;

use App\Traits\XmlConversionTrait;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;

trait SftpConnectionTrait
{
    use XmlConversionTrait;

    public function testConnection()
    {
        $ftpHost = ParamUtil::getParam(Constants::PARAM_SANTANDER_SFTP_HOST);
        $ftpUsername = ParamUtil::getParam(Constants::PARAM_SANTANDER_SFTP_USERNAME);
        $ftpPassword = ParamUtil::getParam(Constants::PARAM_SANTANDER_SFTP_PASSWORD);
        
        try {
            $sftp = new \phpseclib3\Net\SFTP($ftpHost);
        
            if (!$sftp->login($ftpUsername, $ftpPassword)) {
                throw new \RuntimeException('Error de conexión: ' . implode(', ', $sftp->getSFTPErrors()));
            }else{
                return $sftp;
            }
            
        } catch (\SFTPException $e) {
            $response = response()->json([
                'error' => 500,
                'message' => 'Excepción SFTP: ' . $e->getMessage() . PHP_EOL
            ], 500);
            return $response;
        } catch (\Exception $e) {
            $response = response()->json([
                'error' => 500,
                'message' => 'Excepción general: ' . $e->getMessage() . PHP_EOL
            ], 500);
            return $response;
        }
    }
}