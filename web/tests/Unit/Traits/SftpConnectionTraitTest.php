<?php

use Tests\TestCase;
use App\Traits\SftpConnectionTrait;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;

class SftpConnectionTraitTest extends TestCase
{
    use SftpConnectionTrait;

    public function testSuccessfulSftpConnection()
    {
       
        $sftp = $this->testConnection();
        $this->assertInstanceOf(\phpseclib3\Net\SFTP::class, $sftp);
    }

}