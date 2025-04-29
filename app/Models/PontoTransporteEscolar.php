<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PontoTransporteEscolar extends Model
{
    protected $table = 'modules.ponto_transporte_escolar';
    protected $primaryKey = 'cod_ponto_transporte_escolar';
    public $timestamps = false;
}
