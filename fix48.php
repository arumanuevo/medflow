<?php
$c = file_get_contents('app/Services/SidebarService.php');

$injection = "\n\n        // Centro de Ayuda global \n        \$menu[] = [\n            'icon' => 'bi bi-question-circle',\n            'label' => 'Centro de Ayuda',\n            'url' => '/ayuda',\n            'active' => request()->is('ayuda*'),\n        ];\n\n        return \$menu;\n    }";

// Target strictly the end of getMenuItems which is followed immediately by getOwnerMenu
$c = preg_replace('/return \$menu;\s*\}\s*\/\*\*\s*\*\s*Menú para el propietario/s', $injection . "\n\n    /**\n     * Menú para el propietario", $c, 1);
if (strpos($c, 'Menú para el propietario') === false) {
    // Oh, wait, the text is "MenÃº por defecto" or "MenÃº para el propietario"?
    // In HEAD~2 it was: "Menú para el propietario".
    $c = preg_replace('/return \$menu;\s*\}\s*\/\*\*.+?Men.*?propietario/s', $injection . "\n\n    /**\n     * Menú para el propietario", $c, 1);
}

// Let's use str_replace in a precise block. Wait, I can explode at `private function getOwnerMenu()` and replace the preceding string.
$c = file_get_contents('app/Services/SidebarService.php');
$parts = explode('private function getOwnerMenu()', $c);
if (count($parts) > 1) {
    // replace `return $menu;` and `}` before `private function getOwnerMenu()`
    $parts[0] = preg_replace('/return \$menu;\s*\}\s*\/\*\*\s*$/', $injection . "\n\n    /**\n     ", $parts[0]);
    $c = implode('private function getOwnerMenu()', $parts);
    file_put_contents('app/Services/SidebarService.php', $c);
    echo "Replaced correctly by splitting around getOwnerMenu.\n";
} else {
    echo "ERROR: getOwnerMenu not found!\n";
}
