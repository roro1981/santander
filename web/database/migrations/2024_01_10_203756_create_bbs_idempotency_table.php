<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bbs_idempotency', function (Blueprint $table) {
            $table->uuid('idp_uuid')->comment('Identificador único');
            $table->longText('idp_response')->comment('Response API POST');
            $table->string('idp_httpcode', 3)->comment('HTTP Status Code API POST');
            $table->timestamp('idp_created_at')->comment('Fecha creación');
            $table->timestamp('idp_updated_at')->comment('Fecha modificacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bbs_idempotency');
    }
};
