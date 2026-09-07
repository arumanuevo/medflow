<?php
$file = 'k:\desarrollo\medflow\resources\views\consumptions\index.blade.php';
$content = file_get_contents($file);

$headerReplace = '<tr>
<th class="sortable" style="cursor:pointer;" data-sort="sensor">Sensor <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
<th class="sortable" style="cursor:pointer;" data-sort="identifier">Identificador <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
<th>Tipo</th>
<th class="sortable" style="cursor:pointer;" data-sort="group">Grupo <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
<th class="sortable" style="cursor:pointer;" data-sort="value">Consumo Total <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
<th>Unidad</th>
<th class="sortable" style="cursor:pointer;" data-sort="cost">Costo ($) <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
<th class="sortable" style="cursor:pointer;" data-sort="period_end">Período <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
<th class="sortable" style="cursor:pointer;" data-sort="days_between">Días <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
<th class="sortable" style="cursor:pointer;" data-sort="daily_average">Prom. Diario <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
<th style="width: 100px;">Acciones</th>
</tr>';

$content = preg_replace('/<tr>\s*<th>Sensor<\/th>\s*<th>Identificador<\/th>\s*<th>Tipo<\/th>\s*<th>Grupo<\/th>\s*<th>Consumo Total<\/th>\s*<th>Unidad<\/th>\s*<th>Costo \(\$\)<\/th>[^<]+<th>[^<]+<\/th>[^<]+<th>[^<]+<\/th>[^<]+<th>[^<]+<\/th>\s*<th style="width: 100px;">Acciones<\/th>\s*<\/tr>/i', $headerReplace, $content);

file_put_contents($file, $content);
echo "REGEX replacement done!\n";
