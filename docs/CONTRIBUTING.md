# Guide de contribution

Merci de votre intérêt pour contribuer à **Media Library Pro** ! Ce document fournit des directives pour contribuer au projet.

## 📋 Table des matières

- [Code de conduite](#code-de-conduite)
- [Comment contribuer](#comment-contribuer)
  - [Signaler un bug](#signaler-un-bug)
  - [Proposer une fonctionnalité](#proposer-une-fonctionnalité)
  - [Soumettre une Pull Request](#soumettre-une-pull-request)
- [Standards de code](#standards-de-code)
- [Tests](#tests)
- [Documentation](#documentation)

## 📜 Code de conduite

En participant à ce projet, vous acceptez de respecter notre code de conduite. Soyez respectueux, inclusif et constructif dans toutes vos interactions.

## 🤝 Comment contribuer

### Signaler un bug

1. **Vérifiez les issues existantes** : Assurez-vous que le bug n'a pas déjà été signalé dans les [Issues](https://github.com/your-repo/issues)

2. **Créez une nouvelle issue** avec les informations suivantes :
   - **Titre clair et descriptif**
   - **Description détaillée** du problème
   - **Étapes pour reproduire** le bug
   - **Comportement attendu** vs comportement actuel
   - **Environnement** :
     - Version de PHP
     - Version de Laravel
     - Version de Filament
     - Version du package
   - **Messages d'erreur complets** (si applicable)
   - **Captures d'écran** (si applicable)

### Proposer une fonctionnalité

1. **Vérifiez les issues existantes** : Assurez-vous que la fonctionnalité n'a pas déjà été proposée

2. **Créez une issue** avec :
   - **Titre descriptif** de la fonctionnalité
   - **Description détaillée** avec les cas d'usage
   - **Exemples d'utilisation** (code, screenshots, etc.)
   - **Avantages** de cette fonctionnalité
   - **Impact** sur le code existant (breaking changes ?)

### Soumettre une Pull Request

1. **Fork le projet** sur GitHub

2. **Clone votre fork** :
   ```bash
   git clone git@github.com:xavcha03/fillament_xavcha_media_library.git
   cd media-library-pro
   ```

3. **Créez une branche** pour votre fonctionnalité :
   ```bash
   git checkout -b feature/amazing-feature
   # ou
   git checkout -b fix/bug-description
   ```

4. **Faites vos modifications** en suivant les standards de code

5. **Ajoutez des tests** pour vos modifications

6. **Vérifiez que les tests passent** :
   ```bash
   php artisan test
   ```

7. **Commitez vos changements** :
   ```bash
   git add .
   git commit -m "Add amazing feature"
   ```
   
   **Convention de commit** :
   - `feat:` pour les nouvelles fonctionnalités
   - `fix:` pour les corrections de bugs
   - `docs:` pour la documentation
   - `style:` pour le formatage
   - `refactor:` pour le refactoring
   - `test:` pour les tests
   - `chore:` pour les tâches de maintenance

8. **Push vers votre fork** :
   ```bash
   git push origin feature/amazing-feature
   ```

9. **Ouvrez une Pull Request** sur GitHub avec :
   - Une description claire des changements
   - Une référence aux issues liées (si applicable)
   - Des screenshots (si changement UI)

## 📝 Standards de code

### PHP

- Suivez les [PSR-12 Coding Standards](https://www.php-fig.org/psr/psr-12/)
- Utilisez des noms de variables et méthodes descriptifs
- Ajoutez des docblocks PHPDoc pour toutes les méthodes publiques
- Limitez la complexité cyclomatique (max 10 recommandé)
- Évitez les méthodes trop longues (max 50 lignes recommandé)

### Exemple de code

```php
<?php

namespace Xavier\MediaLibraryPro\Services;

/**
 * Service pour gérer le stockage des fichiers médias.
 */
class MediaStorageService
{
    /**
     * Stocke un fichier et retourne un MediaFile.
     *
     * @param  \Illuminate\Http\UploadedFile|string  $file
     * @param  string|null  $disk
     * @param  string|null  $name
     * @return \Xavier\MediaLibraryPro\Models\MediaFile
     * @throws \Exception
     */
    public function store($file, ?string $disk = null, ?string $name = null): MediaFile
    {
        // Implémentation
    }
}
```

### Blade

- Utilisez l'indentation de 4 espaces
- Évitez la logique complexe dans les vues
- Utilisez les composants Filament quand possible

### JavaScript/Alpine.js

- Suivez les conventions JavaScript modernes (ES6+)
- Utilisez des noms de variables descriptifs
- Commentez les parties complexes

## 🧪 Tests

### Écrire des tests

- Ajoutez des tests pour toutes les nouvelles fonctionnalités
- Testez les cas limites et les erreurs
- Utilisez des noms de tests descriptifs

### Exemple de test

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Xavier\MediaLibraryPro\Services\MediaStorageService;
use Illuminate\Http\UploadedFile;

class MediaStorageServiceTest extends TestCase
{
    public function test_store_creates_media_file(): void
    {
        $service = app(MediaStorageService::class);
        $file = UploadedFile::fake()->image('test.jpg');
        
        $mediaFile = $service->store($file);
        
        $this->assertNotNull($mediaFile);
        $this->assertDatabaseHas('media_files', [
            'id' => $mediaFile->id,
        ]);
    }
}
```

### Exécuter les tests

```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter MediaStorageServiceTest

# Avec couverture de code
php artisan test --coverage
```

## 📚 Documentation

### Documentation du code

- Ajoutez des docblocks PHPDoc pour toutes les méthodes publiques
- Documentez les paramètres et valeurs de retour
- Ajoutez des exemples d'utilisation dans les docblocks

### Documentation utilisateur

- Mettez à jour le README.md si nécessaire
- Ajoutez des exemples d'utilisation
- Documentez les breaking changes dans docs/CHANGELOG.md

### Format des docblocks

```php
/**
 * Description courte de la méthode.
 *
 * Description plus détaillée si nécessaire.
 *
 * @param  \Illuminate\Http\UploadedFile  $file  Le fichier à uploader
 * @param  string  $collection  Le nom de la collection
 * @return \Xavier\MediaLibraryPro\Models\MediaAttachment
 * @throws \InvalidArgumentException Si le fichier est invalide
 *
 * @example
 * $article->addMediaFile($request->file('image'), 'images');
 */
```

## 🔍 Processus de review

1. **Votre PR sera revue** par les mainteneurs
2. **Des commentaires peuvent être ajoutés** pour demander des modifications
3. **Une fois approuvée**, votre PR sera mergée
4. **Merci pour votre contribution !** 🎉

## ❓ Questions ?

Si vous avez des questions, n'hésitez pas à :
- Ouvrir une issue avec le label `question`
- Contacter les mainteneurs

---

**Merci de contribuer à Media Library Pro !** 🙏

