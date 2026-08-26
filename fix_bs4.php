<?php
$files = [
    'resources/views/superadmin/users.blade.php',
    'resources/views/superadmin/invoices.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file))
        continue;
    $content = file_get_contents($file);

    // Fix Bootstrap 5 vs 4 syntax
    $content = str_replace('data-bs-toggle=', 'data-toggle=', $content);
    $content = str_replace('data-bs-target=', 'data-target=', $content);
    $content = str_replace('data-bs-dismiss=', 'data-dismiss=', $content);

    // Fix the btn-close to close with times
    $content = str_replace(
        '<button type="button" class="btn-close" data-dismiss="modal"></button>',
        '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>',
        $content
    );

    file_put_contents($file, $content);
    echo "Fixed $file\n";
}
