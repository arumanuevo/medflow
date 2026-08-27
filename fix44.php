<?php
$c = file_get_contents('app/Services/SidebarService.php');

$injection = "\n        // Centro de Ayuda\n        \$menu[] = [\n            'icon' => 'bi bi-question-circle',\n            'label' => 'Centro de Ayuda',\n            'url' => '/ayuda',\n            'active' => request()->is('ayuda*'),\n        ];\n\n        ";

// Find 'public function getMenuItems()'
$pos = strpos($c, 'public function getMenuItems()');
if ($pos !== false) {
    // Find the first 'return $menu;' after $pos
    $returnPos = strpos($c, 'return $menu;', $pos);
    if ($returnPos !== false) {
        $c = substr_replace($c, $injection . 'return $menu;', $returnPos, strlen('return $menu;'));
        file_put_contents('app/Services/SidebarService.php', $c);
        echo "Injected Ayuda to Sidebar via getMenuItems.\n";
    }
}
