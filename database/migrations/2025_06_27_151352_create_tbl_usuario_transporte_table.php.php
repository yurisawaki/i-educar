<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS lealsis');

        Schema::create('lealsis.tbl_usuario_transporte', function (Blueprint $table) {
            $table->id();
            $table->integer('cod_usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lealsis.tbl_usuario_transporte');
    }
};
