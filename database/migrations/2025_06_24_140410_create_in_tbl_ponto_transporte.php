<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS lealsis');

        DB::statement("
            CREATE TABLE IF NOT EXISTS lealsis.in_tbl_ponto_transporte (
                id_ponto INT NOT NULL,
                no_ponto VARCHAR(255) NOT NULL,
                nu_latitude VARCHAR(100),
                nu_longitude VARCHAR(100),
                id_token VARCHAR(255) NOT NULL,
                cd_acao INT NOT NULL,
                dh_sincronizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id_ponto, id_token)
            )
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS lealsis.in_tbl_ponto_transporte");
    }
};
