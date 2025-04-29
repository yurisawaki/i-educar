<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItinerarioTransporteEscolar extends Model
{
    protected $table = 'modules.itinerario_transporte_escolar';
    protected $primaryKey = 'cod_itinerario_transporte_escolar';
    public $timestamps = false;

    public function ponto()
    {
        return $this->belongsTo(PontoTransporteEscolar::class, 'ref_cod_ponto_transporte_escolar', 'cod_ponto_transporte_escolar');
    }
}
