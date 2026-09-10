<?php
$fileController = 'k:\desarrollo\medflow\app\Http\Controllers\SuperAdminController.php';
$contentController = file_get_contents($fileController);

// request validation
$oldVal = "'price_premium' => 'required|numeric|min:0',
        ]";
$newVal = "'price_premium' => 'required|numeric|min:0',
            'price_pack' => 'required|numeric|min:0',
        ]";
$contentController = str_replace($oldVal, $newVal, $contentController);

// saving to json
$oldSave = "'premium' => \$request->price_premium
        ];";
$newSave = "'premium' => \$request->price_premium,
            'pack' => \$request->price_pack
        ];";
$contentController = str_replace($oldSave, $newSave, $contentController);

file_put_contents($fileController, $contentController);
echo "SuperAdminController save logic fixed!\n";
