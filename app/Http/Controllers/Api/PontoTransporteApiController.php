<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PontoTransporteApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Usuário não autenticado'
            ], 401);
        }

        $userId = $user->id;

        $validated = $request->validate([
            'pFetch' => 'required|integer|min:1',
            'pOffset' => 'required|integer|min:0'
        ]);

        $pFetch = $validated['pFetch'];
        $pOffset = $validated['pOffset'];



        DB::delete("
                DELETE FROM lealsis.out_tbl_ponto_transporte
                WHERE id_token = ? OR dh_sincronizacao < CURRENT_DATE - INTERVAL '1 day'

            ", [$user->currentAccessToken()->token]);


        DB::insert("
                INSERT INTO lealsis.out_tbl_ponto_transporte (
                    id_token,
                    dh_sincronizacao,
                    id_ponto,
                    no_ponto,
                    nu_latitude,
                    nu_longitude
                )
                SELECT
                    ?,
                    CURRENT_TIMESTAMP AT TIME ZONE 'America/fortaleza',
                    P.cod_ponto_transporte_escolar,
                    P.descricao,
                    P.latitude,
                    P.longitude
                FROM modules.ponto_transporte_escolar P
            ", [$user->currentAccessToken()->token]);


        // Consulta com paginação baseada no token
        $pontos = DB::select("
            SELECT P.*, PI.no_imagem, PI.tamanho_arquivo
            FROM lealsis.out_tbl_ponto_transporte P
            LEFT JOIN lealsis.tbl_ponto_imagem PI ON PI.id_ponto = P.id_ponto
            WHERE P.id_token = ?
            ORDER BY P.no_ponto ASC
            LIMIT ? OFFSET ?
        ", [$user->currentAccessToken()->token, $pFetch, $pOffset * $pFetch]);



        $count = count($pontos);
        $inicio = $pOffset + 1;
        $final = $inicio + $count - 1;

        return response()->json([
            'status' => 'success',
            'usuario' => [
                'id' => $userId,
            ],
            'parametros_recebidos' => [
                'pFetch' => $pFetch,
                'pOffset' => $pOffset
            ],
            'result' => [
                'count' => $count,
                'pontos' => $pontos
            ]
        ], 200);
    }
}
