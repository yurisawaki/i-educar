<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lealsis.out_tbl_rota_trajeto', function (Blueprint $table) {
            $table->bigIncrements('id_rota_trajeto');
            $table->string('id_token')->nullable(); // movido para depois
            $table->unsignedBigInteger('id_rota');
            $table->unsignedBigInteger('id_ponto_1')->nullable();
            $table->unsignedBigInteger('id_ponto_2')->nullable();
            $table->timestamp('dh_sincronizacao')->nullable();
            $table->text('dh_coordenada')->nullable();
            $table->text('nu_latitude')->nullable();
            $table->text('nu_longitude')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('lealsis.out_tbl_rota_trajeto');
    }
};
