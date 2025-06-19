<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FotoController extends Controller
{
    public function upload(Request $request)
    {
        // Validação
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048',
            'id_ponto' => 'required|integer'
        ]);

        // Armazena a imagem
        $path = $request->file('photo')->store('fotos', 'public');

        // Grava caminho da imagem no banco de dados
        DB::table('lealsis.tbl_ponto_imagem')->updateOrInsert(
            ['id_ponto' => $request->id_ponto], // se já existir, atualiza
            ['no_imagem' => 'fotos/' . basename($path)]
        );

        // Retorna o caminho completo (para uso no frontend)
        return response()->json([
            'success' => true,
            'path' => '/storage/' . $path
        ]);
    }
}
