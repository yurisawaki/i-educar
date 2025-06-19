<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Garante que o schema 'lealsis' exista
        DB::statement('CREATE SCHEMA IF NOT EXISTS lealsis');

        // Cria a tabela dentro do schema 'lealsis'
        DB::statement("
            CREATE TABLE IF NOT EXISTS lealsis.out_tbl_usuario_transporte (
                id_token VARCHAR(255) NOT NULL,
                dh_sincronizacao TIMESTAMP,
                cod_usuario INT,
                nome VARCHAR(300),
                matricula VARCHAR(100),
                senha VARCHAR(256)
            )
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS lealsis.out_tbl_usuario_transporte');
    }
};
