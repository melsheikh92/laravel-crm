<?php

// Direct array check - load the file and see what we get
$array = include 'packages/Webkul/Admin/src/Resources/lang/en/app.php';

echo "Total top-level keys: " . count($array) . "\n";
echo "\nTop-level keys:\n";
foreach (array_keys($array) as $key) {
    echo "  - $key\n";
}

if (isset($array['settings'])) {
    echo "\nSettings array has " . count($array['settings']) . " keys\n";

    // Check what comes after 'pipelines'
    $settingsKeys = array_keys($array['settings']);
    $pipelinesIndex = array_search('pipelines', $settingsKeys);

    if ($pipelinesIndex !== false) {
        echo "\nKeys around 'pipelines' (index $pipelinesIndex):\n";
        for ($i = max(0, $pipelinesIndex - 2); $i < min(count($settingsKeys), $pipelinesIndex + 5); $i++) {
            $marker = ($i === $pipelinesIndex) ? ' <-- pipelines' : '';
            echo "  [$i] {$settingsKeys[$i]}$marker\n";
        }
    }
}
