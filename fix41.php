<?php
$c = file_get_contents('app/Services/SidebarService.php');

// Delete everything starting from `// AGREGAR CENTRO DE AYUDA AL FINAL` to `];`
// Multiple times to be safe
$c = preg_replace("/\/\/\s*AGREGAR CENTRO DE AYUDA AL FINAL.*?\];/s", "", $c);
$c = preg_replace("/\/\/\s*Centro de Ayuda.*?\];/s", "", $c);

// Also remove empty lines before return $menu;
$c = preg_replace("/\n\s*\n\s*return \\\$menu;/s", "\n        return \$menu;", $c);

// Now precisely put ONE item at the end of getMainMenu, exactly.
// Let's explode by "public function getMainMenu()"
$parts = explode('public function getMainMenu()', $c);
if (count($parts) > 1) {
    // inside the second part, find the FIRST return $menu;
    $methodBody = $parts[1];
    $insertPos = strpos($methodBody, 'return $menu;');
    if ($insertPos !== false) {
        $injection = "\n        // Centro de Ayuda\n        \$menu[] = [\n            'icon' => 'bi bi-question-circle',\n            'label' => 'Centro de Ayuda',\n            'url' => '/ayuda',\n            'active' => request()->is('ayuda*'),\n        ];\n\n        ";
        $methodBody = substr_replace($methodBody, $injection, $insertPos, 0);
        $parts[1] = $methodBody;
    }
    $c = implode('public function getMainMenu()', $parts);
}

file_put_contents('app/Services/SidebarService.php', $c);

// Checking Routes again
$r = file_get_contents('routes/web.php');
// Ensure it's correct
$r = preg_replace("/\/\/ Centro de .*?\n\s*Route::get\('\/ayuda'.*?\n\s*\}\)->name\('help\.index'\);\n/s", "", $r);
// Inject
$r = preg_replace('/(Route::middleware\(\[\'auth\',\s*\'verified\'\]\)->group\(function\s*\(\)\s*\{)/', "$1\n\n    // Centro de Documentación y Ayuda\n    Route::get('/ayuda', function() {\n        return view('help.index');\n    })->name('help.index');\n", $r);
file_put_contents('routes/web.php', $r);
echo "Cleanup done.\n";
