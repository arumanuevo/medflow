<?php
$content = file_get_contents('k:\desarrollo\medflow\resources\views\help\index.blade.php');

$replacements = [
    // 1. Plantillas
    "Recomendamos empezar creando una 'Plantilla'. Una Plantilla dicta los campos estáticos o límites de termostato que van a compartir muchos Sensores a la vez. Luego de crear tu plantilla, puedes crear tus 'Grupos de Sensores'." =>
        "Recomendamos empezar creando una 'Plantilla'. ¿Por qué? Porque la plantilla dicta reglas universales (ej: el costo fijo, multiplicador de moneda o límites). Así evitas configurar mecánicamente la misma regla cientos de veces para cada sensor de un barrio o planta.",

    // 2. Sensores
    "Para crear sensores, dirígete al menú de Sensores. Cada dispositivo requiere obligatoriamente pertenecer a un Grupo, ya que heredará las configuraciones base de dicho bloque." =>
        "Dirígete al menú de Sensores. Cada dispositivo requiere obligatoriamente pertenecer a un Grupo. Es de suma importancia que la Nomenclatura del sensor (el nombre o código de serie) sea legible y preciso en los campos de metadatos, ya que esta información convive en la base de datos y será la única forma de identificar físicamente el medidor correcto en el campo.",

    // 3. Mediciones manuales
    "Al registrar lecturas sin carga masiva, selecciona 'Tomar Mediciones' para ingresar a la vista paso a paso (Modo Inspector) y enviar fotos individuales con sus lecturas de temperatura/consumo correspondientes." =>
        "Al registrar lecturas individuales desde 'Tomar Mediciones', podrás subir valores. ¿Por qué es vital exigir o adjuntar la foto del medidor? La fotografía funciona como 'seguro anti-reclamos' y como auditoría de calidad. Si un inspector ingresa un número defectuoso equivocado (ej: 1000 en vez de 100), el administrador siempre tendrá la chance de corregir consultando la imagen fuente resguardada en el registro.",

    // 4. Prorrateo
    "Si administras un barrio privado o condominio, puedes marcar sensores como 'Áreas Comunes' (ej: Riego del parque). El consumo de estos sensores especiales se prorratea y se suma matemáticamente a la despensa o consumo de los lotes privados del mismo Grupo, impactando en su cálculo financiero final de forma equitativa." =>
        "Si administras un consorcio puedes marcar sensores como 'Áreas Comunes'. ¿Por qué se ofrece esta funcionalidad? Porque automatiza las finanzas evitando excel paralelos. El gasto en recursos públicos (ej: riego del parque) se abstrae y prorratea, dividiendo el costo total equitativamente e inyectándolo a cada boleto de los medidores privados del mismo grupo vecinal de manera transparente.",

    // 5. Espacio de trabajo (Workspace)
    "El Workspace (Espacio de Trabajo) aísla visualmente toda la información. Cuando un usuario acepta tu invitación, al loguearse verá un switch arriba a la derecha para pasar de su Workspace Personal al Workspace de tu Empresa." =>
        "El Workspace aísla la información totalmente. ¿Por qué se implementó esta segregación tan estricta? Para la privacidad integral de los datos. Un inspector puede trabajar recolectando lecturas para 10 empresas distintas con la misma cuenta; este diseño fuerza a que cada base de sensores y cobros quede blindada, asegurando que un usuario nunca cruce accidentalmente clientes."

];

foreach ($replacements as $search => $replace) {
    if (strpos($content, $search) !== false) {
        $content = str_replace($search, $replace, $content);
        echo "Replaced: " . substr($search, 0, 30) . "...\n";
    } else {
        echo "NOT FOUND: \n$search\n\n";
    }
}

file_put_contents('k:\desarrollo\medflow\resources\views\help\index.blade.php', $content);
echo "Done.";
