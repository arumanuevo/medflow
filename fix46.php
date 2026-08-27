<?php
$c = file_get_contents('SidebarService_dump.txt');
// The original had:
//        $menu = array_merge($menu, [
//            [
//                'icon' => 'bi bi-folder',
// ...
//            [
//                'icon' => 'bi bi-person-circle',
//                'label' => 'Mi Perfil',
//                'url' => '/profile',
//                'active' => request()->is('profile*'),
//            ],
//        ]);
//
//        }
//
//        // Si el invitado tiene roles perdidos o remanentes, forzar por defecto interface silenciosa
//        return $this->getDefaultMenu();
//    }

$target = <<<EOF
            [
                'icon' => 'bi bi-person-circle',
                'label' => 'Mi Perfil',
                'url' => '/profile',
                'active' => request()->is('profile*'),
            ],
            [
                'icon' => 'bi bi-question-circle',
                'label' => 'Centro de Ayuda',
                'url' => '/ayuda',
                'active' => request()->is('ayuda*'),
            ],
        ]);

        return \$menu;
    }
EOF;

// Replace from 'Mi Perfil' to the end of getMenuItems
$c = preg_replace('/\[\s*\'icon\' => \'bi bi-person-circle\',\s*\'label\' => \'Mi Perfil\',\s*\'url\' => \'\/profile\',\s*\'active\' => request\(\)->is\(\'profile\*\'\),\s*\],\s*\]\);\s*\}(.*?)\}/s', $target, $c);

file_put_contents('app/Services/SidebarService.php', $c);
echo "Sidebar fully rebuilt and single Ayuda item injected.\n";
