<?php
$fileController = 'k:\desarrollo\medflow\app\Http\Controllers\SuperAdminController.php';
$contentController = file_get_contents($fileController);

// Fix validation
$contentController = preg_replace(
    '/\'price_premium\'\s*=>\s*\'required\|numeric\|min:0\',?\s*\]\);/i',
    "'price_premium' => 'required|numeric|min:0',\n            'price_pack' => 'required|numeric|min:0'\n        ]);",
    $contentController
);

// Fix array creation before saving
$contentController = preg_replace(
    '/\'premium\'\s*=>\s*\$request->price_premium\s*\];/i',
    "'premium' => \$request->price_premium,\n            'pack' => \$request->price_pack\n        ];",
    $contentController
);

file_put_contents($fileController, $contentController);
echo "Regex replace done!\n";
