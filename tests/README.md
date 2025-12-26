# Tests MediaLibraryPro

Ce répertoire contient tous les tests unitaires et d'intégration pour le package MediaLibraryPro.

## Structure

```
tests/
├── Unit/
│   ├── Models/
│   │   ├── MediaFileTest.php
│   │   ├── MediaAttachmentTest.php
│   │   └── MediaConversionTest.php
│   ├── Services/
│   │   ├── MediaUploadServiceTest.php
│   │   ├── MediaStorageServiceTest.php
│   │   └── MediaConversionServiceTest.php
│   └── Traits/
│       └── HasMediaFilesTest.php
├── Feature/
│   └── Http/
│       ├── MediaServeControllerTest.php
│       └── MediaConversionControllerTest.php
├── Factories/
│   ├── MediaFileFactory.php
│   ├── MediaAttachmentFactory.php
│   └── MediaConversionFactory.php
├── fixtures/
│   ├── test-image.jpg
│   ├── test-document.pdf
│   ├── test-video.mp4
│   └── test-audio.mp3
├── TestCase.php
└── README.md
```

## Exécution des tests

### Depuis le package

```bash
cd app/Packages/MediaLibraryPro
composer install
vendor/bin/phpunit
```

### Depuis le projet principal

```bash
# Si le package est dans le projet
php artisan test --path=app/Packages/MediaLibraryPro/tests
```

### Tests spécifiques

```bash
# Tests unitaires uniquement
vendor/bin/phpunit tests/Unit

# Tests d'intégration uniquement
vendor/bin/phpunit tests/Feature

# Un test spécifique
vendor/bin/phpunit tests/Unit/Models/MediaFileTest.php
```

## Configuration

Les tests utilisent :
- **SQLite en mémoire** pour la base de données
- **Storage fake** pour éviter les écritures réelles sur le disque
- **Orchestra Testbench** pour simuler un environnement Laravel

## Couverture

Les tests couvrent :
- ✅ **Modèles** : 100% des méthodes publiques
- ✅ **Services** : 95%+ avec focus sur les cas limites
- ✅ **Trait** : 100% des méthodes
- ✅ **Contrôleurs** : Tous les chemins de code (success, erreurs)

## Factories

Les factories sont disponibles pour créer facilement des données de test :

```php
use Xavier\MediaLibraryPro\Tests\Factories\MediaFileFactory;

$mediaFile = MediaFileFactory::new()->image()->create();
$videoFile = MediaFileFactory::new()->video()->create();
$documentFile = MediaFileFactory::new()->document()->create();
```

## Helpers de test

Le `TestCase` de base fournit des helpers :

```php
$this->createTestImage('test.jpg');
$this->createTestDocument('test.pdf');
$this->createTestVideo('test.mp4');
$this->createTestAudio('test.mp3');
```





