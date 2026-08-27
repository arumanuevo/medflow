<?php
$c = file_get_contents('app/Http/Controllers/SuperAdminController.php');

// Normalizing target string for search
$c = preg_replace('/public function index\(\)\s*\{\s*\$users = User::orderBy\(\'created_at\', \'desc\'\)->get\(\);\s*return view\(\'superadmin\.users\', compact\(\'users\'\)\);\s*\}/', "    public function index()
    {
        \$users = User::orderBy('created_at', 'desc')->get();
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
    }", $c);

file_put_contents('app/Http/Controllers/SuperAdminController.php', $c);

// Modify route file
$r = file_get_contents('routes/web.php');
if (strpos($r, 'superadmin.prices.save') === false) {
    $r = preg_replace('/(\'superadmin\.users\.delete\'\);)/', "$1\n        Route::post('/prices', [\App\Http\Controllers\SuperAdminController::class, 'savePrices'])->name('superadmin.prices.save');", $r);
    file_put_contents('routes/web.php', $r);
}
echo "Controller and Routes patched.\n";
