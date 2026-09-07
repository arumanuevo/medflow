<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Template;
use App\Models\SensorGroup;
use App\Models\Sensor;
use Illuminate\Support\Str;

$email = 'medflowsistemas@gmail.com';
$user = User::where('email', $email)->first();

if (!$user) {
    echo "Usuario no encontrado.\n";
    exit;
}

// 1. Create Template for Water
$template = Template::create([
    'user_id' => $user->id,
    'name' => 'Medición de Agua (Consorcios)',
    'description' => 'Plantilla avanzada para lectura de cañerías de agua con datos de inquilino/dueño.',
    'fields' => [
        [
            'name' => 'nombre_dueno',
            'label' => 'Nombre del Titular',
            'type' => 'text',
            'required' => true,
            'is_key' => false
        ],
        [
            'name' => 'email_dueno',
            'label' => 'Email del Titular',
            'type' => 'text',
            'required' => false,
            'is_key' => false
        ],
        [
            'name' => 'lectura_m3',
            'label' => 'Lectura de Agua (m³)',
            'type' => 'number',
            'required' => true,
            'is_key' => true
        ],
        [
            'name' => 'estado_valvula',
            'label' => 'Estado de Válvula',
            'type' => 'select',
            'options' => ['Abierta', 'Cerrada', 'Fuga Detectada'],
            'required' => true,
            'is_key' => false
        ]
    ],
    'is_global' => false,
    'is_active' => true
]);

echo "Plantilla creada: " . $template->name . "\n";

// 2. Create Sensor Group
$group = SensorGroup::create([
    'user_id' => $user->id,
    'template_id' => $template->id,
    'name' => 'Torre Norte - Medidores de Agua',
    'location' => 'Capital Federal, Edificio Principal',
    'description' => 'Grupo de medidores de agua de todos los departamentos de la torre.',
    'is_active' => true
]);

echo "Grupo creado: " . $group->name . "\n";

// 3. Create 100 Sensors
$count = 0;
for ($i = 1; $i <= 100; $i++) {
    $piso = ceil($i / 10);
    $depto = sprintf("%02d", $i - (($piso - 1) * 10)); // 01 to 10

    Sensor::create([
        'group_id' => $group->id,
        'name' => "Medidor Agua - Piso $piso Depto $depto",
        'identificador_fisico' => 'AG-' . strtoupper(Str::random(6)),
        'tipo' => 'Agua',
        'is_active' => true
    ]);
    $count++;
}

echo "$count sensores físicos generados y vinculados al grupo exitosamente.\n";
