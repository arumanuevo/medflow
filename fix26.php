<?php
$c = file_get_contents('resources/views/superadmin/users.blade.php');
$c = str_replace('data-bs-toggle="modal"', 'data-toggle="modal"', $c);
$c = str_replace('data-bs-target="#editPricesModal"', 'data-target="#editPricesModal"', $c);
$c = str_replace('data-bs-dismiss="modal"', 'data-dismiss="modal"', $c);
file_put_contents('resources/views/superadmin/users.blade.php', $c);
echo "Downgraded modal attributes to BS4\n";
