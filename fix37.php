<?php
$c = file_get_contents('app/Services/SidebarService.php');
// Add to the end, just before `return $menu;`
$c = preg_replace('/return \$menu;\s*\}\s*\}/', "    \$menu[] = [
            'icon' => 'bi bi-question-circle',
            'label' => 'Centro de Ayuda',
            'url' => '/ayuda',
            'active' => request()->is('ayuda*'),
        ];\n\n        return \$menu;\n    }\n}", $c);
file_put_contents('app/Services/SidebarService.php', $c);

// Also add a route
$r = file_get_contents('routes/web.php');
if (strpos($r, "Route::get('/ayuda'") === false) {
    // Add before generic middleware auth
    $r = str_replace(
        "      // Dashboard\n      Route::get('/dashboard'",
        "      // Centro de Ayuda\n      Route::get('/ayuda', function() { return view('help.index'); })->name('help.index');\n\n      // Dashboard\n      Route::get('/dashboard'",
        $r
    );
    file_put_contents('routes/web.php', $r);
}

// Make help directory
@mkdir('resources/views/help', 0777, true);
