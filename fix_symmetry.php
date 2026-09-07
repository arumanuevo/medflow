<?php
$file = 'k:\desarrollo\medflow\resources\views\flyer.blade.php';
$content = file_get_contents($file);

// 1. Achicar significativamente el carrusel y cambiar a object-fit: contain
$carouselContainerOld = ".carousel-container { max-width: 950px; margin: 0 auto; border: 6px solid #f8fafc; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(20, 20, 20, 0.35); overflow: hidden; }";
$carouselContainerNew = ".carousel-container { max-width: 850px; margin: 0 auto; border: 4px solid #f8fafc; border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,0.15); overflow: hidden; background: #0f172a; }";

$carouselImgOld = ".carousel-item img { height: 55vh; max-height: 550px; object-fit: cover; object-position: center top; width: 100%; }";
$carouselImgNew = ".carousel-item img { height: 450px !important; max-height: 450px; object-fit: contain; object-position: center; width: 100%; padding: 15px; background: #0f172a; }";

$carouselMobileOld = ".carousel-item img { height: 45vh; }";
$carouselMobileNew = ""; // We remove this to keep a stable height or just override it

$content = str_replace($carouselContainerOld, $carouselContainerNew, $content);
$content = str_replace($carouselImgOld, $carouselImgNew, $content);
$content = str_replace($carouselMobileOld, $carouselMobileNew, $content);


// 2. Modificar la grilla para que sea estrictamente simétrica (3 columnas arriba, 3 abajo = 6 items)
$masonryGridOld = ".masonry-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }";
$masonryGridNew = ".masonry-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; max-width: 1200px; margin: 0 auto; }
        @media (max-width: 991px) { .masonry-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { .masonry-grid { grid-template-columns: 1fr; } }";

$content = str_replace($masonryGridOld, $masonryGridNew, $content);

file_put_contents($file, $content);
echo "Layout adjustments done!\n";
