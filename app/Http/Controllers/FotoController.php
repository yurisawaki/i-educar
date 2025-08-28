<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class FotoController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048',
            'id_ponto' => 'required|integer'
        ]);

        $file = $request->file('photo');
        if (!$file || !$file->isValid()) {
            return response()->json(['error' => 'Arquivo inválido ou não enviado'], 400);
        }

        $nomeArquivo = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $salvou = $file->storeAs('imagem', $nomeArquivo, 'public');

        if (!$salvou) {
            return response()->json(['error' => 'Falha ao salvar arquivo'], 500);
        }

        DB::table('lealsis.tbl_ponto_imagem')->updateOrInsert(
            ['id_ponto' => $request->id_ponto],
            [
                'no_imagem' => $salvou,
                'tamanho_imagem' => $file->getSize(),
                'created_at' => now()
            ]
        );

        $publicUrl = asset('storage/' . $salvou);

        return response()->json([
            'success' => true,
            'path' => $salvou,
            'url' => $publicUrl,
            'size' => $file->getSize() . ' bytes'
        ]);
    }

}
