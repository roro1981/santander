<?php

use Tests\TestCase;
use App\Traits\SftpConnectionTrait;
use Database\Seeders\ParameterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

}