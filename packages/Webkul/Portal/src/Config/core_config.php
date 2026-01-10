<?php

return [
    [
        'key' => 'portal',
        'name' => 'Customer Portal',
        'sort' => 2,
    ],
    [
        'key' => 'portal.general',
        'name' => 'General',
        'sort' => 1,
    ],
    [
        'key' => 'portal.general.settings',
        'name' => 'Settings',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'primary_color',
                'title' => 'Primary Color',
                'type' => 'color',
                'default' => '#4F46E5',
            ],
            [
                'name' => 'background_color',
                'title' => 'Background Color',
                'type' => 'color',
                'default' => '#F8FAFC',
            ],
        ],
    ],
];
