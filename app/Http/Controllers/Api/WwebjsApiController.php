<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wwebjs;

class WwebjsApiController extends Controller
{
    public function Wwebjs(Request $request)
    {
        $request->validate([
            'body' => 'required|string',
            'gn_json' => 'required',
        ]);

        $payload = $request->all();

        // salva no banco usando apenas os campos existentes
        $registro = Wwebjs::create([
            'gn_msg' => $request->body,
            'gn_json' => $payload,
            'dh_msg' => now(),
        ]);

        // verifica se há imagem no JSON
        $imagem_base64 = null;
        $media_type = null;
        $jsonData = is_array($payload['gn_json']) ? $payload['gn_json'] : json_decode($payload['gn_json'], true);

        if (
            !empty($jsonData['mensagem_completa']['type'])
            && $jsonData['mensagem_completa']['type'] === 'image'
            && !empty($jsonData['mensagem_completa']['rawData']['body'])
        ) {
            $imagem_base64 = $jsonData['mensagem_completa']['rawData']['body'];
            $media_type = $jsonData['mensagem_completa']['mimetype'] ?? 'image/jpeg';
        }

        return response()->json([
            'status' => 'success',
            'id_msg' => $registro->id_msg,
            'mensagem' => $registro->gn_msg,
            'imagem_base64' => $imagem_base64,
            'media_type' => $media_type,
        ], 200);
    }
}
