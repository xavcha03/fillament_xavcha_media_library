# Changelog

Tous les changements notables de ce package seront documentés dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère à [Semantic Versioning](https://semver.org/lang/fr/).

## [1.2.0] - 2025-03-09

### ✨ Ajouté - Refonte UX de la sélection

#### État de sélection unifié
- **`selectedMediaIds`** et **`lastSelectedId`** : état partagé entre bibliothèque et picker
- Méthodes Livewire : `toggleSelect()`, `rangeSelect()`, `clearSelection()`, `selectAllInPage()`, `selectAll()`, `applyDragSelection()`, `confirmSelection()`

#### Interactions sur les cartes média
- **Clic** : sélectionne/désélectionne un média
- **Ctrl/Cmd + clic** : ajoute ou retire de la sélection
- **Shift + clic** : sélection par plage (range select)
- **Double-clic** : bibliothèque → ouvre les détails ; picker → valide et insère
- **Checkbox** toujours visible sur chaque carte

#### Toolbar contextuelle (bibliothèque)
- Affichage dès qu’au moins un média est sélectionné
- Compteur « X médias sélectionnés »
- Actions groupées (menu déroulant : Supprimer)
- Boutons : « Tout sélectionner », « Sélectionner tout dans la page », « Annuler »
- Suppression du bouton « Sélectionner » (remplacé par la toolbar)

#### Drag-select
- Sélection rectangulaire en glissant sur la grille (bibliothèque et picker)
- Ctrl/Cmd au relâchement pour ajouter à la sélection
- Implémentation Alpine.js dans `alpine-init.blade.php`

#### Picker (MediaPickerUnified)
- Barre en bas : compteur, boutons « Annuler » et « Insérer »
- Double-clic sur une carte pour valider et insérer
- Comportement aligné avec la bibliothèque globale

#### Styles
- Classes `.media-card` et `.media-drag-select-box` dans `media-library-pro.css`
- Surlignage et feedback visuel pour la sélection

### ✨ Ajouté - Rotation manuelle & optimisation auto

#### Rotation manuelle dans l’UI
- Nouvelles méthodes Livewire `rotateLeft()` et `rotateRight()` dans `MediaLibrary`.
- Service `ImageOptimizationService::rotateMediaFile()` pour pivoter l’original et régénérer les conversions associées.
- Deux boutons « Pivoter à gauche » / « Pivoter à droite » ajoutés dans le panneau de détails de la bibliothèque.
- Le package **n’applique plus de correction EXIF automatique** : l’image est importée telle quelle, et l’utilisateur corrige si besoin via ces actions.

#### Feedback Uploading / Processing
- Dans le picker : badge « Upload en cours… » pendant le transfert, puis bouton avec état « Traitement en cours… » pendant le traitement serveur.
- Dans la bibliothèque : état de bouton « Traitement en cours… » pendant l’upload (traitement complet côté serveur).

#### Métadonnées visibles
- Stockage de l’orientation EXIF brute dans `metadata['orientation']` lors de l’upload.
- Affichage de l’orientation d’origine dans le panneau « Informations détaillées » de la modale de détails.

---

## [1.1.0] - 2025-01-15

### ✨ Ajouté - Phase 1 : Actions configurables et gestion des dossiers

#### Gestion des dossiers
- **Table `media_folders`** : Organisation hiérarchique des médias
- **Modèle `MediaFolder`** : Gestion des dossiers avec relations parent/enfant
- **Service `MediaFolderService`** : Création, suppression et déplacement de dossiers
- **Navigation par dossiers** : Breadcrumbs et navigation dans la bibliothèque
- **Colonne `folder_id`** : Ajout de la relation dossier aux fichiers médias

#### Système d'actions configurables
- **Trait `HasMediaAction`** : Trait de base pour toutes les actions média
- **Actions Filament** :
  - `CreateFolderAction` : Créer un dossier
  - `CreateFolderBulkAction` : Créer un dossier et déplacer des fichiers
  - `DeleteAction` : Supprimer un fichier
  - `DeleteBulkAction` : Supprimer en masse
  - `DownloadAction` : Télécharger un fichier
  - `MoveAction` : Déplacer un fichier vers un dossier
  - `MoveBulkAction` : Déplacer en masse
  - `RenameAction` : Renommer un fichier
  - `SelectFileAction` : Sélectionner un fichier
  - `UploadAction` : Uploader des fichiers
- **Configuration des actions** : Méthode `::configureUsing()` pour personnaliser chaque action

#### Fonctionnalités ajoutées
- **Renommage de fichiers** : Interface pour renommer les fichiers avec validation
- **Téléchargement de fichiers** : Contrôleur et route pour télécharger les médias
- **Déplacement de fichiers** : Interface pour déplacer les fichiers entre dossiers
- **Création de dossiers** : Interface pour créer des dossiers hiérarchiques

#### Améliorations de l'interface
- **Breadcrumbs** : Navigation par dossiers avec fil d'Ariane
- **Affichage des dossiers** : Grille des dossiers enfants dans la bibliothèque
- **Boutons d'actions améliorés** : Design moderne avec icônes colorées
- **Modales** : Modales pour renommer, créer des dossiers et déplacer

#### Configuration
- Section `folders` dans la configuration
- Section `actions` pour activer/désactiver les actions
- Options de profondeur maximale des dossiers

#### Routes
- Route `/media-library-pro/download/{media}` : Téléchargement de fichiers

---

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

