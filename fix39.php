<?php
// Fix Sidebar duplicate
$c = file_get_contents('app/Services/SidebarService.php');
$target = "        // AGREGAR CENTRO DE AYUDA AL FINAL
        \$menu[] = [
            'icon' => 'bi bi-question-circle',
            'label' => 'Centro de Ayuda',
            'url' => '/ayuda',
            'active' => request()->is('ayuda*'),
        ];";
$c = str_replace($target, "", $c);

// Re-add just *one* occurrence before `return $menu;`
$c = preg_replace('/(\n\s*return\s+\$menu;\s*\n\s*\})/', "\n$target$1", $c, 1);
file_put_contents('app/Services/SidebarService.php', $c);
echo "Sidebar duplicate solved.\n";

// Fix missing Route
$r = file_get_contents('routes/web.php');
if (strpos($r, "Route::get('/ayuda'") === false) {
    // Add inside Auth middleware! This is crucial.
    $r = preg_replace('/(Route::middleware\(\[\'auth\', \'verified\'\]\)->group\(function \(\) \{)/', "$1\n\n    // Centro de Ayuda\n    Route::get('/ayuda', function() { return view('help.index'); })->name('help.index');", $r);
    file_put_contents('routes/web.php', $r);
    echo "Help Route correctly injected inside Auth group.\n";
}

// Replace Droplet with Activity globally in views
$files = [
    'resources/views/layouts/public.blade.php',
    'resources/views/public/visor.blade.php',
    'resources/views/landing.blade.php',
    'resources/views/welcome.blade.php',
    'resources/views/layouts/modern.blade.php',
    'resources/views/layouts/app.blade.php'
];

foreach ($files as $f) {
    if (file_exists($f)) {
        $content = file_get_contents($f);
        $content = str_replace('bi-droplet-half', 'bi-activity', $content);
        $content = str_replace('bi-droplet-fill', 'bi-activity', $content);
        $content = str_replace('bi-droplet', 'bi-activity', $content);
        file_put_contents($f, $content);
    }
}
echo "Droplets replaced by Activity.\n";
