<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Garante que o schema 'lealsis' exista
        DB::statement('CREATE SCHEMA IF NOT EXISTS lealsis');

        // Cria a tabela dentro do schema 'lealsis'
        DB::statement("
            CREATE TABLE IF NOT EXISTS lealsis.out_tbl_ponto_transporte (
                id_token VARCHAR(255) NOT NULL,
                dh_sincronizacao TIMESTAMP,
                id_ponto INT,
                no_ponto VARCHAR(255),
                nu_latitude VARCHAR(100),
                nu_longitude VARCHAR(100)
            )
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS lealsis.out_tbl_ponto_transporte');
    }
};
