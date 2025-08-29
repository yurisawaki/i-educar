<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RotaTrajeto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RotaTrajetoController extends Controller
{
    /**
     * @OA\Post(
     *     path="/rota-trajetos",
     *     summary="Armazena trajetos de rota com paginação",
     *     description="Recebe múltiplos trajetos, armazena em uma tabela intermediária e depois salva na tabela final. Autenticado via Sanctum.",
     *     tags={"Rotas"},
     *     security={{"sanctum":{}}},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"trajetos", "pFetch", "pOffset"},
     *             @OA\Property(
     *                 property="trajetos",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id_rota", type="integer", example=101),
     *                     @OA\Property(property="id_ponto_1", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="id_ponto_2", type="integer", nullable=true, example=2),
     *                     @OA\Property(property="dh_coordenada", type="string", nullable=true, example="2025-07-29 10:30:00"),
     *                     @OA\Property(property="nu_latitude", type="string", example="-1.455833"),
     *                     @OA\Property(property="nu_longitude", type="string", example="-48.503887")
     *                 )
     *             ),
     *             @OA\Property(property="pFetch", type="integer", example=10),
     *             @OA\Property(property="pOffset", type="integer", example=0)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dados armazenados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="usuario",
     *                 @OA\Property(property="id", type="integer", example=8629)
     *             ),
     *             @OA\Property(
     *                 property="result",
     *                 @OA\Property(property="count", type="integer", example=10),
     *                 @OA\Property(property="final", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Usuário não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="erro"),
     *             @OA\Property(property="mensagem", type="string", example="Usuário não autenticado")
     *         )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        // Verifica se o usuário está autenticado via Sanctum
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Usuário não autenticado'
            ], 401);
        }
        $userId = $user->id;
        $token = $user->currentAccessToken()->token;

        // Validação dos dados dos trajetos
        $validated = $request->validate([
            'trajetos' => 'required|array|min:0',
            'trajetos.*.id_rota' => 'required|integer',
            'trajetos.*.id_ponto_1' => 'nullable|integer',
            'trajetos.*.id_ponto_2' => 'nullable|integer',
            'trajetos.*.dh_coordenada' => 'nullable|string',
            'trajetos.*.nu_latitude' => 'required|string',
            'trajetos.*.nu_longitude' => 'required|string',
            'trajetos.*.no_imagem' => 'nullable|string',
        ]);
        $trajetos = $validated['trajetos'];

        // Validação da paginação
        $validatedPagination = $request->validate([
            'pFetch' => 'required|integer|min:1',
            'pOffset' => 'required|integer|min:0'
        ]);
        $pFetch = $validatedPagination['pFetch'];
        $pOffset = $validatedPagination['pOffset'];
        $vIsFinal = false;

        if ($pOffset == 0) {
            DB::delete("
                DELETE FROM lealsis.in_tbl_rota_trajeto
                 WHERE id_token = ?
                    OR dh_sincronizacao < CURRENT_DATE - INTERVAL '1 day'
            ", [$token]);
        }

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
                'no_imagem' => $item['no_imagem'] ?? null,
            ]);
        }

        if (count($trajetos) < $pFetch) {
            $vIsFinal = true;

            DB::delete("
                DELETE FROM lealsis.tbl_rota_trajeto
                 WHERE id_rota IN (SELECT id_rota
                                     FROM lealsis.in_tbl_rota_trajeto
                                    WHERE id_token = ?
                )
            ", [$token]);

            DB::insert("
                INSERT INTO lealsis.tbl_rota_trajeto (
                     id_rota
                    ,id_ponto_1
                    ,id_ponto_2
                    ,dh_sincronizacao
                    ,dh_coordenada
                    ,nu_latitude
                    ,nu_longitude
                    ,no_imagem
                )
                SELECT id_rota
                      ,id_ponto_1
                      ,id_ponto_2
                      ,NOW()
                      ,dh_coordenada
                      ,nu_latitude
                      ,nu_longitude
                      ,no_imagem
                  FROM lealsis.in_tbl_rota_trajeto
                 WHERE id_token = ?
            ", [$token]);
        }

        $count = ($pOffset * $pFetch) + count($trajetos);

        return response()->json([
            'status' => 'success',
            'usuario' => [
                'id' => $user->id,
            ],
            'result' => [
                'count' => $count,
                'final' => $vIsFinal,
            ]
        ], 200);
    }
}
