<?php
// fix_ai_integration.php

// 1. Crear el directorio y el archivo de cerebro base
$aiDir = 'k:\desarrollo\medflow\storage\app\ai';
if (!is_dir($aiDir)) {
    mkdir($aiDir, 0777, true);
}
$brainPath = $aiDir . '\medflow_brain.md';
if (!file_exists($brainPath)) {
    file_put_contents($brainPath, "# ROL\nEres Flowy, el asistente oficial de soporte experto de MedFlow.\n\n(Nota: Reemplaza o expande este archivo con el generado por Vibe posteriormente).");
}

// 2. Crear el Controlador (AIChatController)
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

            \$response = Http::withToken(env('DEEPSEEK_API_KEY'))
                ->timeout(30)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-flash', // El modelo rapido y economico
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

            return response()->json(['success' => false, 'answer' => 'Error de conexión con la IA de DeepSeek.'], 500);

        } catch (\Exception \$e) {
            Log::error('DeepSeek Error: ' . \$e->getMessage());
            return response()->json(['success' => false, 'answer' => 'El agente está inactivo en este momento. Intenta más tarde.'], 500);
        }
    }
}
EOT;
file_put_contents($controllerPath, ltrim($controllerContent));

// 3. Agregar la ruta a routes/web.php
$routesPath = 'k:\desarrollo\medflow\routes\web.php';
$routesContent = file_get_contents($routesPath);
if (strpos($routesContent, 'ai.ask') === false) {
    $routesContent .= "\n\n// AI Assistant Route\nRoute::post('/api/soporte/ask', [\App\Http\Controllers\AIChatController::class, 'ask'])->middleware('auth')->name('ai.ask');\n";
    file_put_contents($routesPath, $routesContent);
}

// 4. Inyectar HTML/JS en modern.blade.php
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$layoutContent = file_get_contents($layoutPath);
if (strpos($layoutContent, 'id="btnFlowyAI"') === false) {
    $chatWidget = <<<EOT
<!-- Botón Flotante Flowy AI -->
<button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
        id="btnFlowyAI" 
        style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 1050; border-radius: 50% !important;">
    <i class="bi bi-robot fs-3 text-white"></i>
</button>

<!-- Caja de Chat Oculta -->
<div class="card shadow-lg d-none" id="chatFlowyContainer" 
     style="position: fixed; bottom: 100px; right: 30px; width: 350px; z-index: 1050; border-radius: 15px; border: 1px solid #e0e0e0; overflow: hidden;">
    
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-robot me-2"></i> Flowy (Beta IA)</h6>
        <button type="button" class="btn-close btn-close-white" id="closeFlowyChat" style="font-size: 0.8rem;"></button>
    </div>

    <div class="card-body bg-light" id="flowyChatBox" style="height: 350px; overflow-y: auto; font-size: 0.9rem;">
        <div class="mb-3 text-start">
            <span class="badge bg-white text-dark shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0;">
                ¡Hola! Soy tu asistente inteligente MedFlow. ¿En qué flujo u operación tienes dudas hoy?
            </span>
        </div>
    </div>

    <div class="card-footer bg-white border-top-0 p-2">
        <div class="input-group">
            <input type="text" id="flowyUserInput" class="form-control rounded-pill border-1 bg-light ps-3 me-2" placeholder="Escribe tu consulta..." aria-label="Escribe tu consulta...">
            <button class="btn btn-primary rounded-circle d-flex align-items-center px-3" id="btnSendFlowy" style="height: 40px; width: 40px !important;">
                <i class="bi bi-send-fill" style="margin-left: -2px;"></i>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnOpen = document.getElementById('btnFlowyAI');
    const btnClose = document.getElementById('closeFlowyChat');
    const chatContainer = document.getElementById('chatFlowyContainer');
    const chatBox = document.getElementById('flowyChatBox');
    const userInput = document.getElementById('flowyUserInput');
    const btnSend = document.getElementById('btnSendFlowy');

    if(btnOpen) btnOpen.addEventListener('click', () => chatContainer.classList.toggle('d-none'));
    if(btnClose) btnClose.addEventListener('click', () => chatContainer.classList.add('d-none'));

    function askQuestion() {
        const text = userInput.value.trim();
        if (!text) return;

        chatBox.innerHTML += `
            <div class="mb-3 text-end">
                <span class="badge bg-primary text-white shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 0 15px; text-align: left !important; font-weight: normal;">
                    \${text}
                </span>
            </div>
        `;
        userInput.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        const spinnerId = 'spinner-' + Date.now();
        chatBox.innerHTML += `
            <div class="mb-3 text-start" id="\${spinnerId}">
                <span class="badge bg-white text-primary shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0; font-weight: normal;">
                    <i class="bi bi-chat-dots-fill heartbeat-anim"></i> Pensando...
                </span>
            </div>
        `;
        chatBox.scrollTop = chatBox.scrollHeight;

        $.ajax({
            url: "/api/soporte/ask",
            type: "POST",
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                message: text
            },
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
        });
    }

    if(btnSend) btnSend.addEventListener('click', askQuestion);
    if(userInput) userInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') askQuestion(); });

    const style = document.createElement('style');
    style.innerHTML = `@keyframes heartbeat { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } } .heartbeat-anim { display: inline-block; animation: heartbeat 1.5s infinite; }`;
    document.head.appendChild(style);
});
</script>
EOT;

    // Inyectamos antes del </body>
    $layoutContent = str_replace('</body>', $chatWidget . "\n</body>", $layoutContent);
    file_put_contents($layoutPath, $layoutContent);
}

// 5. Agregar el script al .env de forma segura
$envPath = 'k:\desarrollo\medflow\.env';
$envContent = file_get_contents($envPath);
if (strpos($envContent, 'DEEPSEEK_API_KEY') === false) {
    file_put_contents($envPath, "\n# Configuración Agente I.A (Soporte)\nDEEPSEEK_API_KEY=sk-e2c1e78a645c419ca4557abab4a3d4d1\n", FILE_APPEND);
}

echo "AI Agent Integrado Correctamente en Código!";
