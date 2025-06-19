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
            CREATE TABLE IF NOT EXISTS lealsis.out_tbl_itinerario_transporte (
                id_token VARCHAR(255) NOT NULL,
                dh_sincronizacao TIMESTAMP,

                id_itinerario INT,
                id_rota INT,
                id_ponto INT,
                nu_sequencia INT,
                hr_ponto TIME,
                ds_rota_tipo VARCHAR(50)
            )
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS lealsis.out_tbl_itinerario_transporte');
    }
};
