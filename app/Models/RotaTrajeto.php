<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RotaTrajeto extends Model
{
    protected $table = 'lealsis.in_tbl_rota_trajeto';

    protected $primaryKey = 'id_rota_trajeto';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_token',
        'id_rota',
        'id_ponto_1',
        'id_ponto_2',
        'dh_sincronizacao',
        'dh_coordenada',
        'nu_latitude',
        'nu_longitude',
        'no_imagem',
    ];


    // Relação para o ponto inicial
    public function ponto1()
    {
        return $this->belongsTo(PontoTransporteEscolar::class, 'id_ponto_1', 'cod_ponto_transporte_escolar');
    }

    // Relação para o ponto final
    public function ponto2()
    {
        return $this->belongsTo(PontoTransporteEscolar::class, 'id_ponto_2', 'cod_ponto_transporte_escolar');
    }
}

