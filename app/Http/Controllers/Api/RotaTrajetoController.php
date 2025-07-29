<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RotaTrajeto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RotaTrajetoController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Usuário não autenticado'
            ], 401);
        }

        $userId = $user->id;
        $token = $user->currentAccessToken()->token;

        // Validação da paginação
        $validatedPagination = $request->validate([
            'pFetch' => 'required|integer|min:1',
            'pOffset' => 'required|integer|min:0'
        ]);

        $pFetch = $validatedPagination['pFetch'];
        $pOffset = $validatedPagination['pOffset'];

        // Validação dos dados dos trajetos
        $validated = $request->validate([
            'trajetos' => 'required|array|min:1',
            'trajetos.*.id_rota' => 'required|integer',
            'trajetos.*.id_ponto_1' => 'nullable|integer',
            'trajetos.*.id_ponto_2' => 'nullable|integer',
            'trajetos.*.dh_coordenada' => 'nullable|string',
            'trajetos.*.nu_latitude' => 'required|string',
            'trajetos.*.nu_longitude' => 'required|string',
        ]);

        $trajetos = $validated['trajetos'];

        // Limpa os dados anteriores se offset for 0
        if ($pOffset == 0) {
            DB::delete("
                DELETE FROM lealsis.in_tbl_rota_trajeto
                WHERE id_token = ?
            ", [$token]);
        }

        // Salva os novos trajetos na tabela intermediária
        foreach ($trajetos as $item) {
            RotaTrajeto::create([
                'id_token' => $token,
                'id_rota' => $item['id_rota'],
                'id_ponto_1' => $item['id_ponto_1'] ?? null,
                'id_ponto_2' => $item['id_ponto_2'] ?? null,
                'dh_sincronizacao' => now(),
                'dh_coordenada' => $item['dh_coordenada'] ?? null,
                'nu_latitude' => $item['nu_latitude'],
                'nu_longitude' => $item['nu_longitude'],
            ]);
        }

        // Se recebeu menos que o total por página, deleta da final
        if (count($trajetos) < $pFetch) {
            DB::delete("
                DELETE FROM lealsis.tbl_rota_trajeto
                WHERE id_rota IN (
                    SELECT id_rota
                    FROM lealsis.in_tbl_rota_trajeto
                    WHERE id_token = ?
                )
            ", [$token]);
        }

        // Insere os dados na tabela final
        DB::insert("
            INSERT INTO lealsis.tbl_rota_trajeto (
                id_rota, id_ponto_1, id_ponto_2, dh_sincronizacao, dh_coordenada, nu_latitude, nu_longitude
            )
            SELECT id_rota, id_ponto_1, id_ponto_2, NOW(), dh_coordenada, nu_latitude, nu_longitude
            FROM lealsis.in_tbl_rota_trajeto
            WHERE id_token = ?
        ", [$token]);

        // Limpa dados antigos
        DB::delete("DELETE FROM lealsis.in_tbl_rota_trajeto WHERE dh_sincronizacao < CURRENT_DATE - INTERVAL '1 day'");

        // Aplica paginação na leitura dos dados finais
        $resultados = DB::select("
            SELECT *
            FROM lealsis.tbl_rota_trajeto
            WHERE id_rota IN (
                SELECT DISTINCT id_rota
                FROM lealsis.in_tbl_rota_trajeto
                WHERE id_token = ?
            )
            ORDER BY dh_sincronizacao DESC
            LIMIT ?
            OFFSET ?
        ", [$token, $pFetch, $pOffset]);

        return response()->json([
            'status' => 'success',
            'usuario' => [
                'id' => $user->id,
            ],
            'result' => [
                'count' => count($resultados),
                'trajetos' => $resultados,
            ]
        ], 201);
    }
}
