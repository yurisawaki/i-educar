<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Cria schema se ainda não existir
        DB::statement('CREATE SCHEMA IF NOT EXISTS lealsis');

        // Cria a tabela manualmente, sem updated_at
        DB::statement('
    CREATE TABLE IF NOT EXISTS lealsis.tbl_ponto_imagem (
        id_ponto INT PRIMARY KEY,
        no_imagem VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        tamanho_imagem INT,
        CONSTRAINT fk_ponto FOREIGN KEY (id_ponto)
            REFERENCES modules.ponto_transporte_escolar(cod_ponto_transporte_escolar)
            ON DELETE CASCADE
    )
');

    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS lealsis.tbl_ponto_imagem');
    }
};
