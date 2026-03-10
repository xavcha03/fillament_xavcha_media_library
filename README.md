# Media Library Pro pour Laravel & Filament

Package Laravel / Filament complet pour gérer vos médias (images, vidéos, documents) avec collections, conversions d’images et intégration native Filament v4.

## ✨ Fonctionnalités principales

- **Bibliothèque média centralisée** (images, vidéos, documents…)
- **Collections par modèle** via le trait `HasMediaFiles`
- **Conversions d’images** configurables (thumb, small, medium, etc.)
- **Composant Filament `MediaPickerUnified`** (upload + bibliothèque unifiée)
- **Architecture sans duplication** : un fichier physique peut être lié à plusieurs modèles
- **Support dossiers** (organisation hiérarchique), actions configurables, vue grille & liste
- **UX de sélection moderne** : clic, Ctrl/Cmd, Shift (plage), double-clic, drag-select rectangulaire, toolbar contextuelle
 - **Rotation manuelle simple** : import brut de l’image (sans correction EXIF automatique) + boutons « Pivoter à gauche / à droite » et infos d’orientation dans la modale de détails

## ✅ Compatibilité

- **PHP** : 8.2+
- **Laravel** : 12.x
- **Filament** : 4.x

## 🚀 Installation rapide

```bash
composer require xavcha/fillament-xavcha-media-library
php artisan vendor:publish --tag=media-library-pro-migrations
php artisan migrate
php artisan vendor:publish --tag=media-library-pro-config
php artisan storage:link
```

Plus de détails dans `docs/INSTALLATION.md`.

## 🏃 Démarrage rapide

### 1. Ajouter le trait au modèle

```php
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

### 2. Utiliser `MediaPickerUnified` dans un formulaire Filament

```php
use Xavier\MediaLibraryPro\Forms\Components\MediaPickerUnified;

MediaPickerUnified::make('image_ids')
    ->label('Image principale')
    ->collection('images')
    ->acceptedFileTypes(['image/*'])
    ->single()
    ->showUpload(true)
    ->showLibrary(true);
```

### 3. Afficher l’image dans une vue Blade

```blade
@if($article->getFirstMediaFile('images'))
    @php
        $attachment = $article->getFirstMediaFile('images');
        $mediaFile = $attachment->mediaFile;
        $version = $mediaFile->updated_at?->timestamp ?? $mediaFile->size ?? time();
    @endphp
    <img
        src="{{ route('media-library-pro.serve', ['media' => $mediaFile->uuid, 't' => $version]) }}"
        alt="{{ $article->title }}"
    >
@endif
```

## 🏗️ Architecture (résumé)

- `MediaFile` : fichier unique (chemin, disk, mime, taille, métadonnées…)
- `MediaAttachment` : lien polymorphique modèle ↔ fichier (collection, ordre, propriétés)
- `MediaConversion` : variantes générées (thumb, medium, …)
- Services dédiés :
  - `MediaStorageService` : stockage & URLs
  - `MediaUploadService` : validation + upload
  - `MediaConversionService` : conversions
  - `MediaFolderService` : dossiers hiérarchiques

Les routes publiques principales :

- `media-library-pro.serve` : servir un média original
- `media-library-pro.conversion` : servir une conversion
- `media-library-pro.download` : téléchargement d’un média

## 📚 Documentation

Toute la documentation détaillée a été déplacée dans le dossier `docs/` :

- `docs/INSTALLATION.md` – Installation détaillée
- `docs/GUIDE_UTILISATION.md` – Guide complet d’utilisation de `MediaPickerUnified`
- `docs/METHODES_FLUENTES.md` – API fluente du composant
- `docs/STYLING.md` – Styling & intégration Tailwind / Filament
- `docs/WORKBENCH.md` – Environnement de développement avec workbench + ddev
- `docs/CHANGELOG.md` – Historique des versions
- `docs/VERSIONING.md` – Stratégie de versioning & release
- `docs/TODO.md` – Roadmap et fonctionnalités prévues
- `docs/CONTRIBUTING.md` – Guide de contribution

## 📄 Licence

Ce package est distribué sous licence **MIT**.

# Media Library Pro

[![Laravel](https://img.shields.io/badge/Laravel-12.x%2B-red.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-4.x-blue.svg)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Un package Laravel/Filament complet et moderne pour gérer les médias (images, vidéos, documents) avec support des conversions, collections, et intégration native Filament v4.

## ✨ Fonctionnalités

- 🎯 **Gestion complète des médias** : Images, vidéos, documents, archives
- 🔄 **Conversions d'images** : Génération automatique de thumbnails et variantes
- ⚡ **Optimisation automatique** : Compression et redimensionnement automatiques des images uploadées
- 📁 **Collections** : Organisation des médias par type ou usage
- 📂 **Gestion des dossiers** : Organisation hiérarchique des médias avec navigation par dossiers (création, navigation, upload dans un dossier)
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
- 📂 **Navigation par dossiers** : Accès aux dossiers dans le picker, création de dossiers, upload dans un dossier
- 🎨 **Interface moderne** : Miniatures compactes, design soigné, responsive
- ⚡ **Actions configurables** : Système d'actions Filament modulaires (renommer, déplacer, télécharger, etc.)
- 🎯 **Actions en masse** : Opérations groupées sur plusieurs fichiers
- 🖱️ **UX de sélection moderne** : Clic, Ctrl/Cmd, Shift (plage), double-clic, drag-select rectangulaire, toolbar contextuelle

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
- [🛠️ Développement](#️-développement) - Guide pour développer le package
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
- `media_folders` : Dossiers pour organiser les médias (nouveau)

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

### 5. Installer les outils d'optimisation d'images (recommandé)

Pour activer l'optimisation automatique des images (compression, redimensionnement), installez les outils système suivants sur votre serveur :

#### Ubuntu/Debian

```bash
sudo apt-get update
sudo apt-get install jpegoptim optipng pngquant webp gifsicle
```

#### CentOS/RHEL/Fedora

```bash
# Pour CentOS/RHEL (avec EPEL)
sudo yum install epel-release
sudo yum install jpegoptim optipng pngquant libwebp-tools gifsicle

# Pour Fedora
sudo dnf install jpegoptim optipng pngquant libwebp-tools gifsicle
```

#### macOS (avec Homebrew)

```bash
brew install jpegoptim optipng pngquant webp gifsicle
```

#### Vérification de l'installation

Vérifiez que les outils sont bien installés :

```bash
jpegoptim --version
optipng --version
pngquant --version
cwebp -version
gifsicle --version
```

#### Installation avec DDEV

Si vous utilisez DDEV pour le développement local, installez les outils dans le conteneur :

```bash
ddev exec apt-get update
ddev exec apt-get install -y jpegoptim optipng pngquant webp gifsicle
```

Ou ajoutez-les dans votre `.ddev/config.yaml` :

```yaml
webimage_extra_packages:
  - jpegoptim
  - optipng
  - pngquant
  - webp
  - gifsicle
```

> **Note** : L'optimisation d'images fonctionne même si ces outils ne sont pas installés, mais sera moins efficace. Le package utilisera alors uniquement le redimensionnement et la compression de base via GD/Intervention Image.

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

### Configuration de l'optimisation d'images

```php
'optimization' => [
    'enabled' => true,              // Activer l'optimisation
    'auto_optimize' => true,         // Optimisation automatique à l'upload
    'max_width' => 1920,             // Largeur maximale (null = pas de limite)
    'max_height' => 1920,            // Hauteur maximale (null = pas de limite)
    'quality' => 85,                 // Qualité JPEG/WebP (1-100)
    'convert_to_webp' => false,      // Convertir automatiquement en WebP
    'preserve_original' => false,    // Conserver l'original si conversion WebP
    'queue' => false,                // Traitement en queue (asynchrone)
],

### Cache HTTP des médias

Par défaut, le package applique un cache HTTP long sur les fichiers servis (utile en front/public), mais **optimisé pour l’admin** :

```php
'http_cache' => [
    // Si false : pas de cache navigateur / CDN (admin Filament, préprod, etc.)
    'enabled' => true,

    // max-age utilisé quand enabled=true ET qu'un paramètre "t" est présent dans l'URL
    'max_age' => 31536000,
],
```

- Les routes `media-library-pro.serve` et `media-library-pro.conversion` :
  - **si `http_cache.enabled = false`** → envoient `Cache-Control: no-cache, no-store, must-revalidate`
  - **si `http_cache.enabled = true`** :
    - avec `?t=...` → `Cache-Control: public, max-age=..., immutable`
    - sans `t` → `no-cache, no-store, must-revalidate`

> 💡 **En admin Filament**, tu peux désactiver le cache HTTP en mettant `'http_cache' => ['enabled' => false]` dans `config/media-library-pro.php` pour voir immédiatement les effets de rotation/optimisation sans hard refresh.
```

**Options d'optimisation :**
- `enabled` : Active/désactive complètement l'optimisation
- `auto_optimize` : Optimisation automatique lors de l'upload (recommandé)
- `max_width` / `max_height` : Redimensionne automatiquement les images trop grandes (utile pour les photos de téléphone)
- `quality` : Qualité de compression (80 = bon compromis qualité/taille pour le web, recommandé 75-80)
- `convert_to_webp` : Convertit les JPEG/PNG en WebP (recommandé : 30-50% de réduction supplémentaire, supporté par tous les navigateurs modernes)
- `preserve_original` : Si `true`, conserve l'original lors de la conversion WebP
- `queue` : Si `true`, l'optimisation se fait en arrière-plan (nécessite les queues Laravel)

> **Note** : L'optimisation est particulièrement utile pour les images uploadées depuis des téléphones, qui sont souvent très grandes (3000x4000px+) et lourdes (5-15 Mo).

### Optimiser les images existantes

Pour optimiser les images déjà uploadées avant l'activation de l'optimisation automatique, vous avez deux options :

#### Option 1 : Via l'interface (image par image)

1. Ouvrez la bibliothèque média dans Filament
2. Cliquez sur une image pour ouvrir la modale de détails
3. Cliquez sur le bouton **"Optimiser l'image"** dans la section Actions
4. L'image sera optimisée et vous verrez l'espace économisé

#### Option 2 : Via la commande Artisan (en masse)

Optimisez toutes les images existantes en une seule commande :

```bash
php artisan media-library-pro:optimize-images
```

**Options disponibles :**

```bash
# Optimiser toutes les images
php artisan media-library-pro:optimize-images

# Limiter le nombre d'images à traiter
php artisan media-library-pro:optimize-images --limit=50

# Forcer l'optimisation même si déjà optimisée
php artisan media-library-pro:optimize-images --force

# Traiter par batch de 200 images (par défaut: 100)
php artisan media-library-pro:optimize-images --chunk=200
```

La commande affichera :
- Le nombre d'images optimisées
- Le nombre d'échecs
- L'espace total économisé (en MB)

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

##### UX de sélection (bibliothèque et picker)

- **Clic** : Sélectionne/désélectionne un média
- **Ctrl/Cmd + clic** : Ajoute ou retire de la sélection
- **Shift + clic** : Sélectionne une plage entre le dernier cliqué et celui-ci
- **Double-clic** : Bibliothèque → ouvre les détails ; Picker → valide et insère la sélection
- **Drag-select** : Sélection rectangulaire en glissant la souris sur la grille (Ctrl/Cmd pour ajouter)
- **Toolbar contextuelle** : Compteur « X médias sélectionnés », actions groupées (suppression), « Tout sélectionner », « Sélectionner tout dans la page », « Annuler »
- **Picker** : Barre en bas avec compteur, boutons « Annuler » et « Insérer »

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

### Dossiers

Les dossiers permettent d'organiser les médias de manière hiérarchique, similaire à un système de fichiers.

#### Utilisation dans MediaPickerUnified

Les dossiers sont automatiquement disponibles dans le picker :

- **Navigation** : Cliquez sur un dossier pour y naviguer
- **Création** : Créez des dossiers depuis l'onglet Upload
- **Upload dans un dossier** : Sélectionnez un dossier avant d'uploader des fichiers
- **Breadcrumb** : Navigation facile avec retour à la racine

#### Utilisation programmatique

```php
use Xavier\MediaLibraryPro\Models\MediaFolder;
use Xavier\MediaLibraryPro\Services\MediaFolderService;

$folderService = app(MediaFolderService::class);

// Créer un dossier à la racine
$folder = $folderService->create('Mon Dossier');

// Créer un sous-dossier
$subFolder = $folderService->create('Sous-dossier', $folder);

// Récupérer les dossiers racine
$rootFolders = $folderService->getRootFolders();

// Récupérer les dossiers enfants
$childFolders = $folderService->getChildFolders($folder);

// Déplacer un dossier
$folderService->move($subFolder, null); // Déplacer à la racine

// Supprimer un dossier (et son contenu)
$folderService->delete($folder);
```

#### Associer un média à un dossier

```php
// Lors de l'upload
$mediaFile = $uploadService->upload($file);
$mediaFile->folder_id = $folder->id;
$mediaFile->save();

// Ou via le modèle
$mediaFile = MediaFile::find(1);
$mediaFile->folder_id = $folder->id;
$mediaFile->save();
```

#### Récupérer les médias d'un dossier

```php
// Médias dans un dossier spécifique
$mediaInFolder = MediaFile::where('folder_id', $folder->id)->get();

// Médias à la racine (sans dossier)
$rootMedia = MediaFile::whereNull('folder_id')->get();
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
$mediaFile->folder                            // MediaFolder|null (dossier parent)
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

### Modèle MediaFolder

#### Propriétés

```php
$folder->name                 // Nom du dossier (string)
$folder->path                 // Chemin complet du dossier (string)
$folder->parent_id            // ID du dossier parent (int|null)
$folder->created_at           // Date de création
$folder->updated_at           // Date de mise à jour
```

#### Relations

```php
$folder->parent                // MediaFolder|null (dossier parent)
$folder->children              // Collection<MediaFolder> (sous-dossiers)
$folder->mediaFiles            // Collection<MediaFile> (fichiers dans le dossier)
```

#### Méthodes

```php
$folder->getFullPath()         // Chemin complet du dossier (string)
$folder->moveTo($newParent)    // Déplacer vers un nouveau parent (bool)
$folder->deleteWithContents()  // Supprimer avec son contenu (bool)
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
        $mediaFile = $attachment->mediaFile;
        $version = $mediaFile->updated_at?->timestamp ?? $mediaFile->size ?? time();
        $imageUrl = route('media-library-pro.serve', [
            'media' => $mediaFile->uuid,
            't' => $version,
        ]);
    @endphp
    <img src="{{ $imageUrl }}" 
         alt="{{ $article->title }}"
         loading="lazy">
@endif

{{-- Afficher une galerie --}}
<div class="gallery">
    @foreach($article->getMediaFiles('gallery') as $attachment)
        @php
            $mediaFile = $attachment->mediaFile;
            $version = $mediaFile->updated_at?->timestamp ?? $mediaFile->size ?? time();
        @endphp
        <img src="{{ route('media-library-pro.serve', [
            'media' => $mediaFile->uuid,
            't' => $version,
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

### Le bouton d'optimisation n'apparaît pas ou ne fonctionne pas

Si le bouton "Optimiser l'image" n'apparaît pas ou ne fonctionne pas après une mise à jour du package :

#### 1. Nettoyer tous les caches

```bash
# Nettoyer tous les caches Laravel
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Nettoyer le cache Livewire (important pour les composants Livewire)
php artisan livewire:discover

# Si vous utilisez Filament v4, vider aussi le cache Filament
php artisan filament:cache-components
php artisan filament:cache-forms
php artisan filament:cache-tables
```

**En développement avec DDEV :**

```bash
ddev exec php artisan cache:clear
ddev exec php artisan config:clear
ddev exec php artisan view:clear
ddev exec php artisan route:clear
ddev exec php artisan livewire:discover
```

#### 2. Vérifier que l'optimisation est activée

Assurez-vous que dans `config/media-library-pro.php`, vous avez :

```php
'optimization' => [
    'enabled' => true,  // ← Doit être à true
    // ...
],
```

Puis republiez la configuration si nécessaire :

```bash
php artisan vendor:publish --tag=media-library-pro-config --force
php artisan config:clear
```

#### 3. Vérifier la console du navigateur

Ouvrez la console développeur (F12) et vérifiez s'il y a des erreurs JavaScript ou Livewire.

#### 4. Vérifier les logs Laravel

```bash
tail -f storage/logs/laravel.log
```

### Les vues du package ne se mettent pas à jour

Si vous modifiez les vues du package et que les changements ne sont pas visibles :

1. **Vider le cache des vues :**
   ```bash
   php artisan view:clear
   ```

2. **En développement, vérifier que les vues sont bien chargées depuis le package :**
   - Les vues sont chargées via `loadViewsFrom()` dans le ServiceProvider
   - Pas besoin de les publier pour qu'elles fonctionnent
   - Si vous avez publié les vues, supprimez-les de `resources/views/vendor/media-library-pro/`

3. **Redémarrer le serveur de développement** (si vous utilisez `php artisan serve`)

4. **En développement avec DDEV, redémarrer le conteneur :**
   ```bash
   ddev restart
   ```

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
   ddev exec php workbench/artisan view:clear
   ddev exec php workbench/artisan filament:assets
   ```

📖 **Voir le guide complet :** [STYLING.md](STYLING.md)

### Points clés :
- ❌ Ne PAS utiliser `@apply` (ne fonctionne pas avec Filament)
- ✅ Définir toutes les classes manuellement dans le CSS
- ✅ Inclure les variantes dark mode, responsive, hover, focus
- ✅ Échapper correctement les classes avec caractères spéciaux (`bg-black/70` → `.bg-black\/70`)

## ♻️ Déduplication des uploads (important en prod)

### Pourquoi un “doublon” peut apparaître

En prod (Livewire, latence, retries réseau), il peut arriver qu’un upload soit **soumis deux fois** (double événement, timeout, re-submit). Sans garde-fou côté stockage, cela crée deux lignes `media_files` pointant vers deux fichiers stockés.

### Comment le package gère ça

Le stockage calcule maintenant un **checksum SHA-256** sur le fichier final (après optimisation éventuelle), puis :

- si un `MediaFile` existe déjà avec le même `(disk, checksum)` → le package **réutilise l’existant** et supprime le fichier physique fraîchement stocké.
- sinon → un nouveau `MediaFile` est créé.

> Résultat : même si l’upload est déclenché 2 fois, tu n’obtiens pas deux médias “identiques”.

### Migration

Après mise à jour du package, exécuter les migrations (le package ajoute `media_files.checksum` + un index unique) :

```bash
php artisan migrate
```

## 🛠️ Développement

Pour développer et tester le package localement, consultez le guide complet :

📖 **[WORKBENCH.md](WORKBENCH.md)** - Configuration de l'environnement de développement avec ddev

### Commandes utiles pour le développement

```bash
# Vider le cache des vues
ddev exec php workbench/artisan view:clear

# Recompiler les assets Filament
ddev exec php workbench/artisan filament:assets

# Publier les migrations
ddev exec php workbench/artisan vendor:publish --tag=media-library-pro-migrations --force

# Publier la configuration
ddev exec php workbench/artisan vendor:publish --tag=media-library-pro-config --force
```

## 📚 Documentation

Ce package inclut une documentation complète organisée dans plusieurs fichiers :

### 📖 Documents principaux

- **[README.md](README.md)** (ce fichier) - Vue d'ensemble et guide principal
- **[INSTALLATION.md](INSTALLATION.md)** - Guide d'installation détaillé étape par étape
- **[GUIDE_UTILISATION.md](GUIDE_UTILISATION.md)** - Guide complet d'utilisation de MediaPickerUnified avec exemples détaillés
- **[METHODES_FLUENTES.md](METHODES_FLUENTES.md)** - Référence complète de l'API fluente pour MediaPickerUnified
- **[STYLING.md](STYLING.md)** - Guide complet pour le styling et les classes Tailwind
- **[WORKBENCH.md](WORKBENCH.md)** - Guide pour configurer l'environnement de développement avec ddev
- **[CHANGELOG.md](CHANGELOG.md)** - Historique des versions et changements
- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Guide pour contribuer au projet
- **[TODO.md](TODO.md)** - Liste des fonctionnalités à venir et améliorations prévues

### 🧪 Tests

- **[tests/README.md](tests/README.md)** - Documentation sur les tests et comment les exécuter

### 📝 Structure de la documentation

```
packages/xavcha/fillament-xavcha-media-library/
├── README.md              # Documentation principale (ce fichier)
├── INSTALLATION.md        # Guide d'installation détaillé
├── GUIDE_UTILISATION.md   # Guide complet d'utilisation avec exemples
├── METHODES_FLUENTES.md   # Référence API fluente complète
├── STYLING.md             # Guide de styling Tailwind
├── WORKBENCH.md           # Guide environnement de développement
├── CHANGELOG.md           # Historique des versions
├── CONTRIBUTING.md        # Guide de contribution
├── TODO.md                # Roadmap et fonctionnalités à venir
└── tests/
    └── README.md          # Documentation des tests
```

### 🔍 Navigation rapide

- **Débutant ?** → Commencez par [INSTALLATION.md](INSTALLATION.md)
- **Utiliser MediaPickerUnified ?** → Consultez [GUIDE_UTILISATION.md](GUIDE_UTILISATION.md)
- **Besoin de la référence API ?** → Voir [METHODES_FLUENTES.md](METHODES_FLUENTES.md)
- **Problème de style ?** → Consultez [STYLING.md](STYLING.md)
- **Développement du package ?** → Lisez [WORKBENCH.md](WORKBENCH.md)
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
