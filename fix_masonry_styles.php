<?php
$file = 'k:\desarrollo\medflow\resources\views\flyer.blade.php';
$content = file_get_contents($file);

// Replace the entire .masonry-item and hover block, plus img
$content = preg_replace(
    '/\.masonry-item\s*\{(.*?)\}\s*\.masonry-item:hover\s*\{(.*?)\}\s*\.masonry-item\s*img\s*\{(.*?)\}/s',
    ".masonry-item { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 15px -3px rgba(0,0,0,0.05); border: 2px solid #cbd5e1; transition: 0.3s; }
        .masonry-item:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 25px 30px -5px rgba(0,0,0,0.15); border-color: var(--accent); z-index: 2;}
        .masonry-item img { width: 100%; height: auto; display: block; border-bottom: 1px solid #e2e8f0; } /* Esta línea eliminó la raya azul 3px */
        .masonry-caption { padding: 30px 25px 25px 25px; } /* Añade el espaciado pedido */
        .masonry-caption h4 { font-size: 1.15rem; font-weight: 700; color: var(--primary); margin-bottom: 5px; }
        .masonry-caption p { font-size: 0.9rem; color: var(--text-body); margin-bottom: 0; line-height: 1.4; }",
    $content
);

file_put_contents($file, $content);
echo "Masonry styles thoroughly fixed!\n";
