<?php

namespace Tests\Unit;
namespace App\Http\Utils;
use App\Http\Clients\SantanderClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use Mockery;
use Database\Seeders\ParameterSeeder;

class SantanderClientTest extends TestCase
{
    public $token;

    public function testGetBearerToken()
    {
        $this->seed(ParameterSeeder::class);
        Http::fake([
            '*/auth/basic/token' => Http::response(['token_type' => 'Bearer', 'access_token' => 'test_token'], 200),
        ]);

        $mockSantanderResponse="Bearer test_token";
        
        $mockSantanderClient = Mockery::mock('overload:' . SantanderClient::class);
        $mockSantanderClient->shouldReceive('getBearerToken')->andReturn($mockSantanderResponse);
        $this->instance(SantanderClient::class, $mockSantanderClient);
        $santanderClient = new SantanderClient();
        $token = $santanderClient->getBearerToken();
        $this->assertEquals('Bearer test_token', $token);
       
    }

   
}
