<?php
$c = file_get_contents('temp_index.blade.php');

// First, get the clean base file
// Remove EVERY line matching the bad script injection block
$c = preg_replace("/\\$\\('#btnShareAnalysis'\\)\\.click\\(\\s*function\\s*\\([\\s\\S]*?\\)\\s*\\{.*?\\s*\\}\\);/s", '', $c);

// Also sometimes it gets corrupted like `});});`
// I'll use a very precise string operation.
$pos = strpos($c, 'function resetFilters() {');
if ($pos !== false) {
    // The good code ends around:
    /*
        function renderPagination(meta) {
            ...
        }
    */
    // There shouldn't be anything after `function renderPagination(meta) { ... }` block except `</script>`
    // I know that the corrupted code in JS block is all about `btnShareAnalysis`
}

// Brutal stripping of the duplicate string
while (($p = strpos($c, "$('#btnShareAnalysis').click")) !== false) {
    $end = strpos($c, '});', $p);
    if ($end !== false) {
        $c = substr_replace($c, '', $p, $end - $p + 3);
    }
}

// Clean up hanging `});` tags that were left by the multiple injections
$c = preg_replace('/(\\}\);\s*)+\<\/script\>/s', "});\n    </script>", $c);

// Also remove `});}) return;` or other weird syntax the user introduced in their commit
$c = preg_replace('/\\}\\);\\}\\) return;/s', '});', $c);

file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo 'Removed loops';
