<?php
$file = 'k:\desarrollo\medflow\resources\views\help\index.blade.php';
$content = file_get_contents($file);

$search = <<<EOD
                                    <i class="bi bi-hdd-network me-1 text-success"></i> ¿Cómo tomar mediciones?</a>
                            </li>
                        </ul>
EOD;

$replace = <<<EOD
                                    <i class="bi bi-hdd-network me-1 text-success"></i> ¿Cómo tomar mediciones?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Reemplazo de un Medidor Roto"
                                    data-content="Al registrar una nueva medición desde 'Tomar Mediciones', si el equipo físico fue reemplazado, activa el botón '¿Se reemplazó este medidor físico por uno nuevo?'. Aparecerán dos opciones para eludir el error de 'Consumo Negativo'. Opción 1 (Reseteo Simple): Ignora el consumo de ese periodo y en su lugar inicia una tarifa cero a partir del nuevo aparato. Opción 2 (Cierre Exacto): Te pedirá la última lectura antes de desconectar el equipo viejo y el arranque del nuevo equipo, sumando los gastos para que factures con precisión absoluta el cruce sin alterar los gráficos históricos ni regalar el consumo mensual."
                                    data-steps="Disponible al tomar una nueva lectura manual desde &lt;strong&gt;Tomar Mediciones (Paso a Paso)&lt;/strong&gt; marcando la casilla desplegable.">
                                    <i class="bi bi-hdd-network me-1 text-success"></i> ¿Qué hago si debo cambiar un medidor?</a>
                            </li>
                        </ul>
EOD;

if (strpos($content, "¿Qué hago si debo cambiar un medidor?") === false) {
    if (strpos($content, trim(explode("\n", $search)[0])) !== false) {
        file_put_contents($file, str_replace($search, $replace, $content));
        echo "Done help patch\n";
    } else {
        echo "Search string not found\n";
    }
} else {
    echo "Help already patched\n";
}
