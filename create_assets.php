<?php
// Generar Icono 512x512
$icon = imagecreatetruecolor(512, 512);
$bgList = imagecolorallocate($icon, 14, 165, 233); // #0ea5e9 Blue
imagefill($icon, 0, 0, $bgList);
$white = imagecolorallocate($icon, 255, 255, 255);
$font = 5; // Built in font
$text = "MEDFLOW";
$tw = imagefontwidth($font) * strlen($text);
$th = imagefontheight($font);
imagestring($icon, $font, (512 - $tw) / 2, (512 - $th) / 2, $text, $white);
imagepng($icon, 'k:\desarrollo\medflow\public\icono_google_512.png');
imagedestroy($icon);

// Generar Grafico de Funciones 1024x500
$banner = imagecreatetruecolor(1024, 500);
$bgBanner = imagecolorallocate($banner, 15, 23, 42); // Dark Slate
imagefill($banner, 0, 0, $bgBanner);
$textBanner = "MEDFLOW INSPECTOR";
$twB = imagefontwidth($font) * strlen($textBanner);
$thB = imagefontheight($font);
imagestring($banner, $font, (1024 - $twB) / 2, (500 - $thB) / 2, $textBanner, $white);
imagepng($banner, 'k:\desarrollo\medflow\public\banner_google_1024.png');
imagedestroy($banner);

echo "Assets created successfully\n";
