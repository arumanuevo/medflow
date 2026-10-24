<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIChatController extends Controller
{
    public function ask(Request $request)
    {
        // Proteccion Backend contra abuso (Solo Premium)
        $service = new \App\Services\Subscription\SubscriptionService(auth()->user());
        $userPlan = $service->getPlan()->getPlanKey();
        $isAdmin = method_exists(auth()->user(), 'hasRole') ? (auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin')) : false;

        if (strtolower($userPlan) !== 'premium' && strtolower(auth()->user()->subscription_plan ?? '') !== 'premium' && !$isAdmin) {
             return response()->json(['success' => false, 'answer' => 'Función Premium. Debes subir de plan en tu perfil para usar la Inteligencia Artificial.']);
        }

        $request->validate(['message' => 'required|string']);
        $userMessage = $request->input('message');

        try {
            $rootBrainPath = base_path('medflow_brain.md');
            $brainPath = storage_path('app/ai/medflow_brain.md');
            if (file_exists($rootBrainPath)) {
                $systemContent = file_get_contents($rootBrainPath);
            } elseif (file_exists($brainPath)) {
                $systemContent = file_get_contents($brainPath);
            } else {
                $systemContent = 'Eres Flowy, asistente automatizado. Debes responder brevemente.';
            }

            // Inyectar contexto en vivo del Calendario de Tomas y Tolerancias del usuario actual
            try {
                $user = auth()->user();
                $workspaceId = session('active_workspace', $user->id);
                $scheduleService = app(\App\Services\MeasurementScheduleService::class);
                $liveAlerts = $scheduleService->getActiveAlerts($user, $workspaceId);

                $counts = $liveAlerts['counts'] ?? [];
                $liveSummary = "\n\n---\n## CONTEXTO EN TIEMPO REAL DEL CALENDARIO DE TOMAS DEL USUARIO ACTUAL (" . now()->format('d/m/Y') . ")\n";
                $liveSummary .= "- Sensores completados en su ciclo actual: " . ($counts['completed'] ?? 0) . "\n";
                $liveSummary .= "- Sensores programados a futuro: " . ($counts['scheduled'] ?? 0) . "\n";
                $liveSummary .= "- Sensores en ventana de pre-aviso / hoy toca medir: " . ($counts['in_window'] ?? 0) . "\n";
                $liveSummary .= "- Sensores en plazo de tolerancia: " . ($counts['in_tolerance'] ?? 0) . "\n";
                $liveSummary .= "- Sensores fuera de tolerancia (vencidos): " . ($counts['overdue'] ?? 0) . "\n";

                if (!empty($liveAlerts['items'])) {
                    $liveSummary .= "- Detalle de grupos con avisos activos hoy:\n";
                    foreach (array_slice($liveAlerts['items'], 0, 10) as $item) {
                        $targetFormatted = \Carbon\Carbon::parse($item['target_date'])->format('d/m/Y');
                        $graceFormatted = \Carbon\Carbon::parse($item['grace_end'])->format('d/m/Y');
                        $liveSummary .= "  * Grupo **{$item['group_name']}**: estado **{$item['label']}** ({$item['pending_count']} pendientes de {$item['total_count']}), Fecha Meta: {$targetFormatted}, Fin de Tolerancia: {$graceFormatted}. ({$item['message']})\n";
                    }
                } else {
                    $liveSummary .= "- Actualmente el usuario no tiene tomas atrasadas ni alertas pendientes en ventana o tolerancia.\n";
                }

                // Cobertura geográfica actual en el Mapa Interactivo de Sensores (GIS)
                $totalWorkspaceSensors = \App\Models\Sensor::where('user_id', $workspaceId)->count();
                $locatedSensors = \App\Models\Sensor::where('user_id', $workspaceId)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->where('latitude', '!=', '')
                    ->where('longitude', '!=', '')
                    ->count();
                $unlocatedSensors = max(0, $totalWorkspaceSensors - $locatedSensors);

                $liveSummary .= "\n## CONTEXTO EN TIEMPO REAL DEL MAPA INTERACTIVO DE SENSORES (GIS)\n";
                $liveSummary .= "- Total de sensores en el workspace: {$totalWorkspaceSensors}\n";
                $liveSummary .= "- Sensores con coordenada ubicada en el mapa: {$locatedSensors}\n";
                $liveSummary .= "- Sensores pendientes de ubicar en el mapa (sin coordenada): {$unlocatedSensors}\n";

                $systemContent .= $liveSummary;
            } catch (\Throwable $scheduleEx) {
                // Continuar normalmente si falla la consulta en vivo
            }

            // SOLUCION CACHE: Si env() devuelve null por culpa del cache de Laravel, saltamos a leer el archivo .env directo
            $apiKey = env('DEEPSEEK_API_KEY');
            if (empty($apiKey)) {
                $envVars = @parse_ini_file(base_path('.env'));
                $apiKey = $envVars['DEEPSEEK_API_KEY'] ?? null;
            }

            if (empty($apiKey)) {
                // Devolvemos status 200 para que Javascript lo procese suavemente
                return response()->json(['success' => false, 'answer' => 'Error 01: No se pudo leer la API Key. Por favor limpia la caché de Laravel o verifica tu .env']);
            }

            $messages = [
                ['role' => 'system', 'content' => $systemContent],
            ];

            // Incluir historial de conversación reciente si fue enviado
            $rawHistory = $request->input('chat_history');
            if (!empty($rawHistory)) {
                $decodedHistory = json_decode($rawHistory, true);
                if (is_array($decodedHistory)) {
                    foreach (array_slice($decodedHistory, -6) as $turn) {
                        if (!empty($turn['content'])) {
                            $role = ($turn['role'] ?? '') === 'user' ? 'user' : 'assistant';
                            $messages[] = ['role' => $role, 'content' => (string) $turn['content']];
                        }
                    }
                }
            }

            $messages[] = ['role' => 'user', 'content' => $userMessage];

            // Para evitar problemas de SSL en Wiroos, usamos verify => false temporalmente
            $response = Http::withOptions(['verify' => false])
                ->withToken($apiKey)
                ->timeout(30)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-flash',
                    'messages' => $messages,
                    'temperature' => 0.1,
                ]);

            if (!$response->successful()) {
                $response = Http::withOptions(['verify' => false])
                    ->withToken($apiKey)
                    ->timeout(30)
                    ->post('https://api.deepseek.com/chat/completions', [
                        'model' => 'deepseek-chat',
                        'messages' => $messages,
                        'temperature' => 0.1,
                    ]);
            }

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
