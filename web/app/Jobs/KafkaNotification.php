<?php

namespace App\Jobs;

use App\Http\Utils\Constants;
use App\Models\Cart;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Config\Sasl;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
use Ramsey\Uuid\Uuid;

class KafkaNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $order;
    private $topic;
    private $saslUsername;
    private $saslPassword;

    public function __construct(Cart $order, String $topic)
    {
        $this->order = $order;
        $this->topic = $topic;
        $this->saslUsername = config('kafka.sasl.username') ?? 'user';
        $this->saslPassword = config('kafka.sasl.password') ?? 'password';
    }

    public function handle(): void
    {
        Log::info('Iniciando job envio a Kafka');
        $body = [
            'uuid' => Uuid::uuid4(),
            'id' => $this->order->ord_flow_id,
            'external_id' => $this->order->car_id,
            'product_id' => $this->order->ord_flow_product,
            'payer_email' => $this->order->ord_payer_email,
            'date_notification' => Carbon::now(),
            'amount_paid' => floatval($this->order->ord_amount),
            'currency_paid' => $this->order->ord_currency,
            'payment_detail' => json_encode([
                'type' => Constants::PARAM_KAFKA_PAYMENT_TYPE
            ], true),
            'status' => $this->order->ord_status
        ];

        Log::debug('Mensaje: ' . json_encode($body));
        $message = new Message(
            topicName: $this->topic,
            key: $body['status'],
            body: $body,
        );

        try {
            $producer = Kafka::publishOn($message->getTopicName())
                ->withMessage($message)
                ->withSasl(new Sasl($this->saslUsername, $this->saslPassword, 'SCRAM-SHA-512', 'SASL_SSL'));
            $producer->send();
            Log::info("Evento enviado: {$message->getTopicName()}");
            $this->order->update(['ord_sent_kafka' => true]);
        } catch (Exception $e) {
            Log::error("Error al enviar evento a kafka: {$e->getMessage()}");
            KafkaNotification::dispatch($this->order)->onQueue('kafkaNotification')->delay(5);
        }
        Log::info("Finalizando envio a kafka");
    }
}
