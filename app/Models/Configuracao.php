<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracao extends Model
{
    protected $table = 'lealsis.tbl_configuracao';  // ajuste conforme seu banco
    protected $primaryKey = 'id';            // chave primária
    public $timestamps = false;
    protected $fillable = [
        'tempo',
        'distancia',
    ];
}
