<?php

use Tests\TestCase;
use App\Traits\SftpConnectionTrait;
use Database\Seeders\ParameterSeeder;
use Database\Seeders\ParameterSeeder2;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Parameter;

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

    public function testFailedSftpConnection()
    {

        $result = $this->testConnection();

        $this->assertEquals(500, $result->getStatusCode());
        $this->assertJson($result->getContent());
        $responseContent = json_decode($result->getContent(), true);
        $this->assertEquals(500, $responseContent['error']);
    }

    public function testConnectionFailure()
    {
        $this->seed(ParameterSeeder2::class);
   
        $result=$this->testConnection();
        
        $this->assertEquals('{"error":500,"message":"Excepci\u00f3n general: Error de conexi\u00f3n\n"}', $result->getContent());
            
    }
}