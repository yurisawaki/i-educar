<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {

        DB::statement('CREATE SCHEMA IF NOT EXISTS lealsis');

        DB::statement("
            CREATE TABLE IF NOT EXISTS lealsis.out_tbl_rota_transporte (
                id_token VARCHAR(255) NOT NULL,
                dh_sincronizacao TIMESTAMP,

                id_rota INT,
                nu_ano INT,
                no_rota VARCHAR(255),
                id_destino BIGINT,
                no_destino VARCHAR(255),
                ds_rota_tipo VARCHAR(50),
                is_transportadora INT,
                no_transportadora VARCHAR(255),
                is_terceirizado CHAR(1)
            )
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS lealsis.out_tbl_rota_transporte');
    }
};
