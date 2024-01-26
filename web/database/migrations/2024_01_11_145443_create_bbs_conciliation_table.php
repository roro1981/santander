<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bbs_conciliation', function (Blueprint $table) {
            $table->bigIncrements('con_id')->comment('Id rendicion');
            $table->integer('con_number_payments')->comment('Numero de pagos');
            $table->integer('con_total_amount')->comment('Sumatoria de pagos');
            $table->integer('con_cart_id')->comment('Identificador del carro');
            $table->integer('con_agreement_id')->comment('Identificador del convenio');
            $table->string('con_product_number', 12)->comment('Id del producto pagado');
            $table->string('con_customer_number', 12)->comment('Id del Cliente que realizo el pago');
            $table->datetime('con_product_expiration')->comment('Fecha de Expiración del producto');
            $table->string('con_product_description', 256)->comment('Descripción Producto');
            $table->integer('con_product_amount')->comment('Monto del producto pagado');
            $table->integer('con_operation_number')->comment('Numero de la operación');
            $table->datetime('con_operation_date')->comment('Fecha y hora de la transaccion');
            $table->boolean('con_confirmation')->comment('Confirmacion de envio campo');
            $table->timestamp('con_register_at')->comment('Fecha y hora de registro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bbs_conciliation');
    }
};
