<?php

namespace Tests\Unit\Jobs;

use App\Jobs\KafkaNotification;
use App\Models\Cart;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Junges\Kafka\Facades\Kafka;
use Mockery;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;
use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;

class KafkaNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function testKafkaNotification()
    {
        $order = Cart::factory()->create([
            'car_id_transaction' => Uuid::uuid4(),
            'car_flow_currency' => 'CLP',
            'car_flow_amount' => '2.0000',
            'car_url' => 'https://flow.cl/retorno.php',
            'car_expires_at' => '1699569123',
            'car_items_number' => 1,
            'car_status' => 'CREATED',
            'car_url_return' => 'https://flow.cl/retorno.php',
            'car_sent_kafka' => 0,
            'car_flow_id' => 1,
            'car_flow_attempt_number' => 1,
            'car_flow_method_id' => ParamUtil::getParam(Constants::PARAM_ALLOWED_METHODS),
            'car_flow_product_id' => 1,
            'car_flow_email_paid' => 'rpanes@tuxpan.com',
            'car_flow_subject' => 'Test integracion',
            'car_created_at' => now()
        ]);
        $san=167896;

        $mockKafka = Mockery::mock('overload:' . Kafka::class);
        $mockKafka->shouldReceive('publishOn')->andReturnSelf();
        $mockKafka->shouldReceive('withMessage')->andReturnSelf();
        $mockKafka->shouldReceive('withSasl')->andReturnSelf();
        $mockKafka->shouldReceive('send')->andReturn();
        $this->instance(Kafka::class, $mockKafka);

        $kafkaJob = new KafkaNotification($order,$san);
        $kafkaJob->handle();

        $this->assertEquals(0, $order->car_sent_kafka);
    }

    public function testKafkaNotificationRejected()
    {
        $order = Cart::factory()->create([
            'car_id_transaction' => Uuid::uuid4(),
            'car_flow_currency' => 'CLP',
            'car_flow_amount' => '2.0000',
            'car_url' => 'https://flow.cl/retorno.php',
            'car_expires_at' => '1699569123',
            'car_items_number' => 1,
            'car_status' => 'CREATED',
            'car_url_return' => 'https://flow.cl/retorno.php',
            'car_sent_kafka' => 1,
            'car_flow_id' => 1,
            'car_flow_attempt_number' => 1,
            'car_flow_method_id' => ParamUtil::getParam(Constants::PARAM_ALLOWED_METHODS),
            'car_flow_product_id' => 1,
            'car_flow_email_paid' => 'rpanes@tuxpan.com',
            'car_flow_subject' => 'Test integracion',
            'car_created_at' => now()
        ]);
        $san=167896;

        $mockKafka = Mockery::mock('overload:' . Kafka::class);
        $mockKafka->shouldReceive('publishOn')->andReturnSelf();
        $mockKafka->shouldReceive('withMessage')->andReturnSelf();
        $mockKafka->shouldReceive('withSasl')->andReturnSelf();
        $mockKafka->shouldReceive('send')->andReturn();
        $this->instance(Kafka::class, $mockKafka);
   
        $kafkaJob = new KafkaNotification($order,$san);
        $kafkaJob->handle();
        
        $this->assertEquals(1, $order->car_sent_kafka);
    }

    public function testKafkaNotificationException()
    {
        Queue::fake();
        $order = Cart::factory()->create([
            'car_id_transaction' => Uuid::uuid4(),
            'car_flow_currency' => 'CLP',
            'car_flow_amount' => '2.0000',
            'car_url' => 'https://flow.cl/retorno.php',
            'car_expires_at' => '1699569123',
            'car_items_number' => 1,
            'car_status' => 'CREATED',
            'car_url_return' => 'https://flow.cl/retorno.php',
            'car_sent_kafka' => 1,
            'car_flow_id' => 1,
            'car_flow_attempt_number' => 1,
            'car_flow_method_id' => ParamUtil::getParam(Constants::PARAM_ALLOWED_METHODS),
            'car_flow_product_id' => 1,
            'car_flow_email_paid' => 'rpanes@tuxpan.com',
            'car_flow_subject' => 'Test integracion',
            'car_created_at' => now()
        ]);
        $san=167896;
        $mockKafka = Mockery::mock('overload:' . Kafka::class);
        $mockKafka->shouldReceive('publishOn')->andThrow(Exception::class);
        $this->instance(Kafka::class, $mockKafka);

        $kafkaJob = new KafkaNotification($order);
        $kafkaJob->handle();

        Queue::assertPushed(KafkaNotification::class);
    }
    
    public function tearDown(): void
    {
        
        parent::tearDown();
        Mockery::close();
    }
}
