# Guide d'installation de MediaLibraryPro

Ce guide explique comment installer et utiliser le package `xavcha/fillament-xavcha-media-library` dans vos projets Laravel.

## 📦 Installation

### Option 1 : Dépôt Git privé (Recommandé pour production)

#### 1. Ajouter le dépôt dans composer.json

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:xavcha03/fillament_xavcha_media_library.git"
        }
    ],
    "require": {
        "xavcha/fillament-xavcha-media-library": "^1.0"
    }
}
```

#### 2. Installer le package

```bash
composer require xavcha/fillament-xavcha-media-library
```

### Option 2 : Path Repository (Développement local)

Si vous développez le package localement :

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../media-library-pro-package",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "xavcha/fillament-xavcha-media-library": "@dev"
    }
}
```

Puis :
```bash
composer update xavcha/fillament-xavcha-media-library
```

### Option 3 : Packagist (si vous publiez publiquement)

```bash
composer require xavcha/fillament-xavcha-media-library
```

## ⚙️ Configuration

### 1. Publier les migrations

```bash
php artisan vendor:publish --tag=media-library-pro-migrations
php artisan migrate
```

### 2. Publier la configuration (optionnel)

```bash
php artisan vendor:publish --tag=media-library-pro-config
```

### 3. Créer le lien symbolique du storage

```bash
php artisan storage:link
```

## 🚀 Utilisation

### Dans vos modèles

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Xavier\MediaLibraryPro\Traits\HasMediaFiles;

class Article extends Model
{
    use HasMediaFiles;
    
    protected function registerMediaCollections(): array
    {
        return [
            'images' => [
                'singleFile' => true,
                'acceptedMimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
            ],
        ];
    }
}
```

### Dans vos formulaires Filament

```php
use Xavier\MediaLibraryPro\Forms\Components\MediaPickerUnified;

MediaPickerUnified::make('image_ids')
    ->label('Image principale')
    ->collection('images')
    ->acceptedFileTypes(['image/*'])
    ->multiple(false)
    ->showUpload(true)
    ->showLibrary(true)
```

### Mise à jour du package

```bash
composer update xavcha/fillament-xavcha-media-library
```

## 📝 Notes importantes

- Le package est automatiquement découvert par Laravel grâce au service provider
- Les migrations sont publiées dans `database/migrations/`
- La configuration est optionnelle (le package a des valeurs par défaut)
- Le package utilise le namespace `Xavier\MediaLibraryPro\`

