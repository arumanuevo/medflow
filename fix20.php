<?php
$c = file_get_contents('resources/views/bulk-measurements/select-sensors.blade.php');

$headers = [
    '<th class="text-left">Nombre</th>' => '<th class="text-left sortable" style="cursor:pointer" title="Ordenar por Nombre">Nombre <i class="bi bi-arrow-down-up text-muted small ms-1"></i></th>',
    '<th>Identificador</th>' => '<th class="sortable" style="cursor:pointer" title="Ordenar por Identificador">Identificador <i class="bi bi-arrow-down-up text-muted small ms-1"></i></th>',
    '<th>Grupo</th>' => '<th class="sortable" style="cursor:pointer" title="Ordenar por Grupo">Grupo <i class="bi bi-arrow-down-up text-muted small ms-1"></i></th>',
    '<th>Última Medición</th>' => '<th class="sortable" style="cursor:pointer" title="Ordenar por Fecha">Última Medición <i class="bi bi-arrow-down-up text-muted small ms-1"></i></th>',
    '<th>Valor</th>' => '<th class="sortable" style="cursor:pointer" title="Ordenar por Valor">Valor <i class="bi bi-arrow-down-up text-muted small ms-1"></i></th>',
    '<th>Estado</th>' => '<th class="sortable" style="cursor:pointer" title="Ordenar por Estado">Estado <i class="bi bi-arrow-down-up text-muted small ms-1"></i></th>'
];

foreach ($headers as $old => $new) {
    if (strpos($old, 'Última') !== false) {
        // Fallback for character encoding if any
        $c = preg_replace('/<th>Ãšltima MediciÃ³n<\/th>/u', $new, $c);
        $c = preg_replace('/<th>Última Medición<\/th>/u', $new, $c);
        $c = preg_replace('/<th>[^<]*ltima Medici[^<]*<\/th>/', $new, $c);
    } else {
        $c = str_replace($old, $new, $c);
    }
}

$script = <<<'JS'
            // Sorting Logic
            $('th.sortable').click(function(){
                var table = $(this).parents('table').eq(0);
                var tbody = table.find('tbody');
                
                // Get visible rows only so we don't mess up filtered rows
                var rows = tbody.find('tr').toArray().sort(comparer($(this).index()));
                
                if (this.asc === undefined) { this.asc = true; } else { this.asc = !this.asc; }
                if (!this.asc) { rows = rows.reverse(); }
                
                for (var i = 0; i < rows.length; i++) {
                    tbody.append(rows[i]);
                }
                
                // Update icons
                $('th.sortable i').removeClass('bi-sort-up bi-sort-down text-dark').addClass('bi-arrow-down-up text-muted');
                $(this).find('i').removeClass('bi-arrow-down-up text-muted').addClass(this.asc ? 'bi-sort-up text-dark' : 'bi-sort-down text-dark');
            });
            
            function comparer(index) {
                return function(a, b) {
                    var valA = getCellValue(a, index), valB = getCellValue(b, index);
                    // Date parsing for "Última Medición" (like '04/05/2026')
                    if (valA.match(/^\d{2}\/\d{2}\/\d{4}/) && valB.match(/^\d{2}\/\d{2}\/\d{4}/)) {
                        var dA = valA.split('/').reverse().join('');
                        var dB = valB.split('/').reverse().join('');
                        return dA.localeCompare(dB);
                    }
                    // Number parsing (for "Valor")
                    var numA = parseFloat(valA.replace(/[^\d.-]/g, ''));
                    var numB = parseFloat(valB.replace(/[^\d.-]/g, ''));
                    if(!isNaN(numA) && !isNaN(numB) && valA.match(/^[0-9.,\s]+$/)) {
                        return numA - numB;
                    }
                    return valA.toString().localeCompare(valB);
                }
            }
            
            function getCellValue(row, index){
                return $(row).children('td').eq(index).text().trim();
            }
JS;

if (strpos($c, "let selectionOrder = [];") !== false && strpos($c, "$('th.sortable').click") === false) {
    $c = str_replace("let selectionOrder = [];", $script . "\n\n            let selectionOrder = [];", $c);
    file_put_contents('resources/views/bulk-measurements/select-sensors.blade.php', $c);
    echo "Injected sort algorithm\n";
} else {
    echo "Could not inject or already injected\n";
}
