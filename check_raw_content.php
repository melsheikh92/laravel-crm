<?php

$array = include 'packages/Webkul/Admin/src/Resources/lang/en/app.php';

// Check the raw file content around the problematic area
$content = file_get_contents('packages/Webkul/Admin/src/Resources/lang/en/app.php');

// Find the position of 'pipelines' => [ in settings
$pos1 = strpos($content, "'pipelines' => [", 1000); // Start searching after line 1000
echo "Found 'pipelines' => [ at position: $pos1\n";

// Find what comes after the pipelines section closes
$searchStart = $pos1 + 1000; // Search 1000 chars after pipelines starts
$pos2 = strpos($content, "],\n\n        '", $searchStart);
echo "Found next section marker at position: $pos2\n";

if ($pos2) {
    // Extract 200 chars after the marker to see what the next key is
    $snippet = substr($content, $pos2, 200);
    echo "\nContent after pipelines closes:\n";
    echo $snippet . "\n";
}
