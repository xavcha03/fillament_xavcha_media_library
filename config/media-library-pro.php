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
];
