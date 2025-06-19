<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItinerarioApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // Usuário autenticado via Sanctum

        if (!$user) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Usuário não autenticado'
            ], 401);
        }

        $userId = $user->id;

        // Validação dos parâmetros
        $validated = $request->validate([
            'pFetch' => 'required|integer|min:1',
            'pOffset' => 'required|integer|min:0'
        ]);

        $pFetch = $validated['pFetch'];
        $pOffset = $validated['pOffset'];

        if ($pOffset == 0) {
            // Remove dados antigos do cache para este token
            DB::delete("
                DELETE FROM lealsis.out_tbl_itinerario_transporte
                WHERE id_token = ?
            ", [$user->currentAccessToken()->token]);

            // Insere dados atualizados, ignorando duplicatas
            DB::insert("
                INSERT INTO lealsis.out_tbl_itinerario_transporte (
                    id_token,
                    dh_sincronizacao,
                    id_itinerario,
                    id_rota,
                    id_ponto,
                    nu_sequencia,
                    hr_ponto,
                    ds_rota_tipo
                )
                SELECT
                    ?,
                    NOW(),
                    I.cod_itinerario_transporte_escolar,
                    I.ref_cod_rota_transporte_escolar,
                    I.ref_cod_ponto_transporte_escolar,
                    I.seq,
                    I.hora,
                    CASE WHEN I.tipo = 'I' THEN 'IDA' ELSE 'VOLTA' END
                FROM modules.itinerario_transporte_escolar I
            ", [$user->currentAccessToken()->token]);
        }

        // Consulta com paginação baseada no token do Sanctum
        $itinerarios = DB::select("
            SELECT *
            FROM lealsis.out_tbl_itinerario_transporte
            WHERE id_token = ?
            ORDER BY nu_sequencia ASC
            LIMIT ? OFFSET ?
        ", [$user->currentAccessToken()->token, $pFetch, $pOffset * $pFetch]);

        $count = count($itinerarios);
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
                'itinerarios' => $itinerarios
            ]
        ]);
    }
}
