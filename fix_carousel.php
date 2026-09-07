<?php
$file = 'k:\desarrollo\medflow\resources\views\flyer.blade.php';
$content = file_get_contents($file);

$target1 = ".carousel-item img { height: 75vh; object-fit: cover; object-position: center top; width: 100%; }";
$replace1 = ".carousel-item img { height: 55vh; max-height: 550px; object-fit: cover; object-position: center top; width: 100%; }";

$target2 = ".carousel-caption-custom {
            position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
            padding: 100px 40px 40px; color: white; text-align: left;
        }";
$replace2 = ".carousel-caption-custom {
            position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.95), transparent);
            padding: 80px 40px 30px; color: white; text-align: left;
        }";

$target3 = ".carousel-item img { height: 50vh; }";
$replace3 = ".carousel-item img { height: 45vh; }";

$content = str_replace($target1, $replace1, $content);
$content = str_replace($target2, $replace2, $content);
$content = str_replace($target3, $replace3, $content);

file_put_contents($file, $content);
echo "Carousel styles minimized.\n";
