<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ata extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_ata'; // Ajuste conforme o schema e nome real da tabela

    protected $primaryKey = 'cd_ata';

    protected $fillable = [
        'no_ata',
        'ds_ata',
        'ds_votacao',
        'ds_encerramento',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
