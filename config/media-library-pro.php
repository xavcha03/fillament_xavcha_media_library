<?php

return [
    'storage' => [
        'disk' => 'public',
        'path' => 'media',
        'naming' => 'hash',
    ],
    'conversions' => [
        'enabled' => true,
        'driver' => 'gd',
        'presets' => [
            'thumb' => [
                'width' => 150,
                'height' => 150,
                'fit' => 'crop',
                'quality' => 85,
                'format' => 'jpg',
            ],
        ],
    ],
    'validation' => [
        'max_size' => 10240,
        'allowed_mime_types' => [],
    ],
    'folders' => [
        'enabled' => true,
        'max_depth' => 10, // Profondeur maximale des dossiers
        'max_name_length' => 255,
    ],
    'actions' => [
        'create_folder' => true,
        'delete' => true,
        'rename' => true,
        'download' => true,
        'move' => true,
        'upload' => true,
    ],
];
