<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class InPontoTransporteApiController extends Controller
{



    public function sincronizar(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Usuário não autenticado'
            ], 401);
        }

        $request->validate([
            'pFetch' => 'required|integer|min:1',
            'pOffset' => 'required|integer|min:0',
            'pontos' => 'required|array|min:0',
            'pontos.*.id_ponto' => 'required',
            'pontos.*.no_ponto' => 'required|string',
            'pontos.*.nu_latitude' => 'required|string',
            'pontos.*.nu_longitude' => 'required|string',
        ]);

        $pCount = 100;
        $pFetch = $request['pFetch'];
        $pOffset = $request['pOffset'];
        $token = $user->currentAccessToken()->token;
        $pontos = $request->input('pontos');
        $sincronizados = count($pontos);
        $now = now();

        if ($pOffset == 0) {
            DB::delete("
                DELETE FROM lealsis.in_tbl_ponto_transporte
                WHERE id_token = ? OR dh_sincronizacao < CURRENT_DATE - INTERVAL '1 day'
            ", [$token]);

            foreach ($pontos as $ponto) {
                DB::table('lealsis.in_tbl_ponto_transporte')->updateOrInsert(
                    [
                        'id_ponto' => (string) $ponto['id_ponto'],
                        'id_token' => $token,
                    ],
                    [
                        'no_ponto' => $ponto['no_ponto'],
                        'nu_latitude' => $ponto['nu_latitude'],
                        'nu_longitude' => $ponto['nu_longitude'],
                        'cd_acao' => $ponto['cd_acao'],
                        'dh_sincronizacao' => $now,
                    ]
                );
            }

        }
        if ($pCount > $pFetch) {
            $v_tbl_ponto = DB::select("
            SELECT COALESCE(
                (SELECT cod_ponto_transporte_escolar + 1
                 FROM modules.ponto_transporte_escolar
                 ORDER BY cod_ponto_transporte_escolar DESC
                 LIMIT 1),
                1
            ) AS id_ponto
        ");
            $v_id_ponto = $v_tbl_ponto[0]->id_ponto;

            DB::insert("
                INSERT INTO modules.ponto_transporte_escolar (
                    cod_ponto_transporte_escolar, descricao, cep, idlog, idbai, numero, complemento, latitude, longitude
                )
                SELECT
                    ? + ROW_NUMBER() OVER(ORDER BY A.id_ponto ASC),
                    A.no_ponto,
                    0,
                    0,
                    0,
                    0,
                    '',
                    A.nu_latitude,
                    A.nu_longitude
                FROM lealsis.in_tbl_ponto_transporte A
                WHERE A.id_token = ?
                AND A.cd_acao = 1
            ", [$v_id_ponto, $token]);

            DB::update("
                UPDATE modules.ponto_transporte_escolar A
                   SET descricao = B.no_ponto,
                       latitude = B.nu_latitude,
                       longitude = B.nu_longitude
                  FROM lealsis.in_tbl_ponto_transporte B
                 WHERE B.id_ponto::varchar = A.cod_ponto_transporte_escolar::varchar
                   AND B.id_token = ?
                   AND B.cd_acao = 2
            ", [$token]);
        }

        return response()->json([
            'status' => 'ok',
            'mensagem' => "$sincronizados informações atualizadas com sucesso.",
        ], 200);


    }
}

