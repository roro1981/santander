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
            $table->integer('con_cart_id')->comment('Identificador del carro');
            $table->integer('con_agreement_id')->comment('Identificador del convenio');
            $table->string('con_product_number', 12)->comment('Id del producto pagado');
            $table->string('con_customer_number', 12)->comment('Id del Cliente que realizo el pago');
            $table->datetime('con_product_expiration')->comment('Fecha de Expiración del producto');
            $table->string('con_product_description', 256)->comment('Descripción Producto');
            $table->integer('con_product_amount')->comment('Monto del producto pagado');
            $table->integer('con_operation_number')->comment('Numero de la operación');
            $table->datetime('con_operation_date')->comment('Fecha y hora de la transaccion');
            $table->string('con_status', 50)->comment('Status transaccion:OK, NO EXISTE, INCONSISTENCIA PAGO');
            $table->string('con_file_process', 256)->comment('Archivo de origen del registro');
            $table->timestamp('con_created_at')->nullable()->default(\DB::raw('CURRENT_TIMESTAMP'))->comment('Fecha creación');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bbs_conciliation');
    }
};
