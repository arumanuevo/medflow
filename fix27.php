<?php
$c = file_get_contents('resources/views/superadmin/users.blade.php');
$c = str_replace('<button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>', '<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>', $c);
file_put_contents('resources/views/superadmin/users.blade.php', $c);
echo "Fixed cross\n";
