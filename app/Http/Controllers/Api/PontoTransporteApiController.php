<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//essa APi Deu certo a logica de apagar os dados antigos e inserir os novos
//ela limpa os dados antigos do token atual e insere os novos dados
//também limpa os dados antigos de qualquer token com mais de 1 dia
//e insere os dados atualizados
//ela retorna os pontos de transporte com paginação baseada no token
class PontoTransporteApiController extends Controller
{
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
