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
        Schema::create('lealsis.tbl_wwebjs', function (Blueprint $table) {
            $table->id('id_msg'); // serial
            $table->timestamp('dh_msg')->useCurrent(); // timestamp atual
            $table->text('gn_msg'); // mensagem em texto
            $table->json('gn_json'); // JSON completo
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_wwebjs');
    }
};
