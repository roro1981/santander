<?php

use Tests\TestCase;
use App\Traits\SftpConnectionTrait;
use Database\Seeders\ParameterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;

class SftpConnectionTraitTest extends TestCase
{
    use SftpConnectionTrait;
    use RefreshDatabase;

    public function testSuccessfulSftpConnection()
    {
        $this->seed(ParameterSeeder::class);
        $sftp = $this->testConnection();
        
        $this->assertInstanceOf(\phpseclib3\Net\SFTP::class, $sftp);
    }

    public function testFailedConnection()
    {
        

        try{
            $sftpMock = $this->createMock(\phpseclib3\Net\SFTP::class);
            $sftpMock->method('login')->willReturn(false);
        }catch(Exception $e){
            $this->expectException($e);
            $this->expectExceptionMessage('Error de conexión');
        }
        
    }
}