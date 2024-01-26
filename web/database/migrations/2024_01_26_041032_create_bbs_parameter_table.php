<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bbs_parameter', function (Blueprint $table) {
            $table->string('par_code', 255)->unique()->comment('Titulo parametro');
            $table->text('par_value')->comment('Valor parametro');
            $table->string('par_description', 255)->nullable()->comment('Descripcion parametro');
            $table->timestamp('par_created_at')->comment('Fecha creación parametro');
            $table->timestamp('par_updated_at')->nullable()->default(null)->comment('Fecha modificacion parametro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bbs_parameter');
    }
};
