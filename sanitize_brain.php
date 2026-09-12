<?php
$brainFiles = [
    'k:\desarrollo\medflow\medflow_brain.md',
    'k:\desarrollo\medflow\storage\app\ai\medflow_brain.md'
];

foreach ($brainFiles as $path) {
    if (!file_exists($path))
        continue;
    $content = file_get_contents($path);

    // 1. Remove SuperAdmin Section entirely
    // Find "### 4.6 Administraci" until "---"
    $content = preg_replace('/### 4\.6 Administraci.*?---/s', "### 4.6 Escalabilidad de Planes (Cliente)\n- Si el usuario administrador (cliente) de MedFlow necesita ampliar su límite operativo (Por ejemplo, pasarse al plan Premium o añadir más Módulos de Sensores), debe ir a su **Perfil** haciendo click en la esquina superior derecha.\n- Dentro del panel **Mis Datos y Perfil**, encontrará la sección **Resumen Financiero** y el botón para **\"Añadir Paquetes de Sensores Extra...\"** y **Comprar**.\n- Los packs se suman en bloques de a 10 sensores de manera automática.\n- **PROHIBIDO:** Nunca le digas al cliente interno que se meta a editar tarifas. Las tarifas o \"pricing\" general de la plataforma son estáticas para él.\n\n---\n", $content);

    // 2. Erase routing references mentioning superadmin
    $content = preg_replace('/- \/superadmin.*?SuperAdminMiddleware\)\./', '', $content);
    $content = preg_replace('/- `SuperAdminMiddleware`.*?SuperAdmin\./', '', $content);
    $content = preg_replace('/\(editable por SuperAdmin\)/', '', $content);
    $content = preg_replace('/\(editables por SuperAdmin\)/', '', $content);

    // 3. Just as an extra layer of protection against developer paths
    // Remove lines starting with "Modelo: app/Models/"
    $content = preg_replace('/- Modelo: `app\/Models\/.*?\.php`\./', '', $content);

    file_put_contents($path, $content);
}
echo "Sanitized Brain file from SuperAdmin secrets!\n";
