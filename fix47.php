<?php
$c = file_get_contents('app/Services/SidebarService.php');

$injection = "\n\n        // Centro de Ayuda global \n        \$menu[] = [\n            'icon' => 'bi bi-question-circle',\n            'label' => 'Centro de Ayuda',\n            'url' => '/ayuda',\n            'active' => request()->is('ayuda*'),\n        ];\n\n        return \$menu;\n    }";

$c = preg_replace('/return \$menu;\s*\}\s*\/\*\*/', $injection . "\n\n    /**", $c);
file_put_contents('app/Services/SidebarService.php', $c);
echo "Injected Ayuda cleanly!\n";
