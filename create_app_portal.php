<?php
$webRoutes = 'k:\desarrollo\medflow\routes\web.php';
$content = file_get_contents($webRoutes);

// Add the private app distribution route
$routeStr = <<<EOT

// Portal Privado de Descarga del APK
Route::get('/inspector/descargar-app', function () {
    return view('app_download');
})->name('inspector.descarga');
EOT;

if (strpos($content, 'inspector/descargar-app') === false) {
    file_put_contents($webRoutes, $content . "\n" . $routeStr);
}

// Create the view for the download portal
$viewPath = 'k:\desarrollo\medflow\resources\views\app_download.blade.php';
$viewContent = <<<EOT
@extends('layouts.modern')

@section('content')
<div class="container py-5 d-flex justify-content-center">
    <div class="col-md-6 text-center">
        <div class="card shadow-sm border-0 rounded-4 p-5 mb-4">
            <h1 class="display-1 text-primary mb-3"><i class="bi bi-phone"></i></h1>
            <h2 class="fw-bold mb-3">Tu App Inspector</h2>
            <p class="text-secondary mb-4">Haz clic en el botón inferior para instalar la última versión directa sin intermediarios. Si Google Play Protect te advierte que el desarrollador es desconocido, simplemente selecciona <b>"Instalar de todas formas"</b>, ya que es la app corporativa homologada de MedFlow.</p>
            <a href="{{ asset('apk/medflow_inspector_latest.apk') }}" download class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm fw-bold">
                <i class="bi bi-download me-2"></i> Descargar v1.0
            </a>
            <div class="mt-4 text-start bg-light p-3 rounded-3" style="font-size: 0.85rem">
                <strong><i class="bi bi-gear me-1"></i> Guía rápida de Instalación:</strong>
                <ul class="mb-0 mt-2 text-muted">
                    <li>1. Abre el archivo descargado.</li>
                    <li>2. Ve a Configuración de Chrome > <i>"Permitir desde esta fuente"</i>.</li>
                    <li>3. Inicia sesión con la credencial o Escanea el QR de sesión.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
EOT;

file_put_contents($viewPath, $viewContent);

// Ensure the apk directory exists in public
$apkDir = 'k:\desarrollo\medflow\public\apk';
if (!is_dir($apkDir)) {
    mkdir($apkDir, 0777, true); // Create directory so they can FTP their APK here
}
// Drop a dummy txt inside so folder pushes to git/FTP if needed
file_put_contents($apkDir . '\README.txt', 'Poner tu archivo "medflow_inspector_latest.apk" en esta carpeta.');

echo "Private App Store Portal built.\n";
