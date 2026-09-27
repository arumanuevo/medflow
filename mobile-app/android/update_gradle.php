<?php
$buildGradle = 'k:\desarrollo\medflow\mobile-app\android\app\build.gradle.kts';
$content = file_get_contents($buildGradle);

// 1. Add properties loading at the top
$imports = <<<EOT
import java.io.FileInputStream
import java.util.Properties

val keystorePropertiesFile = rootProject.file("key.properties")
val keystoreProperties = Properties()
if (keystorePropertiesFile.exists()) {
    keystoreProperties.load(FileInputStream(keystorePropertiesFile))
}
EOT;

if (strpos($content, 'keystorePropertiesFile') === false) {
    if (strpos($content, 'plugins {') !== false) {
        $content = str_replace('plugins {', $imports . "plugins {", $content);
    }
}

// 2. Add signingConfigs block inside android {
$signingConfigBlock = <<<EOT
    signingConfigs {
        create("release") {
            keyAlias = keystoreProperties["keyAlias"] as String?
            keyPassword = keystoreProperties["keyPassword"] as String?
            storeFile = keystoreProperties["storeFile"]?.let { file(it as String) }
            storePassword = keystoreProperties["storePassword"] as String?
        }
    }

    buildTypes {
EOT;

if (strpos($content, 'create("release")') === false) {
    $content = str_replace('buildTypes {', $signingConfigBlock, $content);
}

// 3. Change release signingConfig to point to "release"
$oldRelease = <<<EOT
        release {
            // TODO: Add your own signing config for the release build.
            // Signing with the debug keys for now, so `flutter run --release` works.
            signingConfig = signingConfigs.getByName("debug")
        }
EOT;

$newRelease = <<<EOT
        getByName("release") {
            signingConfig = signingConfigs.getByName("release")
            // shrinkResources = true
            // minifyEnabled = true
        }
EOT;

$content = str_replace($oldRelease, $newRelease, $content);
file_put_contents($buildGradle, $content);
echo "build.gradle.kts updated!\n";
