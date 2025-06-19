<?php

namespace App\Http\Controllers;

use App\Models\Coordenada;
use Illuminate\Http\Request;
use App\Models\ItinerarioTransporteEscolar;


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
        $coordenada->cod_usuario = 1; // Você pode obter isso do usuário logado ou de outra forma
        $coordenada->latitude = $request->latitude;
        $coordenada->longitude = $request->longitude;
        $coordenada->dh_coordenada = now(); // Data e hora atuais

        // Salvando no banco de dados
        $coordenada->save();

        // Retornando uma resposta (pode ser um sucesso ou erro)
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
        return view('layout.mapa-osm-itinerario', [
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
}

