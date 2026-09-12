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

            $response = Http::withToken(env('DEEPSEEK_API_KEY'))
                ->timeout(30)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-flash', // El modelo rapido y economico
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

            return response()->json(['success' => false, 'answer' => 'Error de conexión con la IA de DeepSeek.'], 500);

        } catch (\Exception $e) {
            Log::error('DeepSeek Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'answer' => 'El agente está inactivo en este momento. Intenta más tarde.'], 500);
        }
    }
}