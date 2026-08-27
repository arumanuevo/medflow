<?php
$c = file_get_contents('resources/views/help/index.blade.php');
$c = str_replace("@extends('layouts.app')", "@extends('layouts.modern')", $c);
file_put_contents('resources/views/help/index.blade.php', $c);
echo "Changed layout extension.\n";
