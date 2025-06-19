<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conselho extends Model
{
    use SoftDeletes;

    protected $table = "tbl_conselho";

    protected $primaryKey = "cd_conselho"; // Corrigido 'primarykey' para 'primaryKey'

    protected $fillable = [
        'no_conselho',
        'ds_conselho',
        'dt_inicial',
        'dt_final',
        'cd_ata',
        'is_ativo',
        'cd_nucleo',
    ];

    // Caso queira, pode configurar as datas para mutators
    protected $dates = [
        'dt_inicial',
        'dt_final',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $timestamps = true; // created_at e updated_at

}
