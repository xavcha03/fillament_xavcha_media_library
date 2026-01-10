# Guide d'Utilisation Complet - MediaPickerUnified

Ce guide détaille l'utilisation complète du composant `MediaPickerUnified` dans vos ressources Filament.

## Table des Matières

- [Installation dans une Ressource](#installation-dans-une-ressource)
- [Configuration du Modèle](#configuration-du-modèle)
- [Pages Create et Edit](#pages-create-et-edit)
- [Fonctionnalités](#fonctionnalités)
- [Gestion des Collections](#gestion-des-collections)
- [Validation et Contraintes](#validation-et-contraintes)
- [Exemples Complets](#exemples-complets)

## Installation dans une Ressource

### 1. Ajouter le Trait au Modèle

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Xavier\MediaLibraryPro\Traits\HasMediaFiles;

class Product extends Model
{
    use HasMediaFiles;

    protected $fillable = [
        'title',
        'description',
        'price',
    ];

    protected function registerMediaCollections(): array
    {
        return [
            'images' => [
                'singleFile' => false, // Permet plusieurs images
                'acceptedMimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
            ],
        ];
    }
}
```

### 2. Créer la Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

### 3. Configurer la Ressource Filament

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Xavier\MediaLibraryPro\Forms\Components\MediaPickerUnified;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description'),
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('€'),
                    ]),

                Section::make('Images')
                    ->schema([
                        MediaPickerUnified::make('image_ids')
                            ->label('Images du produit')
                            ->collection('images')
                            ->acceptedFileTypes(['image/*'])
                            ->multiple(true)
                            ->maxFiles(10)
                            ->minFiles(0)
                            ->showUpload(true)
                            ->showLibrary(true)
                            ->helperText('Sélectionnez ou uploadez des images. Les images existantes ne seront pas dupliquées.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
```

## Configuration du Modèle

### Méthode `registerMediaCollections()`

Cette méthode définit les collections de médias disponibles pour le modèle :

```php
protected function registerMediaCollections(): array
{
    return [
        'images' => [
            'singleFile' => false,           // true = 1 seul fichier, false = plusieurs
            'acceptedMimeTypes' => [         // Types MIME acceptés
                'image/jpeg',
                'image/png',
                'image/webp',
            ],
        ],
        'documents' => [
            'singleFile' => true,
            'acceptedMimeTypes' => [
                'application/pdf',
            ],
        ],
    ];
}
```

## Pages Create et Edit

### Page Create

```php
<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Xavier\MediaLibraryPro\Models\MediaFile;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected array $selectedMediaIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extraire les IDs des images sélectionnées
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
        // Attacher les images sélectionnées au produit
        if (!empty($this->selectedMediaIds)) {
            foreach ($this->selectedMediaIds as $mediaFileId) {
                $mediaFile = MediaFile::find($mediaFileId);
                if ($mediaFile) {
                    // attachMediaFile() réutilise le MediaFile existant, pas de duplication !
                    $this->record->attachMediaFile($mediaFile, 'images');
                }
            }
        }
    }
}
```

### Page Edit

```php
<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\EditRecord;
use Xavier\MediaLibraryPro\Models\MediaFile;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected array $selectedMediaIds = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Charger les images existantes dans le formulaire
        if ($this->record) {
            $attachments = $this->record->getMediaFiles('images');
            if ($attachments->isNotEmpty()) {
                $mediaFileIds = $attachments->map(function ($attachment) {
                    return (int) $attachment->mediaFile->id;
                })->toArray();
                
                // Toujours encoder en JSON pour multiple=true
                $data['image_ids'] = json_encode($mediaFileIds);
            } else {
                $data['image_ids'] = null;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extraire les IDs des images sélectionnées
        // Important : toujours traiter le champ, même s'il est vide, pour permettre la suppression
        if (array_key_exists('image_ids', $data)) {
            $selectedValue = $data['image_ids'];
            
            if (is_string($selectedValue)) {
                if (empty($selectedValue) || $selectedValue === '[]' || trim($selectedValue) === '') {
                    $selectedIds = [];
                } else {
                    $decoded = json_decode($selectedValue, true);
                    $selectedIds = is_array($decoded) ? $decoded : (empty($selectedValue) ? [] : [$selectedValue]);
                }
            } elseif (is_array($selectedValue)) {
                $selectedIds = $selectedValue;
            } else {
                $selectedIds = empty($selectedValue) ? [] : [$selectedValue];
            }
            
            // Filtrer les IDs vides et convertir en entiers
            $selectedIds = array_filter(array_map('intval', $selectedIds), fn($id) => $id > 0);
            
            // Toujours définir selectedMediaIds, même si vide (pour permettre la suppression)
            $this->selectedMediaIds = array_values($selectedIds);
            unset($data['image_ids']);
        } else {
            // Si le champ n'existe pas, ne pas modifier les images
            $this->selectedMediaIds = null;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Ne mettre à jour les images que si selectedMediaIds a été défini
        if ($this->selectedMediaIds === null) {
            return; // Ne pas modifier les images existantes
        }
        
        // Supprimer toutes les anciennes associations
        $this->record->clearMediaCollection('images');
        
        // Attacher les nouvelles images sélectionnées
        if (!empty($this->selectedMediaIds)) {
            foreach ($this->selectedMediaIds as $mediaFileId) {
                $mediaFile = MediaFile::find($mediaFileId);
                if ($mediaFile) {
                    $this->record->attachMediaFile($mediaFile, 'images');
                }
            }
        }
        // Si selectedMediaIds est vide, toutes les associations ont déjà été supprimées
    }
}
```

## Fonctionnalités

### 1. Sélection depuis la Bibliothèque

- Ouvrez la modal en cliquant sur "Sélectionner des médias"
- Parcourez la bibliothèque de médias existants
- Cliquez sur une image pour la sélectionner/désélectionner
- Les images sélectionnées apparaissent avec une bordure colorée
- Filtrez par collection si nécessaire

### 2. Upload de Nouveaux Fichiers

- Cliquez sur l'onglet "Upload"
- Glissez-déposez des fichiers ou cliquez pour sélectionner
- **Aperçu immédiat** : Les fichiers sélectionnés s'affichent en miniatures avant l'upload
- Cliquez sur "Uploader X fichier(s)" pour lancer l'upload
- **Retour automatique** : Après l'upload, vous êtes automatiquement redirigé vers l'onglet "Bibliothèque"
- Les fichiers uploadés sont automatiquement sélectionnés

### 3. Suppression d'Images

Deux méthodes pour supprimer des images :

#### A. Dans la Modal (Désélection)
- Cliquez sur une image déjà sélectionnée dans la bibliothèque
- L'image est désélectionnée
- Cliquez sur "Valider" pour confirmer

#### B. Avec la Croix Rouge (Suppression)
- Cliquez sur la croix rouge (×) sur une image dans l'aperçu
- L'image est immédiatement supprimée de la sélection
- La suppression est persistée après sauvegarde

### 4. Validation Automatique

- **Bouton "Valider" désactivé** : Si des fichiers sont sélectionnés mais pas encore uploadés, le bouton "Valider" est automatiquement désactivé
- **Limites min/max** : Respect automatique des limites définies
- **Types de fichiers** : Validation des types MIME acceptés

### 5. Filtrage par Collection

- Le composant filtre automatiquement les médias par collection
- Les uploads sont associés à la collection spécifiée
- Le filtre peut être modifié dans la modal

## Gestion des Collections

### Collections et Duplication

**Important** : Le package évite la duplication physique des fichiers. Si vous "uploadez" un fichier qui existe déjà (même nom, même contenu), le système :

1. Détecte le fichier existant
2. Réutilise le fichier existant dans le stockage
3. Crée uniquement une nouvelle association (`MediaAttachment`)
4. Économise l'espace disque

### Exemple de Réutilisation

```php
// Produit 1
$product1 = Product::create(['title' => 'Produit A']);
$product1->attachMediaFile($mediaFile, 'images'); // Fichier stocké

// Produit 2 - même image
$product2 = Product::create(['title' => 'Produit B']);
$product2->attachMediaFile($mediaFile, 'images'); // Même fichier, nouvelle association

// Résultat : 1 seul fichier physique, 2 associations
```

## Validation et Contraintes

### Exemples de Validation

```php
// Minimum 1 image, maximum 5
MediaPickerUnified::make('image_ids')
    ->minFiles(1)
    ->maxFiles(5)
    ->required()
    ->collection('images');

// Exactement 3 fichiers
MediaPickerUnified::make('document_ids')
    ->exactFiles(3)
    ->required()
    ->collection('documents');

// Entre 2 et 10 images
MediaPickerUnified::make('gallery_ids')
    ->limit(2, 10)
    ->collection('gallery');
```

## Exemples Complets

### Exemple 1 : Produit avec Images

Voir les exemples dans les sections précédentes.

### Exemple 2 : Article avec Image Unique

```php
// Modèle
protected function registerMediaCollections(): array
{
    return [
        'featured_image' => [
            'singleFile' => true,
            'acceptedMimeTypes' => ['image/jpeg', 'image/png'],
        ],
    ];
}

// Ressource
MediaPickerUnified::make('featured_image_id')
    ->label('Image mise en avant')
    ->single()
    ->required()
    ->collection('featured_image')
    ->acceptedFileTypes(['image/jpeg', 'image/png'])
    ->maxFileSize(2048); // 2MB
```

### Exemple 3 : Galerie avec Réorganisation

```php
MediaPickerUnified::make('gallery_ids')
    ->label('Galerie photos')
    ->limit(3, 10)
    ->collection('gallery')
    ->allowReordering(true) // Permet de réorganiser
    ->acceptedFileTypes(['image/*'])
    ->helperText('Ajoutez entre 3 et 10 images. Vous pouvez les réorganiser.');
```

## Dépannage

### Les images ne s'affichent pas après sauvegarde

Vérifiez que :
1. `mutateFormDataBeforeFill` charge bien les images existantes
2. Les IDs sont correctement encodés en JSON pour `multiple=true`
3. `afterSave` attache bien les images au modèle

### La suppression ne fonctionne pas

Vérifiez que :
1. `mutateFormDataBeforeSave` traite bien les valeurs vides (`'[]'`)
2. `afterSave` supprime bien les associations avec `clearMediaCollection`
3. Le champ `image_ids` est bien présent dans les données (même s'il est vide)

### Les uploads ne s'affichent pas dans la bibliothèque

Vérifiez que :
1. L'événement `refresh-media-list` est bien dispatché après l'upload
2. Le composant Livewire écoute bien cet événement
3. La pagination est réinitialisée après l'upload

## Ressources Supplémentaires

- [Méthodes Fluentes](./METHODES_FLUENTES.md) - Liste complète des méthodes disponibles
- [README](./README.md) - Documentation générale du package
- [Architecture](./README.md#-architecture) - Comprendre la structure du package






