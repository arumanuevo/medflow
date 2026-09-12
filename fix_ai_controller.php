<?php
// fix_ai_controller.php

$controllerPath = 'k:\desarrollo\medflow\app\Http\Controllers\AIChatController.php';
$controllerContent = <<<EOT
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIChatController extends Controller
{
    public function ask(Request \$request)
    {
        \$request->validate(['message' => 'required|string']);
        \$userMessage = \$request->input('message');

        try {
            \$brainPath = storage_path('app/ai/medflow_brain.md');
            \$systemContent = file_exists(\$brainPath) 
                             ? file_get_contents(\$brainPath) 
                             : 'Eres Flowy, asistente automatizado. Debes responder brevemente.';

            // SOLUCION CACHE: Si env() devuelve null por culpa del cache de Laravel, saltamos a leer el archivo .env directo
            \$apiKey = env('DEEPSEEK_API_KEY');
            if (empty(\$apiKey)) {
                \$envVars = parse_ini_file(base_path('.env'));
                \$apiKey = \$envVars['DEEPSEEK_API_KEY'] ?? null;
            }

            if (empty(\$apiKey)) {
                // Devolvemos status 200 para que Javascript lo procese suavemente
                return response()->json(['success' => false, 'answer' => 'Error 01: No se pudo leer la API Key. Por favor limpia la caché de Laravel o verifica tu .env']);
            }

            // Para evitar problemas de SSL en Wiroos, usamos verify => false temporalmente
            \$response = Http::withOptions(['verify' => false])
                ->withToken(\$apiKey)
                ->timeout(30)
                ->post('https://api.api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-flash',
                    'messages' => [
                        ['role' => 'system', 'content' => \$systemContent],
                        ['role' => 'user', 'content' => \$userMessage]
                    ],
                    'temperature' => 0.1,
                ]);

            if (\$response->successful()) {
                return response()->json([
                    'success' => true,
                    'answer' => \$response->json('choices.0.message.content')
                ]);
            }

            // Si hay error en DeepSeek (ej: saldo agotado, token mal), mostramos que paso:
            return response()->json(['success' => false, 'answer' => 'Error de proveedor IA: ' . \$response->body()]);

        } catch (\Exception \$e) {
            Log::error('DeepSeek Error: ' . \$e->getMessage());
            return response()->json(['success' => false, 'answer' => 'Excepción del Servidor: ' . \$e->getMessage()]);
        }
    }
}
EOT;
file_put_contents($controllerPath, ltrim($controllerContent));

// Actualizar el JS para que no pise los errores
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$layoutContent = file_get_contents($layoutPath);
// Replace the old AJAX error handling to be smarter
$oldAjax = <<<EOT
            success: function(response) {
                document.getElementById(spinnerId).remove();
                let formattedHtml = response.answer.replace(/\*\*(.*?)\*\*/g, '<strong>\$1</strong>');
                chatBox.innerHTML += `
                    <div class="mb-3 text-start">
                        <span class="badge bg-white text-dark shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0; max-width: 90%; text-align: left !important; white-space: pre-wrap; font-weight: normal; line-height: 1.4;">\${formattedHtml}</span>
                    </div>
                `;
                chatBox.scrollTop = chatBox.scrollHeight;
            },
            error: function() {
                document.getElementById(spinnerId).remove();
                chatBox.innerHTML += `
                    <div class="mb-3 text-start">
                        <span class="badge bg-danger text-white shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0; font-weight: normal;">Error de conexión. Intente en unos minutos.</span>
                    </div>
                `;
            }
EOT;

$newAjax = <<<EOT
            success: function(response) {
                document.getElementById(spinnerId).remove();
                if(response.success === false) {
                   chatBox.innerHTML += `
                        <div class="mb-3 text-start">
                            <span class="badge bg-warning text-dark shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0; max-width: 90%; text-align: left !important; white-space: pre-wrap; font-weight: normal; line-height: 1.4;">\${response.answer}</span>
                        </div>
                    `;
                } else {
                    let formattedHtml = response.answer.replace(/\*\*(.*?)\*\*/g, '<strong>\$1</strong>');
                    chatBox.innerHTML += `
                        <div class="mb-3 text-start">
                            <span class="badge bg-white text-dark shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0; max-width: 90%; text-align: left !important; white-space: pre-wrap; font-weight: normal; line-height: 1.4;">\${formattedHtml}</span>
                        </div>
                    `;
                }
                chatBox.scrollTop = chatBox.scrollHeight;
            },
            error: function(xhr) {
                document.getElementById(spinnerId).remove();
                let serverError = xhr.responseJSON && xhr.responseJSON.answer ? xhr.responseJSON.answer : 'Error de respuesta del servidor.';
                chatBox.innerHTML += `
                    <div class="mb-3 text-start">
                        <span class="badge bg-danger text-white shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0; max-width: 90%; white-space: pre-wrap; font-weight: normal; line-height: 1.4;">Error 500: \${serverError}</span>
                    </div>
                `;
                chatBox.scrollTop = chatBox.scrollHeight;
            }
EOT;

$layoutContent = str_replace($oldAjax, $newAjax, $layoutContent);
file_put_contents($layoutPath, $layoutContent);
echo "Controlador y JS actualizados con Anti-Caché y SSL.\n";
