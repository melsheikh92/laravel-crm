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
        'icon' => 'icon-setting',
        'info' => 'Manage global settings for the customer portal.',
    ],
    [
        'key' => 'portal.general.settings',
        'name' => 'Settings',
        'info' => 'Configure portal name, logo, and visual appearance.',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'portal_name',
                'title' => 'Portal Name',
                'type' => 'text',
                'default' => 'Customer Portal',
            ],
            [
                'name' => 'logo',
                'title' => 'Logo',
                'type' => 'image',
            ],
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
