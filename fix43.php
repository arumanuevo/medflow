<?php
$c = file_get_contents('app/Services/SidebarService.php');

$injection = "\n        // Centro de Ayuda\n        \$menu[] = [\n            'icon' => 'bi bi-question-circle',\n            'label' => 'Centro de Ayuda',\n            'url' => '/ayuda',\n            'active' => request()->is('ayuda*'),\n        ];\n\n        ";

// Find 'public function getMainMenu()'
$pos = strpos($c, 'public function getMainMenu()');
if ($pos !== false) {
    // Find the first 'return $menu;' after $pos
    $returnPos = strpos($c, 'return $menu;', $pos);
    if ($returnPos !== false) {
        $c = substr_replace($c, $injection . 'return $menu;', $returnPos, strlen('return $menu;'));
        file_put_contents('app/Services/SidebarService.php', $c);
        echo "Injected Ayuda to Sidebar.\n";
    }
}

// Routes
$r = file_get_contents('routes/web.php');
// Add inside Auth middleware
// We look for: Route::middleware(['auth', 'verified'])->group(function () {
$authPos = strpos($r, "Route::middleware(['auth', 'verified'])->group(function () {");
if ($authPos !== false) {
    $r = substr_replace($r, "Route::middleware(['auth', 'verified'])->group(function () {\n    Route::get('/ayuda', function() { return view('help.index'); })->name('help.index');\n", $authPos, strlen("Route::middleware(['auth', 'verified'])->group(function () {"));
    file_put_contents('routes/web.php', $r);
    echo "Injected Ayuda Route.\n";
} else {
    // try alternative
    $authPos = strpos($r, "Route::middleware(['auth'])->group(function () {");
    if ($authPos !== false) {
        $r = substr_replace($r, "Route::middleware(['auth'])->group(function () {\n    Route::get('/ayuda', function() { return view('help.index'); })->name('help.index');\n", $authPos, strlen("Route::middleware(['auth'])->group(function () {"));
        file_put_contents('routes/web.php', $r);
        echo "Injected Ayuda Route (fallback).\n";
    } else {
        echo "Could not find Auth route group!\n";
    }
}
