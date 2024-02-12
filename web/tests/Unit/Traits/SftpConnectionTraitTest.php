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
    // Seed los parámetros necesarios, pero establezca los valores de host, usuario o contraseña
    // de manera que la conexión falle
    //$this->seed(ParameterSeeder::class);

    // Modifica los parámetros para que la conexión falle
    config([
        'constants.PARAM_SANTANDER_SFTP_HOST' => 'host_incorrecto',
        'constants.PARAM_SANTANDER_SFTP_USERNAME' => 'usuario_incorrecto',
        'constants.PARAM_SANTANDER_SFTP_PASSWORD' => 'contraseña_incorrecta'
    ]);

    // Realiza la prueba de conexión
    $result = $this->testConnection();

    // Verifica que el resultado sea una respuesta JSON con el código de error 500
    $this->assertEquals(500, $result->getStatusCode());
    $this->assertJson($result->getContent());
    $responseContent = json_decode($result->getContent(), true);
    $this->assertEquals(500, $responseContent['error']);
}

}