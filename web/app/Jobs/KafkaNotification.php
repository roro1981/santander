<?php

namespace App\Jobs;

use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;
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
    private $sant;
    private $saslUsername;
    private $saslPassword;

    public function __construct(Cart $order)
    {
        $this->order = $order;
        $this->saslUsername = config('kafka.sasl.username') ?? 'user';
        $this->saslPassword = config('kafka.sasl.password') ?? 'password';
    }

    public function handle(): void
    {
        Log::info('Iniciando job envio a Kafka');

        $body = [
            'uuid' => Uuid::uuid4(),
            'id' => $this->order->car_flow_id,
            'external_id' => $this->order->car_id_transaction,
            'product_id' => $this->order->car_flow_product_id,
            'payer_email' => $this->order->car_flow_email_paid,
            'date_notification' => $this->order->car_updated_at,
            'amount_paid' => floatval($this->order->car_flow_amount),
            'currency_paid' => 'CLP',
            'payment_detail' => json_encode([
                'type' => ParamUtil::getParam(Constants::PARAM_KAFKA_PAYMENT_TYPE)
            ], true),
            'status' => $this->order->car_status == Constants::STATUS_AUTHORIZED ? 'PAY' : 'REJ'
        ];
        Log::debug('Mensaje: ' . json_encode($body));
        $message = new Message(
            topicName: ParamUtil::getParam(Constants::KAFKA_NOTIFICATION_TOPIC),
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
