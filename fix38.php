<?php
$c = file_get_contents('app/Services/SidebarService.php');

$target = "        // âœ… INYECCIÃ“N DE ROL SUPERADMIN POR EMAIL (Root)\n        if (\$user->email === 'scastellanoadmin@gmail.com') {\n            array_unshift(\$menu, [\n                'icon' => 'bi bi-shield-lock-fill text-danger',\n                'label' => 'Panel SuperAdmin',\n                'url' => '/superadmin/users',\n                'active' => request()->is('superadmin*'),\n                'highlight' => true\n            ]);\n        }\n\n        return \$menu;\n    }";

$targetAlt = "        // ✅ INYECCIÓN DE ROL SUPERADMIN POR EMAIL (Root)\n        if (\$user->email === 'scastellanoadmin@gmail.com') {\n            array_unshift(\$menu, [\n                'icon' => 'bi bi-shield-lock-fill text-danger',\n                'label' => 'Panel SuperAdmin',\n                'url' => '/superadmin/users',\n                'active' => request()->is('superadmin*'),\n                'highlight' => true\n            ]);\n        }\n\n        return \$menu;\n    }";

$replacement = <<<EOF
        // INYECCIÓN DE ROL SUPERADMIN POR EMAIL (Root)
        if (\$user->email === 'scastellanoadmin@gmail.com') {
            array_unshift(\$menu, [
                'icon' => 'bi bi-shield-lock-fill text-danger',
                'label' => 'Panel SuperAdmin',
                'url' => '/superadmin/users',
                'active' => request()->is('superadmin*'),
                'highlight' => true
            ]);
        }

        // AGREGAR CENTRO DE AYUDA AL FINAL
        \$menu[] = [
            'icon' => 'bi bi-question-circle',
            'label' => 'Centro de Ayuda',
            'url' => '/ayuda',
            'active' => request()->is('ayuda*'),
        ];

        return \$menu;
    }
EOF;

// Since there can be encoding issues, let's use a simple regex replacing just the return statement
$c = preg_replace('/(\n\s*return\s+\$menu;\s*\n\s*\})/', "\n        // AGREGAR CENTRO DE AYUDA AL FINAL\n        \$menu[] = [\n            'icon' => 'bi bi-question-circle',\n            'label' => 'Centro de Ayuda',\n            'url' => '/ayuda',\n            'active' => request()->is('ayuda*'),\n        ];$1", $c);

file_put_contents('app/Services/SidebarService.php', $c);
echo "Sidebar Help Item injected.\n";

// Now fix the favicon.
// Let's use a generic multi-purpose icon. For instances, "bi-layers" or "bi-activity" (wave).
// We'll use a neat dashboard-like gauge or stacked squares to represent different things (water, gas).
// Actually a "bi-hexagon-fill" or a specialized icon like "bi-activity" works well.
// Let's use a stylized Activity Wave SVG.
$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path fill="#0d6efd" d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/><path fill="#0d6efd" d="M3 8.5a.5.5 0 0 1 .5-.5h1.89l1.254-2.508a.5.5 0 0 1 .91-.016l1.294 2.983.844-1.266a.5.5 0 0 1 .843.018l1.109 1.789H12.5a.5.5 0 0 1 0 1H11a.5.5 0 0 1-.424-.236l-.689-1.11-.849 1.272a.5.5 0 0 1-.844-.017l-1.3-3-1.246 2.492A.5.5 0 0 1 5.2 9H3.5a.5.5 0 0 1-.5-.5z"/></svg>';
$svgBase64 = base64_encode($svg);
$oldTag = '<link rel="icon" type="image/svg+xml" href="{{ asset(\'favicon.svg\') }}">';
$newTag = '<link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,' . $svgBase64 . '">';

$files = [
    'resources/views/layouts/app.blade.php',
    'resources/views/layouts/modern.blade.php',
    'resources/views/landing.blade.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace($oldTag, $newTag, $content);
        file_put_contents($file, $content);
    }
}
if (file_exists('public/favicon.svg'))
    unlink('public/favicon.svg');
echo "Favicon replaced with base64 Activity chart icon.\n";
