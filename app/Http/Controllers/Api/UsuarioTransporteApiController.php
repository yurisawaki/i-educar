<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsuarioTransporteApiController extends Controller
{
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
