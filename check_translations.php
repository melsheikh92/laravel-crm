<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Load the translation file directly
$translations = include 'packages/Webkul/Admin/src/Resources/lang/en/app.php';

echo "Keys in settings array:\n";
if (isset($translations['settings'])) {
    $keys = array_keys($translations['settings']);
    foreach ($keys as $key) {
        echo "  - $key\n";
    }

    echo "\nChecking for territories:\n";
    echo "  territories exists: " . (isset($translations['settings']['territories']) ? 'YES' : 'NO') . "\n";
    echo "  territory-rules exists: " . (isset($translations['settings']['territory-rules']) ? 'YES' : 'NO') . "\n";
    echo "  territory-assignments exists: " . (isset($translations['settings']['territory-assignments']) ? 'YES' : 'NO') . "\n";
} else {
    echo "Settings key not found!\n";
}
