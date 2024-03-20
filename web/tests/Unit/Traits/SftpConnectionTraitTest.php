<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Traits\SftpConnectionTrait;
use phpseclib3\Net\SFTP;
use Mockery;
use App\Http\Utils\ParamUtil;
use App\Http\Utils\Constants;

class SftpConnectionTraitTest extends TestCase
{
    use SftpConnectionTrait;
    
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testSuccessfulSftpConnection()
    {
        $ftpUsername = ParamUtil::getParam(Constants::PARAM_SANTANDER_SFTP_USERNAME);
        $ftpPassword = ParamUtil::getParam(Constants::PARAM_SANTANDER_SFTP_PASSWORD);
  
        $mockSftp = Mockery::mock('overload:' . SFTP::class);
        $mockSftp->shouldReceive('login')->with($ftpUsername, $ftpPassword)->andReturn(true);

        $sftpConnection = $this->testConnection();
        $this->assertInstanceOf(SFTP::class, $sftpConnection);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws Exception
     */
    public function testSftpConnectionFailure()
    {
        $ftpUsername = ParamUtil::getParam(Constants::PARAM_SANTANDER_SFTP_USERNAME);
        $ftpPassword = ParamUtil::getParam(Constants::PARAM_SANTANDER_SFTP_PASSWORD);

        $mockSftp = Mockery::mock('overload:' . SFTP::class);
        $mockSftp->shouldReceive('login')->with($ftpUsername, $ftpPassword)->andReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error de conexión');
        $result = $this->testConnection();
    
    }

}