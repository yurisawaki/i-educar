<?php

namespace App\Http\Controllers;

use App\Models\Coordenada;
use Illuminate\Http\Request;
use App\Models\ItinerarioTransporteEscolar;
use Illuminate\Support\Facades\DB;


class CoordenadaController extends Controller
{
    public function store(Request $request)
    {
        // Validação simples (verificando se latitude e longitude são números)
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Criando a coordenada no banco de dados
        $coordenada = new Coordenada();
        $coordenada->cod_usuario = 1;
        $coordenada->latitude = $request->latitude;
        $coordenada->longitude = $request->longitude;
        $coordenada->dh_coordenada = now();


        $coordenada->save();


        return response()->json(['message' => 'Coordenada salva com sucesso!'], 200);
    }


    public function mapaItinerario(Request $request)
    {
        return view('layout.mapa-itinerario', [
            'pontos' => ItinerarioTransporteEscolar::with('ponto')
                ->where('ref_cod_rota_transporte_escolar', $request->query('cod_rota'))
                ->orderBy('seq')
                ->get()
                ->pluck('ponto')
                ->filter(fn($p) => $p && $p->latitude && $p->longitude)
                ->map(fn($p) => [
                    'latitude' => (float) $p->latitude,
                    'longitude' => (float) $p->longitude,
                    'descricao' => $p->descricao,
                ])
                ->values()
        ]);
    }

    public function mapaItinerarioOsm(Request $request)
    {
        $idRota = $request->query('cod_rota');

        $rota = DB::selectOne(
            "SELECT * FROM lealsis.tbl_rota_trajeto WHERE id_rota = ? ORDER BY id_rota LIMIT 1",
            [$idRota]
        );

        if ($rota) {
            $pontosTrajeto =
                DB::select(

                    "SELECT RT.id_rota
                         ,RT.id_ponto_1 AS id_ponto_origem
                         ,RT.id_ponto_2 AS id_ponto_destino
                         ,RT.dh_coordenada
                         ,RT.nu_latitude
                         ,RT.nu_longitude
                         ,CASE WHEN RT.id_ponto_1 = RT.id_ponto_2 
                                 THEN COALESCE(
                                         (SELECT P.descricao 
                                          FROM modules.ponto_transporte_escolar P 
                                          WHERE P.cod_ponto_transporte_escolar = RT.id_ponto_1), 
                                          'PONTO NÃO LOCALIZADO') 
                                 ELSE '' 
                             END AS no_ponto
                     FROM lealsis.tbl_rota_trajeto RT
                     WHERE id_rota = ?
                     ORDER BY dh_coordenada ASC;
                        
                ",

                    [$idRota]
                );

            $pontosTrajeto = collect($pontosTrajeto)->map(function ($p) {
                $p->latitude = (float) $p->nu_latitude;
                $p->longitude = (float) $p->nu_longitude;
                return $p;
            });

            return view('layout.mapa-osm-itinerario', [
                'pontosItinerario' => collect(),
                'pontosTrajeto' => $pontosTrajeto,
                'rota' => $rota,
            ]);
        } else {
            $pontosItinerario = ItinerarioTransporteEscolar::with('ponto')
                ->where('ref_cod_rota_transporte_escolar', $idRota)
                ->orderBy('seq')
                ->get()
                ->pluck('ponto')
                ->filter(fn($p) => $p && $p->latitude && $p->longitude)
                ->map(fn($p) => [
                    'latitude' => (float) $p->latitude,
                    'longitude' => (float) $p->longitude,
                    'descricao' => $p->descricao,
                ])
                ->values();

            return view('layout.mapa-osm-itinerario', [
                'pontosItinerario' => $pontosItinerario,
                'pontosTrajeto' => collect(),
                'rota' => null
            ]);
        }
    }


}

