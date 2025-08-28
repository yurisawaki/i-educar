<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class ItinerarioApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/rota_ponto",
     *     summary="Lista itinerários de transporte com paginação",
     *     description="Retorna itinerários do transporte escolar, com suporte a paginação. Autenticado via Sanctum.",
     *     tags={"Itinerário"},
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
     *         description="Lista de itinerários retornada com sucesso",
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
     *                     property="itinerarios",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id_itinerario", type="integer", example=123),
     *                         @OA\Property(property="id_rota", type="integer", example=45),
     *                         @OA\Property(property="id_ponto", type="integer", example=67),
     *                         @OA\Property(property="nu_sequencia", type="integer", example=1),
     *                         @OA\Property(property="hr_ponto", type="string", example="08:00"),
     *                         @OA\Property(property="ds_rota_tipo", type="string", example="IDA")
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



        // Verifica se o usuário está autenticado via Sanctum
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Usuário não autenticado'
            ], 401);
        }
        $userId = $user->id;


        // Validação dos parâmetros obrigatórios da requisição
        $validated = $request->validate([
            'pFetch' => 'required|integer|min:1',
            'pOffset' => 'required|integer|min:0'
        ]);
        $pFetch = $validated['pFetch'];
        $pOffset = $validated['pOffset'];


        // Quando o offset é zero (primeira página):
        if ($pOffset == 0) {

            // - Remove registros antigos ou do mesmo token (limpeza)
            DB::delete("
            DELETE FROM lealsis.out_tbl_itinerario_transporte
                 WHERE id_token = ?
                    OR dh_sincronizacao < CURRENT_DATE - INTERVAL '1 day'
                    ", [$user->currentAccessToken()->token]);

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

        // Busca os registros da tabela temporária de saída com paginação
        $itinerarios = DB::select("
            SELECT *
            FROM lealsis.out_tbl_itinerario_transporte
            WHERE id_token = ?
            ORDER BY nu_sequencia ASC
            LIMIT ? OFFSET ?
        ", [$user->currentAccessToken()->token, $pFetch, $pOffset * $pFetch]);


        // Calcula o total de registros retornados e os índices da página
        $count = count($itinerarios);
        $inicio = $pOffset + 1;
        $final = $inicio + $count - 1;


        // Retorna a resposta em formato JSON
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
