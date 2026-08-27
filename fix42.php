<?php
$c = file_get_contents('app/Services/SidebarService.php');
echo "Count of Ayuda in Sidebar: " . substr_count($c, 'Centro de Ayuda') . "\n";
$r = file_get_contents('routes/web.php');
echo "Count of /ayuda in Routes: " . substr_count($r, '/ayuda') . "\n";
