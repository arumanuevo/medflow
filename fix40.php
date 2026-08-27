<?php
// Let's completely clean up the routes/web.php
$r = file_get_contents('routes/web.php');
// strip ALL previous /ayuda routes
$r = preg_replace("/\/\/ Centro de Ayuda\s*Route::get\('\/ayuda'.*?\n/s", "", $r);
$r = preg_replace("/Route::get\('\/ayuda'.*?\n/s", "", $r);

// Now safely inject exactly ONCE inside the auth middleware
$r = preg_replace('/(Route::middleware\(\[\'auth\',\s*\'verified\'\]\)->group\(function\s*\(\)\s*\{)/', "$1\n\n    // Centro de Documentación y Ayuda\n    Route::get('/ayuda', function() {\n        return view('help.index');\n    })->name('help.index');\n", $r);
file_put_contents('routes/web.php', $r);
echo "Routes fixed.\n";

// Now, completely clean up SidebarService.php
$c = file_get_contents('app/Services/SidebarService.php');
// Strip ALL previous occurrences
$c = preg_replace("/\/\/ AGREGAR CENTRO DE AYUDA AL FINAL\s*\\\$menu\[\]\s*=\s*\[\s*'icon'.*?'Centro de Ayuda'.*?\/\ayuda'.*?'ayuda\*'\),\s*\];/s", "", $c);
$c = preg_replace("/\\\$menu\[\]\s*=\s*\[\s*'icon'\s*=>\s*'bi\s*bi-question-circle',\s*'label'\s*=>\s*'Centro de Ayuda',\s*'url'\s*=>\s*'\/ayuda',\s*'active'\s*=>\s*request\(\)->is\('ayuda\*'\),\s*\];/s", "", $c);

// Re-inject EXACTLY ONCE at the END of getMainMenu() ONLY!
// Let's search for "return $menu;" in getMainMenu.
// We can use the fact that getMainMenu ends before the next method `public function getCollaboratorMenu()` or something.
$replaceCount = 0;
// Note: We use a precise regex that looks for the return $menu inside the method. Let's do it by finding the first "return $menu;" after "public function getMainMenu()".
$pos = strpos($c, 'public function getMainMenu()');
if ($pos !== false) {
    $returnPos = strpos($c, 'return $menu;', $pos);
    if ($returnPos !== false) {
        $injection = "        // Centro de Ayuda\n        \$menu[] = [\n            'icon' => 'bi bi-question-circle',\n            'label' => 'Centro de Ayuda',\n            'url' => '/ayuda',\n            'active' => request()->is('ayuda*'),\n        ];\n\n        ";
        $c = substr_replace($c, $injection . 'return $menu;', $returnPos, strlen('return $menu;'));
    }
}
file_put_contents('app/Services/SidebarService.php', $c);
echo "Sidebar fixed.\n";
