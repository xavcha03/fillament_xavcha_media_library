# Changelog

Tous les changements notables de ce package seront documentés dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère à [Semantic Versioning](https://semver.org/lang/fr/).

## [1.0.0] - 2025-12-14

### 🎉 Version initiale

Première version stable du package Media Library Pro, un système complet de gestion de médias pour Laravel et Filament.

### ✨ Ajouté

#### Architecture de base
- **Tables de base de données** :
  - `media_files` : Stockage des fichiers médias uniques avec UUID
  - `media_attachments` : Associations polymorphiques entre fichiers et modèles
  - `media_conversions` : Conversions d'images générées
- **Modèles Eloquent** :
  - `MediaFile` : Modèle principal pour les fichiers
  - `MediaAttachment` : Modèle pour les associations
  - `MediaConversion` : Modèle pour les conversions
- **Trait `HasMediaFiles`** : Trait réutilisable pour les modèles Eloquent

#### Services
- **MediaStorageService** : Gestion du stockage physique des fichiers
  - Support des disques Laravel (public, local, extensible pour S3)
  - Stratégies de nommage (UUID, hash, date, original)
  - Organisation par date (YYYY/MM)
- **MediaUploadService** : Gestion des uploads
  - Validation des fichiers
  - Upload depuis URL
  - Extraction automatique des métadonnées
- **MediaConversionService** : Gestion des conversions d'images
  - Support Intervention Image et GD natif
  - Presets configurables (thumb, small, medium, large)
  - Génération à la volée

#### Composants Filament
- **MediaPickerUnified** : Composant Filament unifié
  - Interface avec onglets (Bibliothèque / Upload)
  - Sélection multiple ou unique
  - Filtrage par type MIME
  - Affichage des conversions
- **MediaLibrary** : Composant Livewire pour la bibliothèque
  - Vue grille et liste
  - Filtres avancés (type, collection, date, taille)
  - Tri personnalisable
  - Actions en masse (suppression, déplacement)
- **MediaLibraryPicker** : Composant Livewire pour le picker modal
  - Intégration avec MediaPickerUnified
  - Upload direct depuis le picker

#### Composants Filament supplémentaires
- **MediaColumn** : Colonne de table pour afficher les médias
- **MediaEntry** : Entrée d'infolist pour afficher les médias

#### Fonctionnalités
- **Collections de médias** : Organisation par type ou usage
  - Collections singleFile ou multiple
  - Types MIME acceptés par collection
  - Ordre personnalisable
- **Conversions d'images** : Génération automatique de variantes
  - Presets configurables
  - Formats supportés : WebP, JPEG, PNG
  - Options de fit : crop, contain, cover, fill
- **Métadonnées** : Extraction automatique
  - Dimensions (largeur, hauteur)
  - Taille du fichier
  - Type MIME
  - Durée (pour vidéos)
- **Routes** : Routes pour servir les médias
  - `/media-library-pro/serve/{uuid}` : Servir un fichier
  - `/media-library-pro/conversion/{uuid}/{name}` : Servir une conversion

#### Configuration
- Fichier de configuration complet (`config/media-library-pro.php`)
- Support des disques de stockage personnalisés
- Configuration des conversions
- Validation personnalisable
- Options d'affichage (vue par défaut, tri, etc.)

### 🔒 Sécurité

- Utilisation d'UUIDs pour l'identification des fichiers (non séquentiels)
- Support des fichiers privés (infrastructure prête, à implémenter selon les besoins)
- Validation des types MIME
- Validation de la taille des fichiers

### 📚 Documentation

- README.md complet avec exemples
- Guide de contribution (CONTRIBUTING.md)
- Changelog (ce fichier)
- Docblocks PHPDoc sur toutes les méthodes publiques

### 🔧 Améliorations techniques

- Architecture modulaire et extensible
- Services enregistrés en singletons
- Support du soft delete
- Relations Eloquent optimisées
- Support des propriétés personnalisées par attachment

---

## Format des versions

Les versions suivent le format [Semantic Versioning](https://semver.org/lang/fr/) :
- **MAJOR** : Changements incompatibles avec les versions précédentes
- **MINOR** : Nouvelles fonctionnalités rétro-compatibles
- **PATCH** : Corrections de bugs rétro-compatibles

## Types de changements

- **Ajouté** : Nouvelles fonctionnalités
- **Modifié** : Changements dans les fonctionnalités existantes
- **Déprécié** : Fonctionnalités qui seront supprimées dans une future version
- **Supprimé** : Fonctionnalités supprimées
- **Corrigé** : Corrections de bugs
- **Sécurité** : Corrections de vulnérabilités
