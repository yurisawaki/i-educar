<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wwebjs extends Model
{
    use HasFactory;

    protected $table = 'lealsis.tbl_wwebjs';
    protected $primaryKey = 'id_msg';
    public $timestamps = false; // já temos dh_msg manual

    protected $fillable = [
        'gn_msg',
        'gn_json',
        'dh_msg',
    ];

    protected $casts = [
        'gn_json' => 'array', // para salvar e recuperar JSON
        'dh_msg' => 'datetime',
    ];
}
