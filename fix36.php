<?php
$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path fill="#0d6efd" d="M8 16a6 6 0 0 0 6-6c0-1.655-1.122-2.904-2.432-4.362C10.254 4.176 8.75 2.503 8 0c-.75 2.503-2.254 4.176-3.568 5.638C3.122 7.096 2 8.345 2 10a6 6 0 0 0 6 6z"/></svg>';
file_put_contents('public/favicon.svg', $svg);

$tag = '<link rel="icon" type="image/svg+xml" href="{{ asset(\'favicon.svg\') }}">';

// App Layout
if (file_exists('resources/views/layouts/app.blade.php')) {
    $app = file_get_contents('resources/views/layouts/app.blade.php');
    if (strpos($app, 'favicon.svg') === false) {
        $app = preg_replace('/<title>/i', $tag . "\n    <title>", $app);
        file_put_contents('resources/views/layouts/app.blade.php', $app);
    }
}

// Modern Layout
if (file_exists('resources/views/layouts/modern.blade.php')) {
    $mod = file_get_contents('resources/views/layouts/modern.blade.php');
    if (strpos($mod, 'favicon.svg') === false) {
        $mod = preg_replace('/<title>/i', $tag . "\n    <title>", $mod);
        file_put_contents('resources/views/layouts/modern.blade.php', $mod);
    }
}

// Landing
if (file_exists('resources/views/landing.blade.php')) {
    $lan = file_get_contents('resources/views/landing.blade.php');
    if (strpos($lan, 'favicon.svg') === false) {
        $lan = preg_replace('/<title>/i', $tag . "\n    <title>", $lan);
        file_put_contents('resources/views/landing.blade.php', $lan);
    }
}

// Delete default favicon.ico if it exists
if (file_exists('public/favicon.ico'))
    unlink('public/favicon.ico');

echo "Favicon generated and injected.\n";
