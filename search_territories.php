<?php

$array = include 'packages/Webkul/Admin/src/Resources/lang/en/app.php';

echo "Searching for 'territories' key in entire array...\n\n";

function searchArray($arr, $path = '')
{
    foreach ($arr as $key => $value) {
        $currentPath = $path ? "$path.$key" : $key;

        if ($key === 'territories' && is_array($value) && isset($value['index'])) {
            echo "FOUND FULL TERRITORIES ARRAY at: $currentPath\n";
            echo "  Has 'index' key: " . (isset($value['index']) ? 'YES' : 'NO') . "\n";
            echo "  Has 'create' key: " . (isset($value['create']) ? 'YES' : 'NO') . "\n";
            echo "  Number of sub-keys: " . count($value) . "\n";
            return true;
        }

        if (is_array($value) && count($value) < 100) { // Don't recurse into huge arrays
            if (searchArray($value, $currentPath)) {
                return true;
            }
        }
    }
    return false;
}

if (!searchArray($array)) {
    echo "\nFull territories array NOT FOUND anywhere in the parsed array!\n";
    echo "\nThis confirms the array is not being parsed from the file.\n";
}
