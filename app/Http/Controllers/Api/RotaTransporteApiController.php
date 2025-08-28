<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RotaTransporteApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/rotas",
     *     summary="Lista rotas de transporte com paginação",
     *     description="Retorna as rotas de transporte escolar, incluindo destino, transportadora e tipo de rota. Autenticado via Sanctum.",
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
     *         description="Lista de rotas retornada com sucesso",
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
     *                     property="rotas",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id_rota", type="integer", example=101),
     *                         @OA\Property(property="nu_ano", type="integer", example=2025),
     *                         @OA\Property(property="no_rota", type="string", example="Rota Norte"),
     *                         @OA\Property(property="id_destino", type="integer", example=55),
     *                         @OA\Property(property="no_destino", type="string", example="Escola Municipal A"),
     *                         @OA\Property(property="ds_rota_tipo", type="string", example="RURAL"),
     *                         @OA\Property(property="is_transportadora", type="integer", example=1),
     *                         @OA\Property(property="no_transportadora", type="string", example="Transporte Silva"),
     *                         @OA\Property(property="is_terceirizado", type="boolean", example=true)
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
        $user = $request->user(); // Autenticado via Sanctum
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
                DELETE FROM lealsis.out_tbl_rota_transporte
                 WHERE id_token = ?
                    OR dh_sincronizacao < CURRENT_DATE - INTERVAL '1 day'
            ", [$user->currentAccessToken()->token]);

            // Gravar tabela OUT
            DB::insert("
            INSERT INTO lealsis.out_tbl_rota_transporte (
                   id_token
                  ,dh_sincronizacao
                  ,id_rota
                  ,nu_ano
                  ,no_rota
                  ,id_destino
                  ,no_destino
                  ,ds_rota_tipo
                  ,is_transportadora
                  ,no_transportadora
                  ,is_terceirizado
                  )
            SELECT
                   ?
                  ,NOW()
                  ,R.cod_rota_transporte_escolar
                  ,R.ano
                  ,R.descricao
                  ,R.ref_idpes_destino
                  ,P.nome
                  ,CASE WHEN R.tipo_rota = 'R' THEN 'RURAL' ELSE 'URBANA' END
                  ,R.ref_cod_empresa_transporte_escolar
                  ,PT.nome
                  ,R.tercerizado
                  FROM modules.rota_transporte_escolar R
             INNER JOIN cadastro.pessoa P ON P.idpes = R.ref_idpes_destino
             INNER JOIN modules.empresa_transporte_escolar T ON T.cod_empresa_transporte_escolar = R.ref_cod_empresa_transporte_escolar
             INNER JOIN cadastro.pessoa PT ON PT.idpes = T.ref_idpes
             ", [$user->currentAccessToken()->token]);
        }

        // Consulta paginada
        $rotas = DB::select("
            SELECT *
              FROM lealsis.out_tbl_rota_transporte
             WHERE id_token = ?
             ORDER BY no_rota ASC
             LIMIT ? OFFSET ?
        ", [$user->currentAccessToken()->token, $pFetch, $pOffset * $pFetch]);

        // Quantitativo de registros
        $count = count($rotas);
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
                'rotas' => $rotas
            ]
        ], 200);
    }
}
