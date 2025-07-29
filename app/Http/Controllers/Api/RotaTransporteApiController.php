<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RotaTransporteApiController extends Controller
{
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
