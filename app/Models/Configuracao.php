<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    protected $table = 'tbl_configuracao';  // tabela do banco
    protected $primaryKey = 'id';            // chave primária
    public $timestamps = true;

    protected $fillable = [
        'tempo',
        'distancia',
    ];

    public $incrementing = true;  // indica que o id é auto-increment
    protected $keyType = 'int';   // tipo da chave primária
}
