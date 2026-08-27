<?php
$c = file_get_contents('app/Http/Controllers/SuperAdminController.php');

$target = <<<EOF
    public function index()
    {
        \$users = User::all();
        return view('superadmin.users', compact('users'));
    }
EOF;

$pricesCode = <<<EOF
    public function index()
    {
        \$users = User::all();
        \$prices = @json_decode(file_get_contents(storage_path('app/pricing.json')), true) ?: ['basico' => 10000.00, 'premium' => 25000.00];
        return view('superadmin.users', compact('users', 'prices'));
    }

    public function savePrices(Request \$request)
    {
        \$request->validate([
            'price_basico' => 'required|numeric|min:0',
            'price_premium' => 'required|numeric|min:0',
        ]);
        \$prices = [
            'basico' => \$request->price_basico,
            'premium' => \$request->price_premium
        ];
        file_put_contents(storage_path('app/pricing.json'), json_encode(\$prices));
        return redirect()->back()->with('success', 'Precios de los planes actualizados correctamente en ARS.');
    }
EOF;

$c = str_replace(str_replace("\r\n", "\n", $target), str_replace("\r\n", "\n", $pricesCode), $c);
$c = str_replace($target, $pricesCode, $c);

file_put_contents('app/Http/Controllers/SuperAdminController.php', $c);
echo "Injected controller\n";
