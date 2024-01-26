<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('bbs_cart_status', function (Blueprint $table) {
            $table->bigIncrements('cas_id')->comment('Id estado carro');
            $table->foreignId('car_id')->constrained('bbs_cart', 'car_id')->comment('Id carro registrado');
            $table->string('cas_status', 45)->comment('Estado del cobro');
            $table->timestamp('cas_created_at')->comment('Fecha creación');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bbs_cart_status');
    }
};
