<?php
$c = file_get_contents('app/Services/SidebarService.php');

// We just need to remove "AGREGAR CENTRO DE AYUDA AL FINAL" block from getOwnerMenu.
// The easiest way is to split by "private function getCollaboratorMenu(" and remove the last addition before it.
$parts = explode('private function getCollaboratorMenu(', $c);
if (count($parts) > 1) {
    // parts[0] is everything up to getCollaboratorMenu.
    // The duplicate is right before `return $menu; }` in parts[0]
    $regex = "/\/\/\s*AGREGAR CENTRO DE AYUDA AL FINAL\s*\\\$menu\[\]\s*=\s*\[.*?'\/ayuda'.*?\];/s";

    // We only want to remove the LAST occurrence in $parts[0].
    // Wait, getOwnerMenu() is the one before getCollaboratorMenu.

    // Actually, why not just entirely strip ALL occurrences of Ayuda block globally, and add it exactly ONCE?
    // Let's strip ALL Ayuda assignments.
    $c = preg_replace("/\/\/\s*AGREGAR CENTRO DE AYUDA AL FINAL.*?\];/s", "", $c);
    $c = preg_replace("/\/\/\s*Centro de Ayuda global.*?\];/s", "", $c);

    // Then add it BACK only into getMenuItems().
    // Find the end of getMenuItems():
    // The end of getMenuItems is marked by:
    /*
        if ($user->email === 'scastellanoadmin@gmail.com') {
            array_unshift($menu, [ ... ]);
        }
    */

    // Find scastellanoadmin@gmail.com
    $pos = strpos($c, 'scastellanoadmin@gmail.com');
    if ($pos !== false) {
        $endPos = strpos($c, 'return $menu;', $pos);
        if ($endPos !== false) {
            $injection = "\n        // Centro de Ayuda\n        \$menu[] = [\n            'icon' => 'bi bi-question-circle',\n            'label' => 'Centro de Ayuda',\n            'url' => '/ayuda',\n            'active' => request()->is('ayuda*'),\n        ];\n\n        ";
            $c = substr_replace($c, $injection . 'return $menu;', $endPos, strlen('return $menu;'));
        }
    }
}
file_put_contents('app/Services/SidebarService.php', $c);
echo "Duplicate removed!\n";
