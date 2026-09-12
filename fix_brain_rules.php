<?php

// Paths
$rootBrain = 'k:\desarrollo\medflow\medflow_brain.md';
$storageBrain = 'k:\desarrollo\medflow\storage\app\ai\medflow_brain.md';

if (file_exists($rootBrain)) {
    $content = file_get_contents($rootBrain);

    // Check to not duplicate
    if (strpos($content, 'REGLAS DE TRADUCCIÓN A LENGUAJE DE NEGOCIO') === false) {
        $injectRules = <<<EOT
- **REGLAS DE TRADUCCIÓN A LENGUAJE DE NEGOCIO (¡PROHIBIDO HABLAR COMO PROGRAMADOR!):**
  - **Tú cliente final es un administrador de consorcio, gerente, o inspector de calle.** Ellos NO saben programar.
  - Tienes estrictamente **PROHIBIDO** usar términos técnicos de desarrollo de software como: *Modelos, Controladores, JSON, casteos, base de datos, arrays, objetos, metadata, SQL, endpoints, vistas, Blade, Flutter o variables*.
  - DEBES traducir todo el conocimiento interno (código) a **acciones funcionales de la pantalla visual** ("plataforma", "sistema", "menú", "tablero", "botones").
  - *Ejemplo Malo:* "El modelo Sensor guarda eso en el campo metadata con un cast array JSON libre desde el Importador."
  - *Ejemplo Excelente:* "Al importar tu Excel desde la herramienta, el sistema guardará esas columnas nativamente y las mantendrá visibles como 'Datos Adicionales' en el tablero de cada lote."
EOT;
        // Inject right after "- **Estilo de respuesta:** ..." 
        // Note the encoding from terminal output above for Estilo de repuesta: "- **Estilo de respuesta:** Profesional..."

        $content = preg_replace(
            '/- \*\*Estilo de respuesta:\*\*[^\n]+/',
            "$0\n" . $injectRules,
            $content
        );

        file_put_contents($rootBrain, $content);
        file_put_contents($storageBrain, $content); // Sync it to the proper folder too
        echo "Rules injected successfully!\n";
    } else {
        echo "Rules already injected!\n";
    }
} else {
    echo "Root file not found.\n";
}
