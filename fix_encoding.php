<?php
$content = "storePassword=aruma2026\nkeyPassword=aruma2026\nkeyAlias=upload\nstoreFile=upload-keystore.jks\n";
file_put_contents('k:\desarrollo\medflow\mobile-app\android\key.properties', $content);
echo "Fixed key.properties encoding\n";
