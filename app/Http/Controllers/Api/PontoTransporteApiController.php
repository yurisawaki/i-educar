<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class PontoTransporteApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/pontos",
     *     summary="Lista pontos de transporte com paginação",
     *     description="Retorna pontos de transporte, com suporte a paginação. Autenticado via Sanctum.",
     *     tags={"Pontos"},
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
     *         description="Lista de pontos retornada com sucesso",
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
     *                     property="pontos",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id_ponto", type="integer", example=101),
     *                         @OA\Property(property="no_ponto", type="string", example="Ponto Central"),
     *                         @OA\Property(property="nu_latitude", type="string", example="-1.455833"),
     *                         @OA\Property(property="nu_longitude", type="string", example="-48.503887"),
     *                         @OA\Property(property="no_imagem", type="string", nullable=true, example="foto.png"),
     *                         @OA\Property(property="tamanho_imagem", type="integer", nullable=true, example=2048)
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
                DELETE FROM lealsis.out_tbl_ponto_transporte
                 WHERE id_token = ?
                    OR dh_sincronizacao < CURRENT_DATE - INTERVAL '1 day'
            ", [$user->currentAccessToken()->token]);

            // Gravar tabela OUT
            DB::insert("
                INSERT INTO lealsis.out_tbl_ponto_transporte (
                       id_token
                      ,dh_sincronizacao
                      ,id_ponto
                      ,no_ponto
                      ,nu_latitude
                      ,nu_longitude
                      )
                SELECT
                       ?
                      ,CURRENT_TIMESTAMP AT TIME ZONE 'America/Fortaleza'
                      ,P.cod_ponto_transporte_escolar
                      ,P.descricao
                      ,P.latitude
                      ,P.longitude
                  FROM modules.ponto_transporte_escolar P
                ", [$user->currentAccessToken()->token]);
        }

        // Consulta paginada
        $pontos = DB::select("
            SELECT P.*, PI.no_imagem, PI.tamanho_imagem
              FROM lealsis.out_tbl_ponto_transporte P
              LEFT JOIN lealsis.tbl_ponto_imagem PI ON PI.id_ponto = P.id_ponto
             WHERE P.id_token = ?
             ORDER BY P.no_ponto ASC
             LIMIT ? OFFSET ?
        ", [$user->currentAccessToken()->token, $pFetch, $pOffset * $pFetch]);

        // Quantitativo de registros
        $count = count($pontos);
        $inicio = $pOffset + 1;
        $final = $inicio + $count - 1;



        // Return
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
                'pontos' => $pontos
            ]
        ], 200);
    }
}
