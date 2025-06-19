<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nucleo extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_nucleo'; // Nome real da tabela

    protected $primaryKey = 'cd_nucleo'; // Chave primária

    protected $fillable = [
        'no_nucleo',
        'ds_nucleo',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
