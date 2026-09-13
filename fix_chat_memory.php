<?php

// 1. Modificar el Controlador para que acepte Historial
$controllerPath = 'k:\desarrollo\medflow\app\Http\Controllers\AIChatController.php';
$contentC = file_get_contents($controllerPath);

$oldMessages = <<<EOT
                    'messages' => [
                        ['role' => 'system', 'content' => \$systemContent],
                        ['role' => 'user', 'content' => \$userMessage]
                    ],
EOT;

$newMessages = <<<EOT
EOT;

// I will just rewrite the whole ask function to be safe with string replacement
$oldFunction = <<<EOT
    public function ask(Request \$request)
    {
        \$request->validate(['message' => 'required|string']);
        \$userMessage = \$request->input('message');

        try {
            // Buscar en carpeta storage o en la raiz directamente
            \$brainPathStorage = storage_path('app/ai/medflow_brain.md');
            \$brainPathRoot = base_path('medflow_brain.md');
            
            if (file_exists(\$brainPathStorage)) {
                 \$systemContent = file_get_contents(\$brainPathStorage);
            } else if (file_exists(\$brainPathRoot)) {
                 \$systemContent = file_get_contents(\$brainPathRoot);
            } else {
                 \$systemContent = 'Eres Flowy, asistente automatizado. Debes responder brevemente. Pero dile al usuario que hubo un error: No encontraste el archivo medflow_brain.md en el servidor.';
            }

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
                ->post('https://api.deepseek.com/chat/completions', [
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
EOT;

$newFunction = <<<EOT
    public function ask(Request \$request)
    {
        \$request->validate(['message' => 'required|string']);
        \$userMessage = \$request->input('message');
        
        // Atrapamos el historial enviado desde Javascript
        \$historyRaw = \$request->input('chat_history');
        \$chatHistory = \$historyRaw ? json_decode(\$historyRaw, true) : [];

        try {
            \$brainPathStorage = storage_path('app/ai/medflow_brain.md');
            \$brainPathRoot = base_path('medflow_brain.md');
            
            if (file_exists(\$brainPathStorage)) {
                 \$systemContent = file_get_contents(\$brainPathStorage);
            } else if (file_exists(\$brainPathRoot)) {
                 \$systemContent = file_get_contents(\$brainPathRoot);
            } else {
                 \$systemContent = 'Hubo un error: No se encontro medflow_brain.md';
            }

            \$apiKey = env('DEEPSEEK_API_KEY') ?: (parse_ini_file(base_path('.env'))['DEEPSEEK_API_KEY'] ?? null);
            if (empty(\$apiKey)) {
                return response()->json(['success' => false, 'answer' => 'Error 01: No se pudo leer la API Key.']);
            }

            // Ensamblamos el paquete: 1. System, 2. Historia pasada, 3. Mensaje actual
            \$messagesArray = [
                ['role' => 'system', 'content' => \$systemContent]
            ];
            
            // Limitamos a los ultimos 6 mensajes por seguridad del lado backend tambien
            \$chatHistory = array_slice(\$chatHistory, -6);
            foreach (\$chatHistory as \$pastMsg) {
                if(isset(\$pastMsg['role']) && isset(\$pastMsg['content'])) {
                     \$messagesArray[] = [
                         'role' => \$pastMsg['role'] === 'bot' ? 'assistant' : 'user', 
                         'content' => \$pastMsg['content']
                     ];
                }
            }
            
            // Agregamos el mensaje nuevo final
            \$messagesArray[] = ['role' => 'user', 'content' => \$userMessage];

            \$response = Http::withOptions(['verify' => false])
                ->withToken(\$apiKey)
                ->timeout(30)
                ->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-flash',
                    'messages' => \$messagesArray,
                    'temperature' => 0.1,
                ]);

            if (\$response->successful()) {
                return response()->json([
                    'success' => true,
                    'answer' => \$response->json('choices.0.message.content')
                ]);
            }

            return response()->json(['success' => false, 'answer' => 'Error de proveedor IA: ' . \$response->body()]);

        } catch (\Exception \$e) {
            Log::error('DeepSeek Error: ' . \$e->getMessage());
            return response()->json(['success' => false, 'answer' => 'Excepción del Servidor: ' . \$e->getMessage()]);
        }
    }
EOT;

$contentC = str_replace($oldFunction, $newFunction, $contentC);
file_put_contents($controllerPath, $contentC);


// 2. Modificar el layout Javascript para manejar la memoria temporal
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$contentL = file_get_contents($layoutPath);

// Encontramos el inicio del envio de datos por Ajax
$oldJSData = <<<EOT
        $.ajax({
            url: "/api/soporte/ask",
            type: "POST",
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                message: text
            },
EOT;

// Y el onDOMContentLoaded donde agregaremos la variable global
$oldInit = "document.addEventListener('DOMContentLoaded', function() {";
$newInit = "document.addEventListener('DOMContentLoaded', function() {\n    // Memoria a corto plazo del chat (Max 6 interacciones)\n    let flowyMemory = [];";
$contentL = str_replace($oldInit, $newInit, $contentL);

$newJSData = <<<EOT
        // Antes de enviar, push history local
        let historyJSON = JSON.stringify(flowyMemory);

        $.ajax({
            url: "/api/soporte/ask",
            type: "POST",
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                message: text,
                chat_history: historyJSON
            },
EOT;
$contentL = str_replace($oldJSData, $newJSData, $contentL);


// Necesitamos meter el mensaje viejo a la memoria
$oldJSSuccessEnd = <<<EOT
                    let formattedHtml = parseMD(response.answer);
                    chatBox.innerHTML += `
                        <div class="mb-3 text-start">
                            <div class="bg-white text-dark shadow-sm px-3 py-3" style="border-radius: 15px 15px 15px 0; max-width: 95%; text-align: left !important; font-weight: normal; line-height: 1.5; font-size: 0.8rem; display: inline-block;">\${formattedHtml}</div>
                        </div>
                    `;
                }
                chatBox.scrollTop = chatBox.scrollHeight;
            },
EOT;

$newJSSuccessEnd = <<<EOT
                    let formattedHtml = parseMD(response.answer);
                    chatBox.innerHTML += `
                        <div class="mb-3 text-start">
                            <div class="bg-white text-dark shadow-sm px-3 py-3" style="border-radius: 15px 15px 15px 0; max-width: 95%; text-align: left !important; font-weight: normal; line-height: 1.5; font-size: 0.8rem; display: inline-block;">\${formattedHtml}</div>
                        </div>
                    `;
                    // Almacenamos este exito en la memoria temporal
                    flowyMemory.push({role: 'user', content: text});
                    flowyMemory.push({role: 'bot', content: response.answer});
                    if(flowyMemory.length > 6) {
                        flowyMemory.splice(0, 2); // Borrar el par mas antiguo si superan 3 idas y vueltas
                    }
                }
                chatBox.scrollTop = chatBox.scrollHeight;
            },
EOT;
$contentL = str_replace($oldJSSuccessEnd, $newJSSuccessEnd, $contentL);

file_put_contents($layoutPath, $contentL);
echo "Memory implemented!\n";
