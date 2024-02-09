<?php

use Tests\TestCase;
use App\Traits\SftpConnectionTrait;
use Database\Seeders\ParameterSeeder;

class SftpConnectionTraitTest extends TestCase
{
    use SftpConnectionTrait;

    public function testSuccessfulSftpConnection()
    {
        $this->seed(ParameterSeeder::class);
        $sftp = $this->testConnection();
        
        $this->assertInstanceOf(\phpseclib3\Net\SFTP::class, $sftp);
    }

}