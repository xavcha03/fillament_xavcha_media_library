# Environnement Workbench avec ddev

Ce guide explique comment configurer un environnement de développement workbench pour tester le package en live avec ddev.

## Prérequis

- [ddev](https://ddev.readthedocs.io/) installé et configuré

## Installation initiale

### 1. Configurer ddev

```bash
ddev config --project-type=laravel --docroot=workbench/public --php-version=8.2 --database=mariadb:11.4
```

### 2. Démarrer ddev

```bash
ddev start
```

### 3. Créer l'application Laravel dans workbench

```bash
ddev exec rm -rf workbench
ddev exec composer create-project laravel/laravel workbench --prefer-dist --no-interaction
```

### 4. Configurer le repository local et installer les dépendances

```bash
# Ajouter le repository path pour le package local
ddev exec composer config repositories.local-package path /var/www/html --working-dir=/var/www/html/workbench

# Installer Filament
ddev exec composer require filament/filament:"^4.0" --working-dir=/var/www/html/workbench

# Installer le package local
ddev exec composer require xavcha/fillament-xavcha-media-library:@dev --working-dir=/var/www/html/workbench
```

### 5. Configurer Filament et le package

```bash
# Installer Filament panels
ddev exec php workbench/artisan filament:install --panels --no-interaction

# Publier la configuration et les migrations du package
ddev exec php workbench/artisan vendor:publish --tag=media-library-pro-config --force
ddev exec php workbench/artisan vendor:publish --tag=media-library-pro-migrations --force

# Exécuter les migrations
ddev exec php workbench/artisan migrate

# Créer le lien symbolique du storage
ddev exec php workbench/artisan storage:link

# Créer un utilisateur Filament (optionnel)
ddev exec php workbench/artisan make:filament-user
```

## Utilisation quotidienne

### Démarrer l'environnement

```bash
ddev start
```

### Accéder à l'application

L'application est disponible à : **https://fillament-xavcha-media-library.ddev.site**

### Accéder au shell du conteneur

```bash
ddev ssh
cd workbench
```

### Commandes utiles

```bash
# Redémarrer ddev
ddev restart

# Arrêter ddev
ddev stop

# Voir les logs
ddev logs

# Exécuter des commandes artisan
ddev exec php workbench/artisan migrate

# Exécuter composer dans workbench
ddev composer install -d workbench

# Accéder à la base de données
ddev mysql
```

## Développement du package

Le package est lié via un repository path dans `workbench/composer.json`. Cela signifie que :

- ✅ Toutes les modifications dans `src/` sont immédiatement visibles dans workbench
- ✅ Les modifications dans `resources/views/` sont immédiatement visibles
- ✅ Les modifications dans `config/` nécessitent de republier : `ddev exec php workbench/artisan vendor:publish --tag=media-library-pro-config --force`
- ✅ Les modifications dans `database/migrations/` nécessitent de republier : `ddev exec php workbench/artisan vendor:publish --tag=media-library-pro-migrations --force`

### Après modification des migrations

```bash
ddev exec php workbench/artisan vendor:publish --tag=media-library-pro-migrations --force
ddev exec php workbench/artisan migrate:fresh
```

### Après modification de la configuration

```bash
ddev exec php workbench/artisan vendor:publish --tag=media-library-pro-config --force
ddev restart
```

## Structure

```
.
├── .ddev/              # Configuration ddev (généré automatiquement)
├── workbench/          # Application Laravel de test (ignoré par git)
│   ├── app/
│   ├── config/
│   ├── database/
│   └── ...
├── src/                # Code source du package
└── resources/          # Vues et assets du package
```

## Dépannage

### Réinitialiser complètement l'environnement

```bash
ddev stop
rm -rf workbench .ddev
# Puis suivre les étapes d'installation initiale ci-dessus
```

### Mettre à jour le package dans workbench

```bash
ddev composer update xavcha/fillament-xavcha-media-library -d workbench
```

### Vérifier que le package est bien lié

```bash
ddev ssh
cd workbench
composer show xavcha/fillament-xavcha-media-library
```

Vous devriez voir que le package pointe vers `../` (le répertoire parent).

