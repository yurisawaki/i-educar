<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParametroApiController extends Controller
{
    public function getParametros(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Usuário não autenticado'
            ], 401);
        }
        $userId = $user->id;

        $parametros = DB::select("
        SELECT tempo, distancia  
        FROM tbl_configuracao
    ");


        $parametro = $parametros[0] ?? null;

        return response()->json([
            'status' => 'success',
            'usuario' => [
                'id' => $userId
            ],
            'GPS' => [
                'Qt_gps_distancia' => $parametro->tempo ?? null,
                'Qt_gps_tempo' => $parametro->distancia ?? null,
            ],
            'FTP' => [
                'No_ftp_host' => '192.168.100.13',
                'No_ftp_usuario' => 'ieducar',
                'Gn_ftp_senha' => '1PWnS6B9',
                'Nu_ftp_porta' => '21',
                'Cd_ftp_passive' => '1',
            ]
        ], 200);
    }

}
