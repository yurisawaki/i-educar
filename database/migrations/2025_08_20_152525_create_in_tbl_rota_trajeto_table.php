<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lealsis.in_tbl_rota_trajeto', function (Blueprint $table) {
            $table->bigIncrements('id_rota_trajeto');
            $table->string('id_token');
            $table->unsignedBigInteger('id_rota');
            $table->unsignedBigInteger('id_ponto_1')->nullable();
            $table->unsignedBigInteger('id_ponto_2')->nullable();
            $table->timestamp('dh_sincronizacao')->nullable();
            $table->timestamp('dh_coordenada')->nullable();
            $table->string('nu_latitude')->nullable();
            $table->string('nu_longitude')->nullable();
            $table->string('no_imagem')->nullable();

            // Índices opcionais para otimizar consultas
            $table->index('id_token');
            $table->index('id_rota');
            $table->index(['id_ponto_1', 'id_ponto_2']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lealsis.in_tbl_rota_trajeto');
    }
};
