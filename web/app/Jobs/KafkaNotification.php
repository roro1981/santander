<?php

namespace App\Jobs;

use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;
use App\Models\Order;
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
    private $saslUsername;
    private $saslPassword;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->saslUsername = config('kafka.sasl.username') ?? 'user';
        $this->saslPassword = config('kafka.sasl.password') ?? 'password';
    }

    /**
     * Execute the job.
     */
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

        /*if ($body['status'] === 'REJ')
        {
            $rej_detail = [
                'code' => $this->order->ord_status == Constants::STATUS_CANCELED ? '501' : '502',
                'message' => $this->order->ord_status == Constants::STATUS_CANCELED ?
                    'anulada' :
                    $this->order->ord_khipu_status_detail
            ];
            $body['rej_detail'] = json_encode($rej_detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }*/

        Log::debug('Mensaje: ' . json_encode($body));
        $message = new Message(
            topicName: Constants::KAFKA_NOTIFICATION_TOPIC,
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
