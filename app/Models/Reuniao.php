<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reuniao extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_reuniao';
    protected $primaryKey = 'cd_reuniao';

    protected $fillable = [
        'cd_conselho',
        'ds_reuniao',
        'cd_reuniao_status',
        'cd_local',
        'dh_reuniao',
        'dh_primeira_chamada',
        'dh_segunda_chamada',
        'dh_inicial',
        'dh_final',
        'cd_convocatoria',
        'dh_convocatoria',
        'cd_ata',
        'no_reuniao'
    ];

    protected $dates = [
        'dh_reuniao',
        'dh_primeira_chamada',
        'dh_segunda_chamada',
        'dh_inicial',
        'dh_final',
        'dh_convocatoria',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
