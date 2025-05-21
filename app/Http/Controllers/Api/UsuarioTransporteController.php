<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsuarioTransporteController extends Controller
{
    public function index()
    {
        $usuarios = DB::select("
            SELECT 
                T.cod_usuario,
                P.nome,
                F.matricula,
                F.senha
            FROM lealsis.tbl_usuario_transporte T
            INNER JOIN pmieducar.usuario U ON U.cod_usuario = T.cod_usuario
            INNER JOIN cadastro.pessoa P ON P.idpes = U.cod_usuario
            INNER JOIN portal.funcionario F ON F.ref_cod_pessoa_fj = P.idpes
            WHERE U.data_exclusao IS NULL
              AND U.ativo = 1
        ");

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'result' => $usuarios
        ], 200);
    }
}
