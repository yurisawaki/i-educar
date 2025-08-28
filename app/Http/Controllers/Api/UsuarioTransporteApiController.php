<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsuarioTransporteApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/usuarios",
     *     summary="Lista usuários de transporte com paginação",
     *     description="Retorna a lista de usuários do transporte escolar, incluindo nome, matrícula e senha. Autenticado via Sanctum.",
     *     tags={"Usuários de Transporte"},
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
     *         description="Lista de usuários retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="usuario",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=8629)
     *             ),
     *             @OA\Property(
     *                 property="parametros_recebidos",
     *                 type="object",
     *                 @OA\Property(property="pFetch", type="integer", example=10),
     *                 @OA\Property(property="pOffset", type="integer", example=0)
     *             ),
     *             @OA\Property(
     *                 property="result",
     *                 type="object",
     *                 @OA\Property(property="count", type="integer", example=10),
     *                 @OA\Property(
     *                     property="usuarios",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="cod_usuario", type="integer", example=1001),
     *                         @OA\Property(property="nome", type="string", example="João Silva"),
     *                         @OA\Property(property="matricula", type="string", example="MAT123"),
     *                         @OA\Property(property="senha", type="string", example="hash_senha")
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
        $user = $request->user(); // Autenticado via Sanctum

        if (!$user) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Usuário não autenticado'
            ], 401);
        }

        $userId = $user->id;

        $validated = $request->validate([
            'pFetch' => 'required|integer|min:1',
            'pOffset' => 'required|integer|min:0'
        ]);

        $pFetch = $validated['pFetch'];
        $pOffset = $validated['pOffset'];

        if ($pOffset == 0) {
            // Remove todos os dados antigos do cache para este token
            DB::delete("
        DELETE FROM lealsis.out_tbl_usuario_transporte
        WHERE id_token = ?
    ", [$user->currentAccessToken()->token]);

            // Insere os dados atualizados, ignorando duplicatas
            DB::insert("
    INSERT INTO lealsis.out_tbl_usuario_transporte (
        id_token,
        dh_sincronizacao,
        cod_usuario,
        nome,
        matricula,
        senha
    )
    SELECT
        ?,
        NOW(),
        T.cod_usuario,
        P.nome,
        F.matricula,
        F.senha
    FROM lealsis.tbl_usuario_transporte T
    INNER JOIN pmieducar.usuario U ON U.cod_usuario = T.cod_usuario
    INNER JOIN cadastro.pessoa P ON P.idpes = U.cod_usuario
    INNER JOIN portal.funcionario F ON F.ref_cod_pessoa_fj = P.idpes
    WHERE U.data_exclusao IS NULL AND U.ativo = 1
", [$user->currentAccessToken()->token]);

        }

        // Consulta com paginação baseada no token do Sanctum
        $usuarios = DB::select("
            SELECT *
            FROM lealsis.out_tbl_usuario_transporte
            WHERE id_token = ?
            ORDER BY nome ASC
            LIMIT ? OFFSET ?
        ", [$user->currentAccessToken()->token, $pFetch, $pOffset * $pFetch]);

        $count = count($usuarios);
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
                'usuarios' => $usuarios
            ]
        ]);
    }
}
