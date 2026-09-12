<?php
$flyerPath = 'k:\desarrollo\medflow\resources\views\flyer.blade.php';
$flyerContent = file_get_contents($flyerPath);

$pattern = '/<h4><i class="bi bi-info-square text-primary me-2"><\/i>\s*Centro de Ayuda<\/h4>\s*<p>.*?<\/p>/s';
$replacement = '<h4><i class="bi bi-robot text-primary me-2"></i> Soporte C-Level (IA)</h4>
                          <p>Los suscriptores Premium desbloquean a <b>Flowy</b>, un agente de Inteligencia Artificial integrado 24/7 para consultas operativas al instante sin tickets de espera.</p>';

$flyerContent = preg_replace($pattern, $replacement, $flyerContent);
file_put_contents($flyerPath, $flyerContent);
echo "Flyer update forced with Regex!\n";
