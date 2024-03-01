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
        try {
            $sftp = $this->testConnection();
            $this->assertInstanceOf(\phpseclib3\Net\SFTP::class, $sftp); 
        } catch (\Exception $e) {
            $this->assertEquals('Error de conexión', $e->getMessage());
        }
    }

    public function testConnectionFailure()
    {
        try{
            $this->seed(ParameterSeeder2::class);
            $result=$this->testConnection();
        }catch(\Exception $e){
            $this->assertEquals('{"error":500,"message":"Excepci\u00f3n general: Error de conexi\u00f3n\n"}', $e->getMessage());
        }
        
   
        
            
    }
}