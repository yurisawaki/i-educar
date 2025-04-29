<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateModulesCoordenadas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(
            '
                -- Criação do esquema modules, se não existir
                CREATE SCHEMA IF NOT EXISTS modules;

                -- Criação da sequência para gerar a chave primária automaticamente
                CREATE SEQUENCE IF NOT EXISTS modules.coordenadas_seq
                    START WITH 1
                    INCREMENT BY 1
                    NO MINVALUE
                    NO MAXVALUE
                    CACHE 1;

                -- Criação da tabela coordenadas
                CREATE TABLE modules.coordenadas (
                    cod_coordenada integer DEFAULT nextval(\'modules.coordenadas_seq\'::regclass) NOT NULL,
                    cod_usuario integer NOT NULL,
                    dh_coordenada timestamp without time zone DEFAULT now() NOT NULL,
                    latitude decimal(9, 6) NOT NULL,  -- Usando decimal para coordenadas
                    longitude decimal(9, 6) NOT NULL, -- Usando decimal para coordenadas
                    CONSTRAINT coordenadas_pkey PRIMARY KEY (cod_coordenada)
                );

                -- Inicializa a sequência com o valor correto
                SELECT pg_catalog.setval(\'modules.coordenadas_seq\', 1, false);
            '
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Apaga a tabela e a sequência se existirem
        DB::unprepared('DROP TABLE IF EXISTS modules.coordenadas CASCADE;');
        DB::unprepared('DROP SEQUENCE IF EXISTS modules.coordenadas_seq;');
    }
}
