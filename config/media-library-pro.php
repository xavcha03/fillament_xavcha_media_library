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
    'optimization' => [
        'enabled' => true,
        'auto_optimize' => true, // Optimisation automatique à l'upload
        'max_width' => 1920, // Largeur maximale (null = pas de limite)
        'max_height' => 1920, // Hauteur maximale (null = pas de limite)
        'quality' => 80, // Qualité JPEG/WebP (1-100) - 80 = bon compromis qualité/taille pour le web (75-80 recommandé)
        'convert_to_webp' => true, // Convertir automatiquement en WebP (recommandé : 30-50% de réduction supplémentaire)
        'preserve_original' => false, // Conserver l'original si conversion WebP
        'queue' => false, // Traitement en queue (asynchrone)
    ],
];
