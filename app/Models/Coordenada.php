<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coordenada extends Model
{
    protected $table = 'modules.coordenadas';
    protected $primaryKey = 'cod_coordenada';
    public $timestamps = false;

    protected $fillable = [
        'cod_usuario',
        'dh_coordenada',
        'latitude',
        'longitude',
    ];
}
