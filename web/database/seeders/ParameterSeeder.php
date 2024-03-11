<?php

namespace Database\Seeders;

use App\Models\Parameter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kafkaTopic = env('KAFKA_NOTIFICATION_TOPIC', 'first_topic');
        $data = [
            [
                'par_code' => 'SANTANDER_TOKEN_COMPANY',
                'par_value' => '768300143',
                'par_description' => 'Company para obtencion bearer token santander',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'SANTANDER_TOKEN_USERNAME',
                'par_value' => '768300143',
                'par_description' => 'Username para obtencion bearer token santander',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'SANTANDER_TOKEN_PASSWORD',
                'par_value' => 'Ax4o5idb_h',
                'par_description' => 'Password para obtencion bearer token santander',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'SANTANDER_TOKEN_URL',
                'par_value' => 'https://paymentbutton-bsan-cert.e-pagos.cl',
                'par_description' => 'URL para obtencion bearer token santander',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'KAFKA_NOTIFICATION_TOPIC',
                'par_value' => $kafkaTopic,
                'par_description' => 'Topic para mensajes de notificación a Kafka',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'KAFKA_PAYMENT_TYPE',
                'par_value' => 'BC',
                'par_description' => 'Detalle de tipo de pago enviado al notificar a Flow Core',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'CURRENCY',
                'par_value' => '999',
                'par_description' => 'Moneda de pago',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'URL_RETORNO',
                'par_value' => 'https://tebi4tbxq0.execute-api.us-west-2.amazonaws.com/QA/santander/v1/redirect',
                'par_description' => 'Url de retorno',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'SFTP_HOST_SANTANDER',
                'par_value' => '200.75.7.235',
                'par_description' => 'Host SFTP SANTANDER',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'SFTP_USERNAME_SANTANDER',
                'par_value' => 'flowsa_bsan',
                'par_description' => 'Username SFTP SANTANDER',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'SFTP_PASSWORD_SANTANDER',
                'par_value' => 'WXv+VC7G',
                'par_description' => 'Password SFTP SANTANDER',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'ORDER_MIN_AMOUNT',
                'par_value' => '1.00',
                'par_description' => 'Monto mínimo permitido para crear ordenes',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'ORDER_MAX_AMOUNT',
                'par_value' => '99999.99',
                'par_description' => 'Monto máximo permitido para crear ordenes',
                'par_created_at' => now()
            ],
        ];
        Parameter::insertOrIgnore($data);
    }
}
