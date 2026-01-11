<?php

// Test if the territories section can be parsed in isolation
$testArray = [
    'settings' => [
        'title' => 'Settings',
        'pipelines' => [
            'index' => [
                'title' => 'Pipelines',
            ],
        ],

        'territory-rules' => [
            'index' => [
                'datagrid' => [
                    'id' => 'ID',
                ],
            ],
        ],

        'territories' => [
            'index' => [
                'title' => 'Territories',
                'create-btn' => 'Create Territory',
            ],
        ],
    ],
];

echo "Test array created successfully!\n";
echo "Territories exists: " . (isset($testArray['settings']['territories']) ? 'YES' : 'NO') . "\n";
echo "Territory-rules exists: " . (isset($testArray['settings']['territory-rules']) ? 'YES' : 'NO') . "\n";

if (isset($testArray['settings']['territories'])) {
    echo "Territories title: " . $testArray['settings']['territories']['index']['title'] . "\n";
}
