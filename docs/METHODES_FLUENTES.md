# Méthodes Fluentes pour MediaPickerUnified

Le composant `MediaPickerUnified` offre une API fluente similaire aux autres composants Filament, permettant de configurer facilement toutes les options disponibles.

## Méthodes de Configuration de Base

### Sélection Multiple/Unique

```php
// Sélection unique (1 fichier)
MediaPickerUnified::make('image_id')
    ->single()
    ->collection('images');

// Équivalent à :
MediaPickerUnified::make('image_id')
    ->multiple(false)
    ->maxFiles(1)
    ->collection('images');

// Sélection multiple (illimitée)
MediaPickerUnified::make('image_ids')
    ->multiple(true)
    ->collection('images');

// Sélection multiple avec limites
MediaPickerUnified::make('image_ids')
    ->limit(2, 5) // Minimum 2, maximum 5 fichiers
    ->collection('images');

// Nombre exact de fichiers requis
MediaPickerUnified::make('image_ids')
    ->exactFiles(3) // Exactement 3 fichiers requis
    ->collection('images');
```

### Limites de Fichiers

```php
MediaPickerUnified::make('image_ids')
    ->minFiles(1)        // Minimum 1 fichier requis
    ->maxFiles(10)       // Maximum 10 fichiers
    ->collection('images');
```

### Types de Fichiers Acceptés

```php
// Images uniquement
MediaPickerUnified::make('image_ids')
    ->acceptedFileTypes(['image/*'])
    ->collection('images');

// Images spécifiques
MediaPickerUnified::make('image_ids')
    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
    ->collection('images');

// Vidéos uniquement
MediaPickerUnified::make('video_ids')
    ->acceptedFileTypes(['video/*'])
    ->collection('videos');

// Images et vidéos
MediaPickerUnified::make('media_ids')
    ->acceptedFileTypes(['image/*', 'video/*'])
    ->collection('media');
```

### Taille Maximale des Fichiers

```php
MediaPickerUnified::make('image_ids')
    ->maxFileSize(5120) // 5MB en KB
    ->collection('images');
```

### Collection

```php
MediaPickerUnified::make('image_ids')
    ->collection('product-images') // Nom de la collection
    ->acceptedFileTypes(['image/*']);
```

### Affichage des Onglets

```php
// Afficher uniquement la bibliothèque (pas d'upload)
MediaPickerUnified::make('image_ids')
    ->showUpload(false)
    ->showLibrary(true)
    ->collection('images');

// Afficher uniquement l'upload (pas de bibliothèque)
MediaPickerUnified::make('image_ids')
    ->showUpload(true)
    ->showLibrary(false)
    ->collection('images');

// Afficher les deux (par défaut)
MediaPickerUnified::make('image_ids')
    ->showUpload(true)
    ->showLibrary(true)
    ->collection('images');
```

### Conversion d'Image

```php
MediaPickerUnified::make('image_ids')
    ->conversion('thumb') // Afficher la conversion 'thumb' par défaut
    ->collection('images');
```

### Réorganisation des Fichiers

```php
MediaPickerUnified::make('image_ids')
    ->allowReordering(true) // Permet de réorganiser les fichiers par drag & drop
    ->collection('images');
```

### Téléchargement

```php
MediaPickerUnified::make('image_ids')
    ->downloadable(true) // Permet de télécharger les fichiers depuis l'aperçu
    ->collection('images');
```

## Méthodes Filament Standards

Le composant hérite de `Field`, donc toutes les méthodes standards de Filament sont disponibles :

```php
MediaPickerUnified::make('image_ids')
    ->label('Images du produit')
    ->helperText('Sélectionnez jusqu\'à 10 images')
    ->required() // Validation : au moins 1 fichier requis
    ->disabled(fn ($record) => $record?->isPublished())
    ->visible(fn ($record) => auth()->user()->can('upload-images'))
    ->columnSpanFull()
    ->collection('images');
```

## Exemples Complets

### Exemple 1 : Image Unique Requise

```php
MediaPickerUnified::make('featured_image_id')
    ->label('Image mise en avant')
    ->single()
    ->required()
    ->acceptedFileTypes(['image/jpeg', 'image/png'])
    ->maxFileSize(2048) // 2MB
    ->collection('featured-images')
    ->helperText('Image principale du produit (JPG ou PNG, max 2MB)');
```

### Exemple 2 : Galerie d'Images (2-5 images)

```php
MediaPickerUnified::make('gallery_image_ids')
    ->label('Galerie d\'images')
    ->limit(2, 5) // Entre 2 et 5 images
    ->acceptedFileTypes(['image/*'])
    ->maxFileSize(5120) // 5MB par image
    ->allowReordering(true)
    ->collection('gallery')
    ->helperText('Ajoutez entre 2 et 5 images pour la galerie');
```

### Exemple 3 : Documents PDF (Exactement 3)

```php
MediaPickerUnified::make('document_ids')
    ->label('Documents PDF')
    ->exactFiles(3) // Exactement 3 fichiers
    ->acceptedFileTypes(['application/pdf'])
    ->maxFileSize(10240) // 10MB par fichier
    ->showUpload(true)
    ->showLibrary(false) // Pas de sélection depuis la bibliothèque
    ->collection('documents')
    ->required()
    ->helperText('Veuillez uploader exactement 3 documents PDF');
```

### Exemple 4 : Médias Mixtes avec Conversion

```php
MediaPickerUnified::make('media_ids')
    ->label('Médias')
    ->multiple(true)
    ->maxFiles(20)
    ->acceptedFileTypes(['image/*', 'video/*'])
    ->conversion('thumb') // Afficher les miniatures
    ->downloadable(true)
    ->collection('media')
    ->helperText('Images et vidéos acceptés');
```

## Validation

La validation peut être combinée avec les méthodes Filament standards :

```php
MediaPickerUnified::make('image_ids')
    ->required()
    ->minFiles(1)
    ->maxFiles(5)
    ->collection('images');
```

La validation vérifie automatiquement :
- Le nombre minimum de fichiers (si `minFiles > 0` ou `required()`)
- Le nombre maximum de fichiers (si `maxFiles` est défini)
- Les types de fichiers acceptés
- La taille maximale des fichiers (si `maxFileSize` est défini)

## Liste Complète des Méthodes

| Méthode | Type | Description |
|---------|------|-------------|
| `single()` | - | Sélection unique (équivalent à `multiple(false)` + `maxFiles(1)`) |
| `multiple(bool)` | bool | Autoriser la sélection multiple |
| `limit(int $min, ?int $max)` | int, int\|null | Définit min et max en une seule méthode |
| `exactFiles(int $count)` | int | Nombre exact de fichiers requis |
| `minFiles(int)` | int | Nombre minimum de fichiers |
| `maxFiles(int\|null)` | int\|null | Nombre maximum de fichiers |
| `acceptedFileTypes(array)` | array | Types MIME acceptés |
| `collection(string)` | string | Nom de la collection |
| `maxFileSize(int\|null)` | int\|null | Taille maximale en KB |
| `showUpload(bool)` | bool | Afficher l'onglet upload |
| `showLibrary(bool)` | bool | Afficher l'onglet bibliothèque |
| `conversion(string\|null)` | string\|null | Conversion à afficher |
| `allowReordering(bool)` | bool | Permettre la réorganisation |
| `downloadable(bool)` | bool | Permettre le téléchargement |

Toutes les méthodes standards de Filament `Field` sont également disponibles :
- `label()`, `helperText()`, `hint()`, `placeholder()`
- `required()`, `disabled()`, `visible()`, `hidden()`
- `columnSpan()`, `columnSpanFull()`, etc.

