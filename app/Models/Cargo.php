<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cargo extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_cargo'; // Ajuste conforme o schema e nome real da tabela

    protected $primaryKey = 'cd_cargo';

    protected $fillable = [
        'ds_cargo',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
