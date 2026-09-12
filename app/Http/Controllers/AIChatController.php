<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIChatController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate(['message' => 'required|string']);
        $userMessage = $request->input('message');

        try {
            $brainPath = storage_path('app/ai/medflow_brain.md');
            $systemContent = file_exists($brainPath)
                ? file_get_contents($brainPath)
                : 'Eres Flowy, asistente automatizado. Debes responder brevemente.';

            // SOLUCION CACHE: Si env() devuelve null por culpa del cache de Laravel, saltamos a leer el archivo .env directo
            $apiKey = env('DEEPSEEK_API_KEY');
            if (empty($apiKey)) {
                $envVars = parse_ini_file(base_path('.env'));
                $apiKey = $envVars['DEEPSEEK_API_KEY'] ?? null;
            }

            if (empty($apiKey)) {
                // Devolvemos status 200 para que Javascript lo procese suavemente
                return response()->json(['success' => false, 'answer' => 'Error 01: No se pudo leer la API Key. Por favor limpia la caché de Laravel o verifica tu .env']);
            }

            // Para evitar problemas de SSL en Wiroos, usamos verify => false temporalmente
            $response = Http::withOptions(['verify' => false])
                ->withToken($apiKey)
                ->timeout(30)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-flash',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemContent],
                        ['role' => 'user', 'content' => $userMessage]
                    ],
                    'temperature' => 0.1,
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'answer' => $response->json('choices.0.message.content')
                ]);
            }

            // Si hay error en DeepSeek (ej: saldo agotado, token mal), mostramos que paso:
            return response()->json(['success' => false, 'answer' => 'Error de proveedor IA: ' . $response->body()]);

        } catch (\Exception $e) {
            Log::error('DeepSeek Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'answer' => 'Excepción del Servidor: ' . $e->getMessage()]);
        }
    }
}