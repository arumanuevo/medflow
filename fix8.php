<?php
$c = file_get_contents('resources/views/consumptions/index.blade.php');
$c = preg_replace('/<div class="col-md-2" style="min-width: 15rem;">\s*<label class="form-label">&nbsp;<\/label>\s*<div class="d-flex gap-2 w-100">/s', '<div class="col-md-4">' . "\n" . '                                <label class="form-label">&nbsp;</label>' . "\n" . '                                <div class="d-flex gap-2 w-100">', $c);
$c = preg_replace('/id="btnFilterCommunity"\s*style="white-space: nowrap;"/s', 'id="btnFilterCommunity" class="btn-sm" style="white-space: nowrap;"', $c);
$c = preg_replace('/id="applyFiltersBtn"/s', 'id="applyFiltersBtn" class="btn-sm"', $c);
$c = str_replace('id="clearFiltersBtn"', 'id="clearFiltersBtn" class="btn-sm"', $c);

file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo "Final button touchup applied\n";
