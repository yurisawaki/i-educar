<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OutRotaTrajetoApiController extends Controller
{
    public function index(Request $request)
    {
        // Autenticação
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

        // Se offset for 0, limpar e popular a tabela
        if ($pOffset == 0) {
            // Deletar registros antigos
            DB::delete("
                DELETE FROM lealsis.out_tbl_rota_trajeto
                 WHERE id_token = ?
                    OR dh_sincronizacao < CURRENT_DATE - INTERVAL '1 day'
            ", [$user->currentAccessToken()->token]);

            // Inserir novos dados
            $token = $user->currentAccessToken()->token;

            DB::insert("
                INSERT INTO lealsis.out_tbl_rota_trajeto (
                    id_token,
                    dh_sincronizacao, 
                    id_rota_trajeto,
                    id_rota,
                    id_ponto_1,
                    id_ponto_2,
                    dh_coordenada,
                    nu_latitude,
                    nu_longitude
                )
                SELECT
                    ? as id_token,
                    CURRENT_TIMESTAMP AT TIME ZONE 'America/Fortaleza',
                    RT.id_rota_trajeto,
                    RT.id_rota,
                    RT.id_ponto_1,
                    RT.id_ponto_2,
                    RT.dh_coordenada,
                    RT.nu_latitude,
                    RT.nu_longitude
                FROM lealsis.tbl_rota_trajeto RT
            ", [$token]);

        }

        // SELECT paginado dos dados
        $trajetos = DB::select("
            SELECT *
              FROM lealsis.out_tbl_rota_trajeto
             WHERE id_token = ?
             ORDER BY id_rota_trajeto ASC
             LIMIT ? OFFSET ?
        ", [$user->currentAccessToken()->token, $pFetch, $pOffset * $pFetch]);

        // Dados de retorno
        $count = count($trajetos);
        $inicio = $pOffset + 1;
        $final = $inicio + $count - 1;

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
                'trajetos' => $trajetos
            ]
        ], 200);
    }
}
