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

        // Pega o arquivo e obtém o tamanho
        $file = $request->file('photo');
        $path = $file->store('imagem/transporte/ponto', 'public');

        $tamanho = $file->getSize();

        // Insere ou atualiza no banco
        DB::table('lealsis.tbl_ponto_imagem')->updateOrInsert(
            ['id_ponto' => $request->id_ponto],
            [
                'no_imagem' => $path,
                'tamanho_imagem' => $tamanho,
                'created_at' => now()
            ]
        );

        return response()->json([
            'success' => true,
            'path' => '/storage/' . $path,
            'size' => $tamanho . ' bytes'
        ]);
    }
}
