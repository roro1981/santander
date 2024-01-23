<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bbs_api_log', function (Blueprint $table) {
            $table->bigIncrements('alg_id')->comment('Id log');
            $table->integer('alg_external_id')->comment('Id de la orden en Flow');
            $table->string('alg_url', 600)->comment('URL de API POST');
            $table->string('alg_request', 600)->nullable()->comment('Body enviado en API POST');
            $table->string('alg_response', 600)->nullable()->comment('Response de API POST');
            $table->integer('alg_status_code')->nullable()->comment('HTTP Status Code de API POST');
            $table->timestamp('alg_created_at')->comment('Fecha creación');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bbs_api_log');
    }
};
