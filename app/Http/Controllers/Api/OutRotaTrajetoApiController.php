<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OutRotaTrajetoApiController extends Controller
{   /**
    * @OA\Get(
    *     path="/out-rota-trajetos",
    *     summary="Lista trajetos de rota com paginação",
    *     description="Retorna trajetos de rota disponíveis no sistema com suporte a paginação. Autenticação via Sanctum.",
    *     tags={"Rotas"},
    *     security={{"sanctum":{}}},
    *
    *     @OA\Parameter(
    *         name="pFetch",
    *         in="query",
    *         required=true,
    *         description="Quantidade de registros por página",
    *         @OA\Schema(type="integer", example=10)
    *     ),
    *     @OA\Parameter(
    *         name="pOffset",
    *         in="query",
    *         required=true,
    *         description="Número da página (offset)",
    *         @OA\Schema(type="integer", example=0)
    *     ),
    *
    *     @OA\Response(
    *         response=200,
    *         description="Lista de trajetos retornada com sucesso",
    *         @OA\JsonContent(
    *             @OA\Property(property="status", type="string", example="success"),
    *             @OA\Property(
    *                 property="usuario",
    *                 type="object",
    *                 @OA\Property(property="id", type="integer", example=8629)
    *             ),
    *             @OA\Property(
    *                 property="contadores",
    *                 type="object",
    *                 @OA\Property(property="count", type="integer", example=10),
    *                 @OA\Property(property="pFetch", type="integer", example=10),
    *                 @OA\Property(property="pOffset", type="integer", example=0)
    *             ),
    *             @OA\Property(
    *                 property="result",
    *                 type="object",
    *                 @OA\Property(
    *                     property="trajetos",
    *                     type="array",
    *                     @OA\Items(
    *                         @OA\Property(property="id_rota_trajeto", type="integer", example=1),
    *                         @OA\Property(property="id_rota", type="integer", example=101),
    *                         @OA\Property(property="id_ponto_1", type="integer", nullable=true, example=5),
    *                         @OA\Property(property="id_ponto_2", type="integer", nullable=true, example=6),
    *                         @OA\Property(property="dh_coordenada", type="string", example="2025-07-30 14:00:00"),
    *                         @OA\Property(property="nu_latitude", type="string", example="-1.455833"),
    *                         @OA\Property(property="nu_longitude", type="string", example="-48.503887")
    *                     )
    *                 )
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
    public function index(Request $request)
    {
        // Autenticação do usuário
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

        // Verificar se o offset é 0
        if ($pOffset == 0) {
            // Limpa os dados antigos (token atual ou com mais de 1 dia)

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

        // Consulta paginada
        $trajetos = DB::select("
            SELECT *
              FROM lealsis.out_tbl_rota_trajeto
             WHERE id_token = ?
             ORDER BY id_rota_trajeto ASC
             LIMIT ? OFFSET ?
        ", [$user->currentAccessToken()->token, $pFetch, $pOffset * $pFetch]);

        // Quantitativo de registros
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
