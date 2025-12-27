# Media Library Pro

[![Laravel](https://img.shields.io/badge/Laravel-12.x%2B-red.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-4.x-blue.svg)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Un package Laravel/Filament complet et moderne pour gérer les médias (images, vidéos, documents) avec support des conversions, collections, et intégration native Filament v4.

## ✨ Fonctionnalités

- 🎯 **Gestion complète des médias** : Images, vidéos, documents, archives
- 🔄 **Conversions d'images** : Génération automatique de thumbnails et variantes
- 📁 **Collections** : Organisation des médias par type ou usage
- 🎨 **Interface Filament native** : Composants intégrés pour Filament v4
- 🔒 **Sécurité** : Support des fichiers publics et privés
- 🚀 **Performance** : Optimisé pour les gros volumes
- 📦 **Réutilisable** : Un fichier peut être associé à plusieurs modèles (pas de duplication physique)
- 🎛️ **Configurable** : Configuration flexible et extensible avec API fluente
- 🔍 **Recherche et filtres** : Filtrage avancé dans la bibliothèque
- 📱 **Responsive** : Interface adaptée mobile et desktop
- 🖼️ **Aperçu avant upload** : Visualisation des fichiers avant l'upload
- ✅ **Validation intelligente** : Désactivation automatique du bouton valider si fichiers en attente
- 🔄 **Synchronisation automatique** : Retour automatique à la bibliothèque après upload
- 🗑️ **Suppression persistante** : Suppression avec croix rouge sauvegardée automatiquement

## 📋 Table des matières

- [Installation](#-installation)
- [Configuration](#️-configuration)
- [Démarrage rapide](#-démarrage-rapide)
- [Architecture](#-architecture)
- [Guide d'utilisation](#-guide-dutilisation)
  - [Trait HasMediaFiles](#trait-hasmediafiles)
  - [Composants Filament](#composants-filament)
  - [Services](#services)
  - [Conversions d'images](#conversions-dimages)
  - [Collections](#collections)
- [📖 Guide Complet d'Utilisation](./GUIDE_UTILISATION.md) - Guide détaillé pour MediaPickerUnified
- [🔧 Méthodes Fluentes](./METHODES_FLUENTES.md) - Liste complète des méthodes de configuration
- [API de référence](#-api-de-référence)
- [Exemples avancés](#-exemples-avancés)
- [Migration depuis Spatie](#-migration-depuis-spatie-media-library)
- [Sécurité](#-sécurité)
- [Dépannage](#-dépannage)
- [🎨 Guide de Styling](#-guide-de-styling) - **Important pour le développement**
- [📚 Documentation](#-documentation)
- [📋 Roadmap / TODO](#-roadmap--todo)
- [Contribution](#-contribution)

## 🚀 Installation

### Prérequis

- PHP 8.2 ou supérieur
- Laravel 12.x ou supérieur
- Filament 4.x (pour l'interface admin)
- Extension GD ou Intervention Image (pour les conversions)

### Installation via Composer

```bash
composer require xavcha/fillament-xavcha-media-library
```

### 1. Publier les migrations

```bash
php artisan vendor:publish --tag=media-library-pro-migrations
php artisan migrate
```

Cela créera les tables suivantes :
- `media_files` : Fichiers médias uniques
- `media_attachments` : Associations entre fichiers et modèles
- `media_conversions` : Conversions d'images générées

### 2. Publier la configuration (optionnel)

```bash
php artisan vendor:publish --tag=media-library-pro-config
```

### 3. Créer le lien symbolique du storage

```bash
php artisan storage:link
```

### 4. Installer les dépendances pour les conversions (optionnel)

Si vous souhaitez utiliser Intervention Image pour les conversions :

```bash
composer require intervention/image
```

## ⚙️ Configuration

Le fichier de configuration se trouve dans `config/media-library-pro.php` :

### Configuration du stockage

```php
'storage' => [
    'disk' => 'public',        // Disque Laravel ('local', 'public', 's3', etc.)
    'path' => 'media',         // Chemin de base dans le disque
    'naming' => 'hash',        // Stratégie: 'uuid', 'hash', 'date', 'original'
],
```

**Stratégies de nommage :**
- `uuid` : Génère un UUID unique pour chaque fichier
- `hash` : Génère un hash aléatoire (recommandé)
- `date` : Organise par date (YYYY/MM/DD)
- `original` : Conserve le nom original avec un suffixe

### Configuration des conversions

```php
'conversions' => [
    'enabled' => true,
    'driver' => 'intervention', // 'intervention' ou 'gd'
    'presets' => [
        'thumb' => [
            'width' => 150,
            'height' => 150,
            'fit' => 'crop',        // 'crop', 'contain', 'cover', 'fill'
            'quality' => 85,
            'format' => 'jpg',      // 'webp', 'jpg', 'png'
        ],
        'small' => [
            'width' => 300,
            'height' => null,       // null = proportionnel
            'fit' => 'contain',
            'quality' => 85,
            'format' => 'jpg',
        ],
        // ... autres presets
    ],
],
```

### Configuration de la validation

```php
'validation' => [
    'max_size' => 10240,           // KB (10MB par défaut)
    'allowed_mime_types' => [],    // Vide = tous les types autorisés
],
```

## 🏃 Démarrage rapide

### 1. Ajouter le trait à votre modèle

```php
<?php

namespace App\Models;

use Xavier\MediaLibraryPro\Traits\HasMediaFiles;
use Illuminate\Database\Eloquent\Model;

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

### 2. Utiliser dans un formulaire Filament

```php
use Xavier\MediaLibraryPro\Forms\Components\MediaPickerUnified;

MediaPickerUnified::make('image_ids')
    ->label('Image')
    ->collection('images')
    ->acceptedFileTypes(['image/*'])
    ->multiple(false)
    ->showUpload(true)
    ->showLibrary(true)
```

### 3. Afficher dans une vue Blade

```blade
@if($article->getFirstMediaFile('images'))
    <img src="{{ route('media-library-pro.serve', [
        'media' => $article->getFirstMediaFile('images')->mediaFile->uuid
    ]) }}" alt="{{ $article->title }}">
@endif
```

## 🏗️ Architecture

### Structure des modèles

```
MediaFile (fichier unique)
    ├── UUID unique
    ├── Métadonnées (taille, type, dimensions)
    └── Relations
        ├── attachments (MediaAttachment[]) → Associations avec modèles
        └── conversions (MediaConversion[]) → Conversions générées

MediaAttachment (association)
    ├── model_type + model_id → Modèle parent (polymorphique)
    ├── collection_name → Collection
    ├── order → Ordre dans la collection
    └── mediaFile → MediaFile associé

MediaConversion (conversion)
    ├── conversion_name → Nom du preset
    ├── Métadonnées (dimensions, qualité, format)
    └── mediaFile → MediaFile source
```

### Flux de données

```
Upload → MediaUploadService → MediaStorageService → MediaFile
                                              ↓
                                    MediaAttachment (association)
                                              ↓
                                    MediaConversion (si image)
```

### Avantages de cette architecture

1. **Pas de duplication** : Un fichier physique peut être associé à plusieurs modèles
2. **Traçabilité** : Chaque fichier a un UUID unique
3. **Flexibilité** : Collections et propriétés personnalisées par association
4. **Performance** : Conversions générées une seule fois, réutilisables

## 📖 Guide d'utilisation

### Trait HasMediaFiles

#### Méthodes principales

##### `addMediaFile(UploadedFile|string $file, string $collection = 'default', ?string $name = null, array $customProperties = [])`

Ajoute un fichier au modèle depuis un `UploadedFile` ou un chemin.

```php
// Depuis un UploadedFile
$article->addMediaFile($request->file('image'), 'images', 'featured-image');

// Depuis un chemin local
$article->addMediaFile('/path/to/image.jpg', 'images');
```

##### `addMediaFromUrl(string $url, string $collection = 'default', ?string $name = null, array $customProperties = [])`

Télécharge et ajoute un fichier depuis une URL.

```php
$article->addMediaFromUrl('https://example.com/image.jpg', 'images');
```

##### `addMediaFromPath(string $path, string $collection = 'default', ?string $name = null, array $customProperties = [])`

Ajoute un fichier depuis un chemin local.

```php
$article->addMediaFromPath('/tmp/image.jpg', 'images');
```

##### `getMediaFiles(?string $collection = null)`

Récupère tous les fichiers d'une collection (ou toutes les collections).

```php
// Tous les fichiers d'une collection
$images = $article->getMediaFiles('images');

// Tous les fichiers de toutes les collections
$allMedia = $article->getMediaFiles();
```

**Retourne :** `Collection<MediaAttachment>`

##### `getFirstMediaFile(?string $collection = null)`

Récupère le premier fichier d'une collection.

```php
$featuredImage = $article->getFirstMediaFile('images');
```

**Retourne :** `MediaAttachment|null`

##### `clearMediaCollection(string $collection)`

Supprime tous les fichiers d'une collection (détache les attachments, ne supprime pas les fichiers physiques).

```php
$article->clearMediaCollection('images');
```

##### `deleteMediaFile(MediaFile $mediaFile, string $collection = null)`

Supprime un fichier spécifique. Si le fichier n'est utilisé nulle part ailleurs, il est supprimé physiquement.

```php
$mediaFile = MediaFile::find(1);
$article->deleteMediaFile($mediaFile, 'images');
```

##### `attachMediaFile(MediaFile $mediaFile, string $collection = 'default', array $customProperties = [])`

Attache un MediaFile existant au modèle (réutilise le fichier sans duplication).

```php
$existingFile = MediaFile::find(1);
$article->attachMediaFile($existingFile, 'images');
```

### Composants Filament

#### MediaPickerUnified

Composant Filament unifié pour sélectionner et uploader des médias avec une interface moderne.

```php
use Xavier\MediaLibraryPro\Forms\Components\MediaPickerUnified;

MediaPickerUnified::make('image_ids')
    ->label('Image principale')
    ->collection('images')
    ->acceptedFileTypes(['image/*'])
    ->multiple(false)
    ->showUpload(true)
    ->showLibrary(true)
    ->conversion('thumb')      // Afficher une conversion par défaut
    ->maxFiles(1)
    ->minFiles(0)
    ->required()
```

##### Propriétés disponibles

Pour la liste complète des méthodes, consultez [METHODES_FLUENTES.md](./METHODES_FLUENTES.md).

| Méthode | Type | Description |
|---------|------|-------------|
| `collection(string $collection)` | string | Nom de la collection |
| `acceptedFileTypes(array $types)` | array | Types MIME acceptés (ex: `['image/*', 'video/*']`) |
| `multiple(bool $multiple)` | bool | Autoriser la sélection multiple |
| `single()` | - | Sélection unique (équivalent à `multiple(false)` + `maxFiles(1)`) |
| `limit(int $min, ?int $max)` | int, int\|null | Définit min et max en une seule méthode |
| `exactFiles(int $count)` | int | Nombre exact de fichiers requis |
| `showUpload(bool $show)` | bool | Afficher l'onglet upload |
| `showLibrary(bool $show)` | bool | Afficher l'onglet bibliothèque |
| `conversion(?string $conversion)` | string\|null | Conversion à afficher par défaut |
| `maxFiles(?int $max)` | int\|null | Nombre maximum de fichiers |
| `minFiles(int $min)` | int | Nombre minimum de fichiers |
| `maxFileSize(int\|null)` | int\|null | Taille maximale en KB |
| `allowReordering(bool)` | bool | Permettre la réorganisation (drag & drop) |
| `downloadable(bool)` | bool | Permettre le téléchargement depuis l'aperçu |

##### Utilisation dans les pages Create/Edit

**Page Create :**

```php
<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected array $selectedMediaIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['image_ids'])) {
            $selectedValue = $data['image_ids'];
            
            if (is_string($selectedValue)) {
                $decoded = json_decode($selectedValue, true);
                $selectedIds = is_array($decoded) ? $decoded : [$selectedValue];
            } else {
                $selectedIds = is_array($selectedValue) ? $selectedValue : [$selectedValue];
            }
            
            $selectedIds = array_filter($selectedIds, fn($id) => !empty($id));
            
            if (!empty($selectedIds)) {
                $this->selectedMediaIds = array_values($selectedIds);
                unset($data['image_ids']);
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if (!empty($this->selectedMediaIds ?? [])) {
            foreach ($this->selectedMediaIds as $mediaFileId) {
                $mediaFile = MediaFile::find($mediaFileId);
                if ($mediaFile) {
                    $this->record->attachMediaFile($mediaFile, 'images');
                }
            }
        }
    }
}
```

**Page Edit :**

```php
<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected array $selectedMediaIds = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record) {
            $attachments = $this->record->getMediaFiles('images');
            if ($attachments->isNotEmpty()) {
                $mediaFileIds = $attachments->map(function ($attachment) {
                    return $attachment->mediaFile->id;
                })->toArray();
                
                if (count($mediaFileIds) === 1) {
                    $data['image_ids'] = (string) $mediaFileIds[0];
                } else {
                    $data['image_ids'] = json_encode($mediaFileIds);
                }
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['image_ids'])) {
            $selectedValue = $data['image_ids'];
            
            if (is_string($selectedValue)) {
                $decoded = json_decode($selectedValue, true);
                $selectedIds = is_array($decoded) ? $decoded : [$selectedValue];
            } else {
                $selectedIds = is_array($selectedValue) ? $selectedValue : [$selectedValue];
            }
            
            $selectedIds = array_filter($selectedIds, fn($id) => !empty($id));
            
            if (!empty($selectedIds)) {
                $this->selectedMediaIds = array_values($selectedIds);
                unset($data['image_ids']);
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if (!empty($this->selectedMediaIds ?? [])) {
            $this->record->clearMediaCollection('images');
            
            foreach ($this->selectedMediaIds as $mediaFileId) {
                $mediaFile = MediaFile::find($mediaFileId);
                if ($mediaFile) {
                    $this->record->attachMediaFile($mediaFile, 'images');
                }
            }
        }
    }
}
```

> **📖 Pour un guide complet et détaillé** avec tous les exemples, consultez [GUIDE_UTILISATION.md](./GUIDE_UTILISATION.md)

##### Fonctionnalités Avancées

- **Aperçu avant Upload** : Les fichiers sélectionnés s'affichent en miniatures avant l'upload
- **Retour Automatique** : Après l'upload, retour automatique à l'onglet "Bibliothèque"
- **Validation Intelligente** : Le bouton "Valider" est désactivé si des fichiers sont en attente d'upload
- **Suppression Persistante** : La suppression avec la croix rouge est automatiquement sauvegardée
- **Filtrage par Collection** : Les médias sont automatiquement filtrés par la collection spécifiée
- **Pas de Duplication** : Les fichiers existants sont réutilisés, pas dupliqués physiquement

### Services

#### MediaStorageService

Gère le stockage physique des fichiers.

```php
use Xavier\MediaLibraryPro\Services\MediaStorageService;

$storageService = app(MediaStorageService::class);

// Stocker un fichier
$mediaFile = $storageService->store($uploadedFile, 'public', 'custom-name.jpg');

// Obtenir l'URL
$url = $storageService->getUrl($mediaFile);

// Obtenir le chemin physique
$path = $storageService->getPath($mediaFile);

// Supprimer un fichier
$storageService->delete($mediaFile);
```

#### MediaUploadService

Gère les uploads et la validation.

```php
use Xavier\MediaLibraryPro\Services\MediaUploadService;

$uploadService = app(MediaUploadService::class);

// Uploader un fichier
$mediaFile = $uploadService->upload($request->file('image'), [
    'name' => 'custom-name',
    'disk' => 'public',
]);

// Uploader depuis une URL
$mediaFile = $uploadService->uploadFromUrl('https://example.com/image.jpg', [
    'name' => 'downloaded-image',
]);

// Valider un fichier
$uploadService->validate($uploadedFile, [
    'max_size' => 5000, // KB
    'mime_types' => ['image/jpeg', 'image/png'],
]);
```

#### MediaConversionService

Gère les conversions d'images.

```php
use Xavier\MediaLibraryPro\Services\MediaConversionService;

$conversionService = app(MediaConversionService::class);

// Générer une conversion
$conversion = $conversionService->convert($mediaFile, 'thumb');

// Récupérer une conversion existante
$conversion = $conversionService->getConversion($mediaFile, 'thumb');

// Régénérer toutes les conversions
$conversionService->regenerate($mediaFile);

// Supprimer une conversion
$conversionService->delete($mediaFile, 'thumb');
```

### Conversions d'images

#### Configuration des presets

Dans `config/media-library-pro.php` :

```php
'conversions' => [
    'presets' => [
        'thumb' => [
            'width' => 150,
            'height' => 150,
            'fit' => 'crop',        // 'crop', 'contain', 'cover', 'fill'
            'quality' => 80,
            'format' => 'webp',     // 'webp', 'jpg', 'png'
        ],
        'medium' => [
            'width' => 800,
            'height' => null,       // null = proportionnel
            'fit' => 'contain',
            'quality' => 90,
            'format' => 'webp',
        ],
    ],
],
```

#### Options de fit

- `crop` : Recadre l'image pour remplir exactement les dimensions
- `contain` : Redimensionne en conservant les proportions, peut laisser des espaces
- `cover` : Redimensionne pour couvrir toute la zone, peut couper
- `fill` : Étire l'image pour remplir exactement les dimensions

#### Utilisation

```php
// Générer une conversion
$conversion = $mediaFile->generateConversion('thumb');

// Obtenir l'URL d'une conversion
$thumbUrl = route('media-library-pro.conversion', [
    'media' => $mediaFile->uuid,
    'conversion' => 'thumb'
]);

// Ou via MediaAttachment
$attachment = $article->getFirstMediaFile('images');
$thumbUrl = $attachment->getConversionUrl('thumb');
```

### Collections

Les collections permettent d'organiser les médias par type ou usage.

#### Définir des collections

```php
protected function registerMediaCollections(): array
{
    return [
        'featured' => [
            'singleFile' => true,
            'acceptedMimeTypes' => ['image/jpeg', 'image/png'],
        ],
        'gallery' => [
            'singleFile' => false,
            'acceptedMimeTypes' => ['image/*'],
        ],
        'documents' => [
            'singleFile' => false,
            'acceptedMimeTypes' => ['application/pdf', 'application/msword'],
        ],
    ];
}
```

#### Utilisation

```php
// Ajouter à une collection spécifique
$article->addMediaFile($file, 'featured');

// Récupérer les fichiers d'une collection
$galleryImages = $article->getMediaFiles('gallery');

// Vider une collection
$article->clearMediaCollection('gallery');
```

## 📚 API de référence

### Modèle MediaFile

#### Propriétés

```php
$mediaFile->uuid              // UUID unique (string)
$mediaFile->file_name         // Nom original (string)
$mediaFile->stored_name       // Nom de stockage (string)
$mediaFile->disk              // Disque de stockage (string)
$mediaFile->path              // Chemin relatif (string)
$mediaFile->mime_type         // Type MIME (string)
$mediaFile->size              // Taille en octets (int)
$mediaFile->width             // Largeur pour images (int|null)
$mediaFile->height            // Hauteur pour images (int|null)
$mediaFile->duration          // Durée pour vidéos (int|null)
$mediaFile->metadata          // Métadonnées (array)
$mediaFile->alt_text          // Texte alternatif (string|null)
$mediaFile->description       // Description (string|null)
$mediaFile->is_public         // Public ou privé (bool)
$mediaFile->created_at        // Date de création
$mediaFile->updated_at        // Date de mise à jour
```

#### Méthodes

```php
// URLs et chemins
$mediaFile->getUrl()                          // URL publique (string)
$mediaFile->getPath()                         // Chemin physique (string)
$mediaFile->getStorageDisk()                  // Instance Storage (Filesystem)

// Informations
$mediaFile->getFormattedSize()                // Taille formatée (ex: "2.5 MB")
$mediaFile->isImage()                         // Est une image ? (bool)
$mediaFile->isVideo()                         // Est une vidéo ? (bool)
$mediaFile->isAudio()                         // Est un audio ? (bool)
$mediaFile->isDocument()                      // Est un document ? (bool)

// Conversions
$mediaFile->generateConversion($name)         // Générer une conversion (MediaConversion)
$mediaFile->getConversionUrl($name)           // URL d'une conversion (string|null)
```

#### Relations

```php
$mediaFile->attachments                       // Collection<MediaAttachment>
$mediaFile->conversions                       // Collection<MediaConversion>
```

### Modèle MediaAttachment

#### Propriétés

```php
$attachment->collection_name  // Nom de la collection (string)
$attachment->order            // Ordre dans la collection (int)
$attachment->custom_properties // Propriétés personnalisées (array)
$attachment->is_primary       // Est le fichier principal ? (bool)
```

#### Relations

```php
$attachment->mediaFile        // MediaFile
$attachment->model            // Modèle parent (polymorphique)
```

#### Méthodes

```php
$attachment->getUrl()                         // URL du fichier (string)
$attachment->getConversionUrl($name)          // URL d'une conversion (string|null)
```

### Modèle MediaConversion

#### Propriétés

```php
$conversion->conversion_name  // Nom du preset (string)
$conversion->file_name        // Nom du fichier (string)
$conversion->disk             // Disque de stockage (string)
$conversion->path             // Chemin relatif (string)
$conversion->width            // Largeur (int)
$conversion->height           // Hauteur (int)
$conversion->size             // Taille en octets (int)
$conversion->quality          // Qualité (int)
$conversion->format           // Format (string)
```

#### Relations

```php
$conversion->mediaFile        // MediaFile source
```

#### Méthodes

```php
$conversion->getUrl()                         // URL de la conversion (string)
$conversion->getPath()                        // Chemin physique (string)
```

## 💡 Exemples avancés

### Accessor pour l'image principale

```php
class Article extends Model
{
    use HasMediaFiles;
    
    public function getImageAttribute()
    {
        $attachment = $this->getFirstMediaFile('images');
        if ($attachment && $attachment->mediaFile) {
            return route('media-library-pro.serve', [
                'media' => $attachment->mediaFile->uuid
            ]);
        }
        return null;
    }
    
    public function getThumbnailAttribute()
    {
        $attachment = $this->getFirstMediaFile('images');
        if ($attachment) {
            return $attachment->getConversionUrl('thumb');
        }
        return null;
    }
}
```

### Upload multiple avec validation

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'images.*' => 'required|image|max:2048',
    ]);
    
    $article = Article::create($request->only(['title', 'content']));
    
    foreach ($request->file('images') as $file) {
        $article->addMediaFile($file, 'gallery');
    }
    
    return redirect()->route('articles.index');
}
```

### Utilisation dans les vues Blade

```blade
{{-- Afficher l'image principale --}}
@if($article->getFirstMediaFile('images'))
    @php
        $attachment = $article->getFirstMediaFile('images');
        $imageUrl = route('media-library-pro.serve', [
            'media' => $attachment->mediaFile->uuid
        ]);
    @endphp
    <img src="{{ $imageUrl }}" 
         alt="{{ $article->title }}"
         loading="lazy">
@endif

{{-- Afficher une galerie --}}
<div class="gallery">
    @foreach($article->getMediaFiles('gallery') as $attachment)
        <img src="{{ route('media-library-pro.serve', [
            'media' => $attachment->mediaFile->uuid
        ]) }}" 
             alt="{{ $attachment->mediaFile->file_name }}"
             loading="lazy">
    @endforeach
</div>

{{-- Afficher une conversion --}}
@if($article->getFirstMediaFile('images'))
    <img src="{{ $article->getFirstMediaFile('images')->getConversionUrl('thumb') }}" 
         alt="Thumbnail">
@endif
```

### Utilisation avec les colonnes Filament

```php
use Xavier\MediaLibraryPro\Tables\Columns\MediaColumn;

MediaColumn::make('image')
    ->collection('images')
    ->conversion('thumb')
    ->size(50);
```

### Utilisation avec les infolists Filament

```php
use Xavier\MediaLibraryPro\Infolists\Entries\MediaEntry;

MediaEntry::make('images')
    ->collection('images')
    ->conversion('thumb');
```

## 🔄 Migration depuis Spatie Media Library

Si vous migrez depuis Spatie Media Library, voici un script de migration :

```php
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Models\MediaAttachment;

Media::chunk(100, function ($medias) {
    foreach ($medias as $oldMedia) {
        // Vérifier si le MediaFile existe déjà (par chemin)
        $mediaFile = MediaFile::where('path', $oldMedia->getPath())
            ->where('disk', $oldMedia->disk)
            ->first();
        
        if (!$mediaFile) {
            // Créer le MediaFile
            $mediaFile = MediaFile::create([
                'file_name' => $oldMedia->name,
                'stored_name' => basename($oldMedia->file_name),
                'disk' => $oldMedia->disk,
                'path' => $oldMedia->getPath(),
                'mime_type' => $oldMedia->mime_type,
                'size' => $oldMedia->size,
                'width' => $oldMedia->getCustomProperty('width'),
                'height' => $oldMedia->getCustomProperty('height'),
                'is_public' => true,
            ]);
        }
        
        // Créer l'attachment
        if ($oldMedia->model) {
            MediaAttachment::create([
                'media_file_id' => $mediaFile->id,
                'model_type' => get_class($oldMedia->model),
                'model_id' => $oldMedia->model_id,
                'collection_name' => $oldMedia->collection_name,
                'order' => $oldMedia->order_column ?? 0,
                'custom_properties' => $oldMedia->custom_properties ?? [],
                'is_primary' => $oldMedia->collection_name === 'images' && $oldMedia->order_column === 1,
            ]);
        }
    }
});
```

## 🔒 Sécurité

### Fichiers privés

Par défaut, tous les fichiers sont publics. Pour rendre un fichier privé :

```php
$mediaFile = $article->addMediaFile($file, 'images');
$mediaFile->mediaFile->update(['is_public' => false]);
```

Puis implémentez la vérification dans `MediaServeController` :

```php
if (!$mediaFile->is_public) {
    if (!auth()->check() || !auth()->user()->can('view', $mediaFile)) {
        abort(403, 'Accès non autorisé');
    }
}
```

### Validation des uploads

Le package valide automatiquement :
- La taille maximale (configurable)
- Les types MIME (configurables par collection)
- L'existence du fichier

Vous pouvez ajouter des validations personnalisées :

```php
$uploadService = app(MediaUploadService::class);

try {
    $uploadService->validate($file, [
        'max_size' => 5000, // KB
        'mime_types' => ['image/jpeg', 'image/png'],
    ]);
} catch (\Exception $e) {
    // Gérer l'erreur
}
```

## 🐛 Dépannage

### Les images ne s'affichent pas

1. **Vérifiez le lien symbolique** :
   ```bash
   php artisan storage:link
   ls -la public/storage
   ```

2. **Vérifiez les permissions** :
   ```bash
   chmod -R 775 storage/app/public
   chown -R www-data:www-data storage/app/public
   ```

3. **Vérifiez les routes** :
   ```bash
   php artisan route:list | grep media-library-pro
   ```

4. **Activez le mode debug** :
   ```php
   // Dans config/app.php
   'debug' => true,
   ```

### Erreur 404 sur les images

1. **Vérifiez que le fichier existe physiquement** :
   ```php
   $mediaFile = MediaFile::find(1);
   Storage::disk($mediaFile->disk)->exists($mediaFile->path);
   ```

2. **Vérifiez le chemin dans la DB** :
   ```php
   // Le chemin doit être relatif au disque
   $mediaFile->path; // Ex: "media/2025/12/image.jpg"
   ```

3. **Vérifiez les logs** :
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Les conversions ne se génèrent pas

1. **Vérifiez que le driver est installé** :
   ```bash
   # Pour Intervention Image
   composer require intervention/image
   
   # Ou utilisez GD natif (déjà inclus dans PHP)
   ```

2. **Vérifiez les permissions d'écriture** :
   ```bash
   chmod -R 775 storage/app/public/media/conversions
   ```

3. **Vérifiez la configuration** :
   ```php
   // Dans config/media-library-pro.php
   'conversions' => [
       'enabled' => true,
       'driver' => 'intervention', // ou 'gd'
   ],
   ```

### Problèmes de performance

1. **Utilisez les conversions** : Ne servez pas les images originales si elles sont grandes
2. **Activez le cache** : Utilisez un CDN ou un cache HTTP
3. **Optimisez les requêtes** : Utilisez `with()` pour éviter N+1
   ```php
   $articles = Article::with('mediaAttachments.mediaFile')->get();
   ```

## 🎨 Guide de Styling

**⚠️ IMPORTANT pour le développement :** Filament ne compile PAS automatiquement les classes Tailwind des packages.

Si vous modifiez les vues Blade ou ajoutez de nouvelles classes Tailwind, vous devez :

1. **Définir manuellement toutes les classes** dans `resources/css/media-library-pro.css`
2. **Recompiler les assets** après chaque modification :
   ```bash
   ddev artisan view:clear && ddev artisan filament:assets
   ```

📖 **Voir le guide complet :** [STYLING.md](STYLING.md)

### Points clés :
- ❌ Ne PAS utiliser `@apply` (ne fonctionne pas avec Filament)
- ✅ Définir toutes les classes manuellement dans le CSS
- ✅ Inclure les variantes dark mode, responsive, hover, focus
- ✅ Échapper correctement les classes avec caractères spéciaux (`bg-black/70` → `.bg-black\/70`)

## 📚 Documentation

Ce package inclut une documentation complète organisée dans plusieurs fichiers :

### 📖 Documents principaux

- **[README.md](README.md)** (ce fichier) - Vue d'ensemble et guide principal
- **[INSTALLATION.md](INSTALLATION.md)** - Guide d'installation détaillé étape par étape
- **[STYLING.md](STYLING.md)** - Guide complet pour le styling et les classes Tailwind
- **[CHANGELOG.md](CHANGELOG.md)** - Historique des versions et changements
- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Guide pour contribuer au projet
- **[TODO.md](TODO.md)** - Liste des fonctionnalités à venir et améliorations prévues

### 🧪 Tests

- **[tests/README.md](tests/README.md)** - Documentation sur les tests et comment les exécuter

### 📝 Structure de la documentation

```
packages/xavcha/fillament-xavcha-media-library/
├── README.md              # Documentation principale
├── INSTALLATION.md        # Guide d'installation
├── STYLING.md             # Guide de styling Tailwind
├── CHANGELOG.md           # Historique des versions
├── CONTRIBUTING.md        # Guide de contribution
├── TODO.md                # Roadmap et fonctionnalités à venir
└── tests/
    └── README.md          # Documentation des tests
```

### 🔍 Navigation rapide

- **Débutant ?** → Commencez par [INSTALLATION.md](INSTALLATION.md)
- **Problème de style ?** → Consultez [STYLING.md](STYLING.md)
- **Voulez contribuer ?** → Lisez [CONTRIBUTING.md](CONTRIBUTING.md)
- **Nouvelles fonctionnalités ?** → Voir [TODO.md](TODO.md)

## 📋 Roadmap / TODO

Voir [TODO.md](TODO.md) pour la liste complète des fonctionnalités prévues et améliorations à venir.

## 📝 Changelog

Voir [CHANGELOG.md](CHANGELOG.md) pour la liste complète des changements.

## 🤝 Contribution

Les contributions sont les bienvenues ! Veuillez lire [CONTRIBUTING.md](CONTRIBUTING.md) pour plus de détails.

### Processus de contribution

Consultez [CONTRIBUTING.md](CONTRIBUTING.md) pour le guide complet de contribution.

## 📄 Licence

Ce package est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 📧 Support

Pour toute question ou problème :

- Ouvrez une issue sur le dépôt GitHub
- Consultez la documentation
- Vérifiez les [questions fréquentes](#-dépannage)

## 🙏 Remerciements

- [Laravel](https://laravel.com) pour le framework
- [Filament](https://filamentphp.com) pour l'interface admin
- [Intervention Image](https://image.intervention.io) pour les conversions (optionnel)

---

**Fait avec ❤️ pour la communauté Laravel/Filament**
