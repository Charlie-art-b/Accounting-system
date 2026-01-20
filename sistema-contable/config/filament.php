<?php

return [
    'default' => env('FILAMENT_THEME', 'default'),

    'panels' => [
        'default' => [
            'path' => 'admin',
            'brand' => 'Sistema Contable',
            'favicon' => null,
            'navigation_groups' => [
                'default',
            ],
        ],
    ],

    // Deshabilitar la validación de intl
    'locale' => 'es',
];
