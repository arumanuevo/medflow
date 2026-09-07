<?php
$file = 'k:\desarrollo\medflow\resources\views\consumptions\index.blade.php';
$content = file_get_contents($file);

// Replace Table Headers to add sorting capabilities
$headerSearch = "<tr>
                                        <th>Sensor</th>
                                        <th>Identificador</th>
                                        <th>Tipo</th>
                                        <th>Grupo</th>
                                        <th>Consumo Total</th>
                                        <th>Unidad</th>
                                        <th>Costo ($)</th>
                                        <th>Período</th>
                                        <th>Días transcurridos</th>
                                        <th>Promedio Diario</th>
                                        <th style=\"width: 100px;\">Acciones</th>
                                    </tr>";

$headerReplace = "<tr id=\"sortableHeaders\">
                                        <th class=\"sortable cursor-pointer\" data-sort=\"sensor\">Sensor <i class=\"bi bi-arrow-down-up text-muted ms-1\"></i></th>
                                        <th class=\"sortable cursor-pointer\" data-sort=\"identifier\">Identificador <i class=\"bi bi-arrow-down-up text-muted ms-1\"></i></th>
                                        <th>Tipo</th>
                                        <th class=\"sortable cursor-pointer\" data-sort=\"group\">Grupo <i class=\"bi bi-arrow-down-up text-muted ms-1\"></i></th>
                                        <th class=\"sortable cursor-pointer\" data-sort=\"value\">Consumo Total <i class=\"bi bi-arrow-down-up text-muted ms-1\"></i></th>
                                        <th>Unidad</th>
                                        <th class=\"sortable cursor-pointer\" data-sort=\"cost\">Costo ($) <i class=\"bi bi-arrow-down-up text-muted ms-1\"></i></th>
                                        <th class=\"sortable cursor-pointer\" data-sort=\"period_end\">Período <i class=\"bi bi-arrow-down-up text-muted ms-1\"></i></th>
                                        <th class=\"sortable cursor-pointer\" data-sort=\"days_between\">Días <i class=\"bi bi-arrow-down-up text-muted ms-1\"></i></th>
                                        <th class=\"sortable cursor-pointer\" data-sort=\"daily_average\">Prom. Diario <i class=\"bi bi-arrow-down-up text-muted ms-1\"></i></th>
                                        <th style=\"width: 100px;\">Acciones</th>
                                    </tr>";

// Support both UTF-8 or raw ANSI encodings if needed (handling special characters from the file system)
if (!str_contains($content, "<th>Período</th>")) {
    $headerSearch = "<tr>
                                        <th>Sensor</th>
                                        <th>Identificador</th>
                                        <th>Tipo</th>
                                        <th>Grupo</th>
                                        <th>Consumo Total</th>
                                        <th>Unidad</th>
                                        <th>Costo ($)</th>
                                        <th>PerÃ­odo</th>
                                        <th>DÃ­as transcurridos</th>
                                        <th>Promedio Diario</th>
                                        <th style=\"width: 100px;\">Acciones</th>
                                    </tr>";
}

$content = str_replace($headerSearch, $headerReplace, $content);


// Add global variables to script
$scriptSearch = "let currentPage = 1;";
$scriptReplace = "let currentPage = 1;
        let currentSortBy = 'period_end';
        let currentSortDir = 'desc';";
$content = str_replace($scriptSearch, $scriptReplace, $content);

// Update loadConsumptions to send params
$loadParamsSearch = "const params = {
                page: currentPage,
                per_page: 15
            };";
$loadParamsReplace = "const params = {
                page: currentPage,
                per_page: 15,
                sort_by: currentSortBy,
                sort_dir: currentSortDir
            };";
$content = str_replace($loadParamsSearch, $loadParamsReplace, $content);

// Add event listener binding for sorting
$eventBindingSearch = "$('#calculateConsumption').click(calculateAllConsumptions);";
$eventBindingReplace = "$('#calculateConsumption').click(calculateAllConsumptions);
            
            // Lógica de ordenamiento
            $('.sortable').click(function() {
                const sortBy = $(this).data('sort');
                if (currentSortBy === sortBy) {
                    currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSortBy = sortBy;
                    currentSortDir = 'asc';
                }
                
                // Actualizar iconos
                $('.sortable i').removeClass('bi-sort-up bi-sort-down text-primary').addClass('bi-arrow-down-up text-muted');
                $(this).find('i').removeClass('bi-arrow-down-up text-muted')
                       .addClass(currentSortDir === 'asc' ? 'bi-sort-up' : 'bi-sort-down')
                       .addClass('text-primary');
                       
                loadConsumptions();
            });";
$content = str_replace($eventBindingSearch, $eventBindingReplace, $content);

file_put_contents($file, $content);
echo "Blade templates updated for sorting.\n";
