<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use File; // Importar helper File
use Exception;

class FixController extends Controller
{
    public function fixLogPermission()
    {
        try {
            // Caminho do arquivo de log
            $logFile = storage_path('logs/laravel.log');
            // Caminho da pasta storage
            $storagePath = storage_path();

            // Verifica se o arquivo de log existe antes de tentar modificar as permissões
            if (File::exists($logFile)) {
                // Verifica se o arquivo de log é gravável
                if (!File::isWritable($logFile)) {
                    // Tenta modificar a permissão para permitir escrita no arquivo de log
                    exec('chmod 0666 ' . escapeshellarg($logFile)); // Permite leitura e escrita para o proprietário, grupo e outros
                }
            } else {
                throw new Exception("O arquivo de log não foi encontrado.");
            }

            // Verifica se a pasta de logs existe antes de tentar modificar as permissões
            if (File::exists($storagePath . '/logs')) {
                // Garante que a pasta logs tenha permissões adequadas
                exec('chmod -R 0777 ' . escapeshellarg($storagePath . '/logs')); // Permissões totais para todos (em ambiente de produção, use com cuidado)
            } else {
                throw new Exception("A pasta de logs não foi encontrada.");
            }

            // Retorna sucesso se as permissões foram corrigidas
            return response()->json([
                'success' => true,
                'message' => 'Permissões corrigidas com sucesso.'
            ]);
        } catch (Exception $e) {
            // Retorna erro em caso de falha
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
