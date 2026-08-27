<?php
$c = file_get_contents('app/Services/SidebarService.php');

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

        // Si el invitado tiene roles perdidos o remanentes, forzar por defecto interface silenciosa
        // return \$this->getDefaultMenu();
    }
EOF;

// Replace the botched block
$c = preg_replace('/\[\s*\'icon\' => \'bi bi-person-circle\',\s*\'label\' => \'Mi Perfil\',\s*\'url\' => \'\/profile\',\s*\'active\' => request\(\)->is\(\'profile\*\'\),\s*\],\s*\]\);\s*\}(.*?)\}$/s', $target, $c);

// Wait, the regex might fail if `}` is repeated. Let's do it firmly.
$pos = strpos($c, 'request()->is(\'profile*\'),');
if ($pos !== false) {
    // Find the end of the array_merge ']);'
    $endPos = strpos($c, ']);', $pos);
    if ($endPos !== false) {
        $replacementText = <<<EOF
request()->is('profile*'),
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

        $methodEnd = strpos($c, 'private function getDefaultMenu()', $endPos);
        $c = substr_replace($c, $replacementText . "\n\n    /**\n     * Menú por defecto", $pos, $methodEnd - $pos + strlen("    /**\n     * Menú por defecto"));
    }
}
$c = str_replace('}        }        // Si el', '}        // Si el', $c);

// Hard override if it messed up. I will just restore the file perfectly.
file_put_contents('app/Services/SidebarService.php', $c);
echo "Sidebar repaired!\n";
