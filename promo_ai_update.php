<?php

// 1. Update Flyer Masonry Grid
$flyerPath = 'k:\desarrollo\medflow\resources\views\flyer.blade.php';
$flyerContent = file_get_contents($flyerPath);

$searchFlyer = '<h4><i class="bi bi-info-square text-primary me-2"></i> Centro de Ayuda</h4>
                          <p>Manuales y vías de contacto de soporte listas para asistir a cualquier nivel gerencial u operario.</p>';
$replaceFlyer = '<h4><i class="bi bi-robot text-primary me-2"></i> Soporte C-Level IA</h4>
                          <p>Los suscriptores Premium y Corporate desbloquean a <b>Flowy</b>, un Agente IA disponible 24/7 integrado al Panel para responder consultas contables u operativas al instante.</p>';

$flyerContent = str_replace($searchFlyer, $replaceFlyer, $flyerContent);

// Add encoding safety
$searchFlyerEncoded = '<h4><i class="bi bi-info-square text-primary me-2"></i> Centro de Ayuda</h4>
                          <p>Manuales y vas de contacto de soporte listas para asistir a cualquier nivel gerencial u
                              operario.</p>';
$flyerContent = str_replace($searchFlyerEncoded, $replaceFlyer, $flyerContent);

file_put_contents($flyerPath, $flyerContent);


// 2. Update Landing Prices feature list
$landingPath = 'k:\desarrollo\medflow\resources\views\landing.blade.php';
$landingContent = file_get_contents($landingPath);

$searchLanding = '<div class="plan-feature"><i class="bi bi-check text-primary"></i> <b class="text-dark">Colaboración Multi-Rol</b></div>';
$replaceLanding = '<div class="plan-feature"><i class="bi bi-robot text-primary"></i> <b class="text-primary">Chat IA 24/7 (Flowy)</b></div>
                              <div class="plan-feature"><i class="bi bi-check text-primary"></i> <b class="text-dark">Colaboración Multi-Rol</b></div>';

$landingContent = str_replace($searchLanding, $replaceLanding, $landingContent);

// Also safety encode
$searchLandingEncoded = '<div class="plan-feature"><i class="bi bi-check text-primary"></i> <b class="text-dark">Colaboracin Multi-Rol</b></div>';
$replaceLandingEncoded = '<div class="plan-feature"><i class="bi bi-robot text-primary"></i> <b class="text-primary">Chat IA 24/7 (Flowy)</b></div>
                              <div class="plan-feature"><i class="bi bi-check text-primary"></i> <b class="text-dark">Colaboración Multi-Rol</b></div>';

$landingContent = str_replace($searchLandingEncoded, $replaceLandingEncoded, $landingContent);

file_put_contents($landingPath, $landingContent);


// 3. Update Welcome (if it exists and differs)
$welcomePath = 'k:\desarrollo\medflow\resources\views\welcome.blade.php';
if (file_exists($welcomePath)) {
    $welcomeContent = file_get_contents($welcomePath);
    $searchWelcome = '<li><i class="bi bi-check-circle-fill"></i> Gestin y exportacin masiva</li>';
    $searchWelcome2 = '<li><i class="bi bi-check-circle-fill"></i> Gestión y exportación masiva</li>';

    $replaceWelcome = '<li><i class="bi bi-rocket-fill text-warning"></i> Asistente de Inteligencia Artificial</li>
                          <li><i class="bi bi-check-circle-fill"></i> Gestión y exportación masiva</li>';

    $welcomeContent = str_replace($searchWelcome, $replaceWelcome, $welcomeContent);
    $welcomeContent = str_replace($searchWelcome2, $replaceWelcome, $welcomeContent);
    file_put_contents($welcomePath, $welcomeContent);
}

echo "Promotional materials updated to showcase Flowy AI!\n";
