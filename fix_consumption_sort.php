<?php
$file = 'k:\desarrollo\medflow\app\Http\Controllers\Api\ConsumptionController.php';
$content = file_get_contents($file);

$target = "// Ordenar por fecha de fin descendente
        \$allConsumptions = \$allConsumptions->sortByDesc('period_end')->values();";

$replacement = "// Lógica de ordenamiento dinámico
        \$sortBy = \$request->query('sort_by', 'period_end');
        \$sortDir = \$request->query('sort_dir', 'desc');

        \$allConsumptions = \$allConsumptions->sortBy(function(\$consumption) use (\$sortBy) {
            if (\$sortBy === 'sensor') return strtolower(\$consumption['sensor']['name'] ?? '');
            if (\$sortBy === 'identifier') return strtolower(\$consumption['sensor']['identifier'] ?? '');
            if (\$sortBy === 'group') return strtolower(\$consumption['sensor']['group']['name'] ?? '');
            if (\$sortBy === 'type') return strtolower(\$consumption['sensor']['group']['template']['type'] ?? '');
            return \$consumption[\$sortBy] ?? null;
        }, SORT_REGULAR, \$sortDir === 'desc')->values();";

$content = str_replace($target, $replacement, $content);
file_put_contents($file, $content);
echo "Controller PHP sorting updated.\n";
