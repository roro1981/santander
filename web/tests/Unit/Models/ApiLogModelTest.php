<?php

namespace Tests\Unit\Models;

use App\Models\Api_log;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiLogModelTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        DB::table('bbs_api_log')->insert([
            'alg_id' => 1,
            'alg_external_id' => 1,
            'alg_url' => 'https://santander.com/api/2.0/payments',
            'alg_request' => '{"subject":"Test integracion"}',
            'alg_response' => '{"payment_id":"pot7vyqt8i5r"}',
            'alg_status_code' => 200,
            'alg_created_at' => now()
        ]);
    }

    public function testApiLogModel()
    {
        $apiLog = Api_log::where('alg_id', 1)->first();
        $this->assertInstanceOf(Api_log::class, $apiLog);

        $this->assertEquals(1, $apiLog->alg_id);
        $this->assertEquals(1, $apiLog->alg_external_id);
        $this->assertEquals('https://santander.com/api/2.0/payments', $apiLog->alg_url);
        $this->assertEquals('{"subject":"Test integracion"}', $apiLog->alg_request);
        $this->assertEquals('{"payment_id":"pot7vyqt8i5r"}', $apiLog->alg_response);
        $this->assertEquals(200, $apiLog->alg_status_code);

        $this->assertContains('alg_external_id', $apiLog->getFillable());
        $this->assertContains('alg_url', $apiLog->getFillable());
        $this->assertContains('alg_request', $apiLog->getFillable());
        $this->assertContains('alg_response', $apiLog->getFillable());
        $this->assertContains('alg_status_code', $apiLog->getFillable());
    }

    public function testStoreLog()
    {
        $response = ['subject' => 'Test store'];
        $apiLog = Api_log::storeLog(1, 'https://santander.com/api/2.0/payments', $response);
        $this->assertNotNull($apiLog);
        $this->assertEquals(1, $apiLog->alg_external_id);
        $this->assertEquals('https://santander.com/api/2.0/payments', $apiLog->alg_url);
        $this->assertEquals(json_encode($response), $apiLog->alg_request);
        $this->assertNull($apiLog->alg_response);
        $this->assertNull($apiLog->alg_status_code);
    }

    public function testUpdateLog()
    {
        $response = ['alg_response' => 'boagngwaoamh'];
        $apiLog = Api_log::where('alg_id', 1)->first();
        $apiLog->updateLog(['alg_response' => 'boagngwaoamh'], 200);
        $this->assertEquals(json_encode($response), $apiLog->alg_response);
        $this->assertEquals(200, $apiLog->alg_status_code);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
