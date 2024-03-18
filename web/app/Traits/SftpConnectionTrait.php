<?php

namespace App\Traits;

use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;

trait SftpConnectionTrait
{

    public function testConnection()
    {
        $ftpHost = ParamUtil::getParam(Constants::PARAM_SANTANDER_SFTP_HOST);
        $ftpUsername = ParamUtil::getParam(Constants::PARAM_SANTANDER_SFTP_USERNAME);
        $ftpPassword = ParamUtil::getParam(Constants::PARAM_SANTANDER_SFTP_PASSWORD);
        
        try {
            $sftp = new \phpseclib3\Net\SFTP($ftpHost);
            
            if (!$sftp->login($ftpUsername, $ftpPassword)) {
                throw new \Exception('Error de conexión');
            }else{
                return $sftp;
            }
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}