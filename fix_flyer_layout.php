<?php
$file = 'k:\desarrollo\medflow\resources\views\flyer.blade.php';
$content = file_get_contents($file);

// 1. Achicar el carrusel container
$carouselOld = ".carousel-container { max-width: 1400px; margin: 0 auto; border-radius: 30px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; }";
$carouselNew = ".carousel-container { max-width: 950px; margin: 0 auto; border: 6px solid #f8fafc; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(20, 20, 20, 0.35); overflow: hidden; }";
$content = str_replace($carouselOld, $carouselNew, $content);

// 2. Bordes resaltados para masonry y estilos de caption
$masonryCSSOld = ".masonry-item { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: 0.3s; }
        .masonry-item:hover { transform: scale(1.02); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); z-index: 2;}
        .masonry-item img { width: 100%; height: auto; display: block; border-bottom: 3px solid var(--accent);}";

$masonryCSSNew = ".masonry-item { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 15px -3px rgba(0,0,0,0.05); border: 2px solid #cbd5e1; transition: 0.3s; }
        .masonry-item:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 25px 30px -5px rgba(0,0,0,0.15); border-color: var(--accent); z-index: 2;}
        .masonry-item img { width: 100%; height: auto; display: block; border-bottom: 1px solid #e2e8f0; }
        .masonry-caption { padding: 15px; }
        .masonry-caption h4 { font-size: 1.15rem; font-weight: 700; color: var(--primary); margin-bottom: 5px; }
        .masonry-caption p { font-size: 0.9rem; color: var(--text-body); margin-bottom: 0; line-height: 1.4; }";
$content = str_replace($masonryCSSOld, $masonryCSSNew, $content);

// 3. Reemplazar el bloque masonry-grid entero con HTML interactivo y textos explicativos (Especialmente QR)
$masonryHtmlStartRegex = '/<div class="masonry-grid">.*?<\/div>\s*<\/div>\s*<\/section>/s';

$masonryHtmlNew = '<div class="masonry-grid">
                <div class="masonry-item">
                    <img src="{{ asset(\'flyer/grilla_qr.PNG\') }}" alt="Tokens QR">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-qr-code-scan text-primary me-2"></i> Tokens de Visor (QR)</h4>
                        <p>Brinda a cada usuario un Token o Código QR único para que puedan escanearlo y consultar la evolución de sus propios consumos desde su celular en todo momento, sin contraseñas.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset(\'flyer/plantillas.PNG\') }}" alt="Plantillas">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-braces text-primary me-2"></i> Plantillas Dinámicas</h4>
                        <p>Crea esquemas variables de toma (Agua, Energía, Presión) y obliga a tus inspectores a relevar exactly los campos que necesites.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset(\'flyer/backups.PNG\') }}" alt="Seguridad">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-shield-check text-primary me-2"></i> Backups Cifrados</h4>
                        <p>Descarga e importa copias de seguridad estáticas de toda la información de tus medidores con un flujo protegido.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset(\'flyer/sensores.PNG\') }}" alt="Inventario">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-router text-primary me-2"></i> Inventario Táctico</h4>
                        <p>Tu flota en terreno unificada. Busca, filtra y monitoriza qué inspectores están atendiendo qué líneas.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset(\'flyer/detalle_consumo.PNG\') }}" alt="Liquidador">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-receipt-cutoff text-primary me-2"></i> Detalles de Liquidación</h4>
                        <p>Transparencia fotográfica por cada medidor para respaldar los cálculos financieros ante reclamos.</p>
                    </div>
                </div>
                <div class="masonry-item">
                    <img src="{{ asset(\'flyer/centro_ayuda.PNG\') }}" alt="Base de Conocimiento">
                    <div class="masonry-caption">
                        <h4><i class="bi bi-info-square text-primary me-2"></i> Centro de Ayuda</h4>
                        <p>Manuales y vías de contacto de soporte listas para asistir a cualquier nivel gerencial u operario.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>';

$content = preg_replace($masonryHtmlStartRegex, $masonryHtmlNew, $content);

file_put_contents($file, $content);
echo "Flyer layout tweaked!\n";
