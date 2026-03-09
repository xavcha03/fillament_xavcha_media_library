# Guide de versioning et mise à jour

## 🏷️ Gestion des versions

Ce package utilise le [Semantic Versioning](https://semver.org/lang/fr/) :
- **MAJOR** (1.0.0) : Changements incompatibles avec les versions précédentes
- **MINOR** (1.1.0) : Nouvelles fonctionnalités rétro-compatibles
- **PATCH** (1.1.1) : Corrections de bugs rétro-compatibles

## 📦 Mise à jour du package

### Étape 1 : Créer un tag de version (dans le package)

Après avoir commit et push vos changements :

```bash
# 1. Mettre à jour la version dans composer.json
# 2. Commit le changement
git add composer.json
git commit -m "chore: version 1.1.0"

# 3. Créer un tag annoté
git tag -a v1.1.0 -m "Version 1.1.0 - Description des changements"

# 4. Push le code et le tag
git push origin main
git push origin v1.1.0
```

### Étape 2 : Mettre à jour dans votre projet

Dans votre projet qui utilise le package :

#### Option A : Mise à jour vers une version spécifique

```bash
# Mettre à jour vers la version exacte
composer require xavcha/fillament-xavcha-media-library:^1.1.0

# Ou mettre à jour vers la dernière version compatible
composer update xavcha/fillament-xavcha-media-library
```

#### Option B : Utiliser une contrainte de version flexible

Dans votre `composer.json`, utilisez une contrainte qui accepte les mises à jour mineures :

```json
{
    "require": {
        "xavcha/fillament-xavcha-media-library": "^1.1.0"
    }
}
```

Puis :

```bash
composer update xavcha/fillament-xavcha-media-library
```

#### Option C : Toujours utiliser la dernière version (développement)

```json
{
    "require": {
        "xavcha/fillament-xavcha-media-library": "dev-main"
    }
}
```

⚠️ **Attention** : `dev-main` utilise directement la branche main, sans tags. Pour la production, préférez les versions taguées.

## 🔍 Vérifier la version installée

```bash
composer show xavcha/fillament-xavcha-media-library
```

## 🚨 Problèmes courants

### Composer ne détecte pas la nouvelle version

1. **Vérifier que le tag existe** :
   ```bash
   git ls-remote --tags origin
   ```

2. **Vider le cache Composer** :
   ```bash
   composer clear-cache
   ```

3. **Vérifier la contrainte de version** dans votre `composer.json` :
   - `^1.0.0` accepte les versions 1.0.0 à < 2.0.0
   - `^1.1.0` accepte les versions 1.1.0 à < 2.0.0
   - `~1.1.0` accepte les versions 1.1.0 à < 1.2.0

4. **Forcer la mise à jour** :
   ```bash
   composer update xavcha/fillament-xavcha-media-library --with-dependencies
   ```

### Le tag n'apparaît pas sur GitHub

Assurez-vous d'avoir bien poussé le tag :

```bash
git push origin v1.1.0
```

## 📝 Workflow recommandé

1. **Développement** : Faire vos modifications et commits
2. **Versioning** : Mettre à jour `composer.json` et `docs/CHANGELOG.md`
3. **Tag** : Créer un tag Git avec `git tag -a vX.Y.Z`
4. **Push** : Pousser le code et le tag
5. **Mise à jour** : Dans vos projets, faire `composer update`

## 🔄 Exemple complet

```bash
# Dans le package
git add .
git commit -m "feat: nouvelle fonctionnalité"
git push origin main

# Mettre à jour la version
# Éditer composer.json : "version": "1.2.0"
git add composer.json docs/CHANGELOG.md
git commit -m "chore: version 1.2.0"
git tag -a v1.2.0 -m "Version 1.2.0"
git push origin main
git push origin v1.2.0

# Dans votre projet
composer update xavcha/fillament-xavcha-media-library
```

