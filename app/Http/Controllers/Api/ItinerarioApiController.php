<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class ItinerarioApiController extends Controller
{
    public function index(Request $request)
    {
        // Autenticação via Sanctum
        $user = $request->user();
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

        // Se for o primeiro carregamento (offset 0)
        if ($pOffset == 0) {
            // Limpa dados antigos deste token
            DB::delete("
                DELETE FROM lealsis.out_tbl_itinerario_transporte
                 WHERE id_token = ?
                    OR dh_sincronizacao < CURRENT_DATE - INTERVAL '1 day'
            ", [$user->currentAccessToken()->token]);


            // Insere dados atualizados da origem
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

        // Consulta paginada
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

        // Retorno JSON padronizado
        return response()->json([
            'status' => 'success',
            'usuario' => [
                'id' => $userId,
            ],
            'contadores' => [
                'count' => $count,
                'pFetch' => $pFetch,
                'pOffset' => $pOffset
            ],
            'result' => [
                'itinerarios' => $itinerarios
            ]
        ], 200);
    }
}
