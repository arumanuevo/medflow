<?php

// 1. Modificar SuperAdminController
$fileController = 'k:\desarrollo\medflow\app\Http\Controllers\SuperAdminController.php';
$contentController = file_get_contents($fileController);

// default fallback index()
$oldIdx = "['basico' => 10000.00, 'premium' => 25000.00]";
$newIdx = "['basico' => 10000.00, 'premium' => 25000.00, 'pack' => 10000.00]";
$contentController = str_replace($oldIdx, $newIdx, $contentController);

// request validation
$oldVal = "'price_premium' => 'required|numeric'
        ]";
$newVal = "'price_premium' => 'required|numeric',
            'price_pack' => 'required|numeric'
        ]";
$contentController = str_replace($oldVal, $newVal, $contentController);

// saving to json
$oldSave = "'premium' => \$request->price_premium
        ];";
$newSave = "'premium' => \$request->price_premium,
            'pack' => \$request->price_pack
        ];";
$contentController = str_replace($oldSave, $newSave, $contentController);

// handling edge cases where old array style existed
$oldSave2 = "'premium' => \$request->price_premium,
        ];";
$contentController = str_replace($oldSave2, $newSave, $contentController);

file_put_contents($fileController, $contentController);


// 2. Modificar Vista SuperAdmin (users.blade.php) para añadir el input de Pack Extras
$fileView = 'k:\desarrollo\medflow\resources\views\superadmin\users.blade.php';
$contentView = file_get_contents($fileView);

$searchHtml = '<div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase"><i class="bi bi-star-fill text-warning me-1"></i> Plan Premium (ARS)</label>';

$replaceHtml = '<div class="col-md-4">
                                <label class="form-label text-muted small fw-bold text-uppercase"><i class="bi bi-star-fill text-warning me-1"></i> Plan Premium (ARS)</label>';

$searchHtml2 = 'value="{{ $prices[\'premium\'] ?? 25000.00 }}" required>
                            </div>';

$replaceHtml2 = 'value="{{ $prices[\'premium\'] ?? 25000.00 }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold text-uppercase"><i class="bi bi-box-seam text-success me-1"></i> Pack Extras (ARS)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0">$</span>
                                <input type="number" name="price_pack" class="form-control" step="0.01" value="{{ $prices[\'pack\'] ?? 10000.00 }}" required>
                            </div>
                            <div class="form-text mt-2"><small>Valor unitario por bloque adicional (10 sensores).</small></div>
                        '; // This handles the column layout adjustment

$contentView = str_replace('<div class="col-md-6">', '<div class="col-md-4">', $contentView);

// I'll use preg_replace for safer HTML structure replacement around the inputs
$contentView = preg_replace(
    '/(<input type="number" name="price_premium"[^>]+>\s*<\/div>\s*<\/div>)/is',
    '$1
    <div class="col-md-4">
        <label class="form-label text-muted small fw-bold text-uppercase"><i class="bi bi-box-seam text-success me-1"></i> Pack 10 Extras (ARS)</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted"><strong>$</strong></span>
            <input type="number" name="price_pack" class="form-control" step="0.01" value="{{ $prices[\'pack\'] ?? 10000.00 }}" required>
        </div>
    </div>',
    file_get_contents($fileView)
);
$contentView = preg_replace('/col-md-6/', 'col-md-4', $contentView); // change two inputs to thirds

file_put_contents($fileView, $contentView);


// 3. Update profile logic to read PRICE_PACK
$fileProfile = 'k:\desarrollo\medflow\resources\views\profile\index.blade.php';
$contentProfile = file_get_contents($fileProfile);

$searchPhp = "\$sysPrices = @json_decode(file_get_contents(storage_path('app/pricing.json')), true) ?: ['basico' => 10000, 'premium' => 25000];
        \$priceBasico = \$sysPrices['basico'];
        \$pricePremium = \$sysPrices['premium'];";
$replacePhp = "\$sysPrices = @json_decode(file_get_contents(storage_path('app/pricing.json')), true) ?: ['basico' => 10000, 'premium' => 25000, 'pack' => 10000];
        \$priceBasico = \$sysPrices['basico'] ?? 10000;
        \$pricePremium = \$sysPrices['premium'] ?? 25000;
        \$pricePack = \$sysPrices['pack'] ?? 10000;";
$contentProfile = str_replace($searchPhp, $replacePhp, $contentProfile);

$searchJs = "const PRICE_PREMIUM = {{ \$pricePremium }};";
$replaceJs = "const PRICE_PREMIUM = {{ \$pricePremium }};
        const PRICE_PACK = {{ \$pricePack }};";
$contentProfile = str_replace($searchJs, $replaceJs, $contentProfile);

// Replace PRICE_BASICO with PRICE_PACK for extra pack calculations
$contentProfile = str_replace('PRICE_BASICO).toLocaleString', 'PRICE_PACK).toLocaleString', $contentProfile);
$contentProfile = str_replace('PRICE_BASICO *', 'PRICE_PACK *', $contentProfile);

file_put_contents($fileProfile, $contentProfile);

echo "Pack pricing configuration and variables linked successfully.\n";
