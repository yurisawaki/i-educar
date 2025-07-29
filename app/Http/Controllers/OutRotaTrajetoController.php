<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OutRotaTrajetoController extends Controller
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

        $validated = $request->validate([
            'pFetch' => 'required|integer|min:1',
            'pOffset' => 'required|integer|min:0'
        ]);

        $pFetch = $validated['pFetch'];
        $pOffset = $validated['pOffset'];
        $token = $user->currentAccessToken()->token;

        if ($pOffset == 0) {
            // Limpa registros antigos ou de outro token
            DB::delete("
                DELETE FROM lealsis.out_tbl_rota_trajeto
                WHERE id_token = ? OR dh_sincronizacao < CURRENT_DATE - INTERVAL '1 day'
            ", [$token]);
        }

        DB::insert("
            INSERT INTO lealsis.out_tbl_rota_trajeto (
                id_token,
                dh_sincronizacao,
                id_rota,
                id_rota_trajeto,
                nu_ordem,
                nu_latitude,
                nu_longitude,
                ds_observacao
            )
            SELECT
                ?,
                NOW(),
                T.id_rota,
                T.id_rota_trajeto,
                T.nu_ordem,
                T.nu_latitude,
                T.nu_longitude,
                T.ds_observacao
            FROM modules.tbl_rota_trajeto T
        ", [$token]);


        // Paginar os dados inseridos
        $trajetos = DB::select("
            SELECT *
            FROM lealsis.out_tbl_rota_trajeto
            WHERE id_token = ?
            ORDER BY id_rota_trajeto ASC
            LIMIT ? OFFSET ?
        ", [$token, $pFetch, $pOffset * $pFetch]);

        $count = count($trajetos);
        $inicio = $pOffset + 1;
        $final = $inicio + $count - 1;

        return response()->json([
            'status' => 'success',
            'usuario' => [
                'id' => $user->id,
            ],
            'parametros_recebidos' => [
                'pFetch' => $pFetch,
                'pOffset' => $pOffset
            ],
            'result' => [
                'count' => $count,
                'trajetos' => $trajetos
            ]
        ]);
    }
}
