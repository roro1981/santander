<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('bbs_cart', function (Blueprint $table) {
            $table->bigIncrements('car_id')->autoIncrementStartingFrom(1000)->comment('Id carro registrado');
            $table->uuid('car_uuid')->comment('Uuid carro registrado');
            $table->string('car_id_transaction', 36)->comment('Identificador transaccion');
            $table->string('car_flow_currency', 3)->comment('Moneda del cobro');
            $table->integer('car_flow_amount')->comment('Monto total para pagar');
            $table->string('car_agreement', 20)->nullable()->comment('Codigo de convenio asociado al comercio');
            $table->string('car_url', 600)->comment('URL para redireccionamiento autorización de pago');
            $table->unsignedBigInteger('car_expires_at')->comment('Tiempo expiracion codigo QR');
            $table->integer('car_items_number')->comment('Cantidad de detalles a informar');
            $table->string('car_collector', 20)->nullable()->comment('Codigo identificador del recaudador');
            $table->enum('car_status', ['CREATED', 'REGISTERED-CART', 'AUTHORIZED', 'FAILED'])->comment('Estado del cobro');
            $table->string('car_url_return', 600)->nullable()->comment('URL de retorno tras pago exitoso');
            $table->string('car_authorization_uuid', 255)->nullable()->comment('Codigo autorización de orden entregada por el webhook');
            $table->tinyInteger('car_sent_kafka')->length(1)->comment('Verifica si carro fue enviado a kafka');
            $table->string('car_fail_code', 255)->nullable()->comment('Codigo error webhook');
            $table->longText('car_fail_motive')->nullable()->comment('Detalle error webhook');
            $table->integer('car_flow_id')->comment('Identificador de flow');
            $table->integer('car_flow_attempt_number')->comment('Numero de intentos de pago');
            $table->integer('car_flow_method_id')->comment('ID método de pago Flow');
            $table->integer('car_flow_product_id')->comment('Id del producto');
            $table->string('car_flow_email_paid', 255)->comment('Email usuario');
            $table->string('car_flow_subject', 255)->comment('Asunto de transaccion de pago');
            $table->timestamp('car_created_at')->nullable()->default(\DB::raw('CURRENT_TIMESTAMP'))->comment('Fecha creación');
            $table->timestamp('car_updated_at')->nullable()->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Fecha modificación');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('bbs_cart');
    }
};
