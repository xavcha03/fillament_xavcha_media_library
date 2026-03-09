# TODO - Roadmap et Améliorations

Ce document liste les fonctionnalités prévues, améliorations et tâches à venir pour le package Media Library Pro.

> **Note** : Les éléments marqués avec ✅ **FAIT** sont déjà implémentés et fonctionnels.

## ✅ Déjà Implémenté

### Fonctionnalités de base
- ✅ **Gestion des dossiers** : Navigation, création, upload dans un dossier, breadcrumb
- ✅ **Modale de détail** : Affichage des détails d'un média (accessible depuis la grille)
- ✅ **Tri par colonne** : Tri par nom, type, collection, taille, date dans la vue liste
- ✅ **Sélection multiple** : Mode sélection avec checkboxes dans les deux vues
- ✅ **Lazy loading** : Images chargées en lazy loading (`loading="lazy"`)
- ✅ **Eager loading** : Prévention N+1 avec `->with(['attachments', 'folder'])`
- ✅ **Index de base de données** : Index sur les colonnes fréquemment filtrées
- ✅ **Colonne order** : Support de l'ordre dans MediaAttachment (géré automatiquement)
- ✅ **Vue grille et liste** : Deux modes d'affichage avec basculement
- ✅ **Filtres avancés** : Filtrage par collection, type MIME, date, taille
- ✅ **Upload avec prévisualisation** : Aperçu des fichiers avant upload
- ✅ **Gestion des collections** : Organisation par collections
- ✅ **Conversions d'images** : Génération de thumbnails et variantes
- ✅ **Interface moderne** : Design soigné avec miniatures compactes

## 🎯 Priorité Haute

### 1. Compression d'images ⚡
- [ ] Implémenter la fonctionnalité de compression d'images
- [ ] Ajouter des options de qualité configurables
- [ ] Compression automatique à l'upload (optionnelle)
- [ ] Interface dans la modale de détail pour compresser manuellement
- [ ] Support de différents algorithmes (JPEG, WebP, AVIF)
- [ ] Prévisualisation avant/après compression

**Fichiers concernés :**
- `src/Services/ImageCompressionService.php` (à créer)
- `src/Livewire/MediaLibrary.php` (méthode `compressImage()`)
- `resources/views/livewire/media-library.blade.php` (bouton compression)

### 2. Recherche en temps réel 🔍
- [ ] Ajouter une barre de recherche dans la toolbar
- [ ] Recherche par nom de fichier
- [ ] Recherche par alt_text et description
- [ ] Mise en surbrillance des résultats
- [ ] Recherche avec debounce pour optimiser les performances
- [ ] Historique de recherche récente

**Fichiers concernés :**
- `src/Livewire/MediaLibrary.php` (propriété `$search` et méthode `updatedSearch()`)
- `resources/views/livewire/media-library.blade.php` (input de recherche)
- `src/Livewire/MediaLibrary.php` (méthode `getMediaQuery()` - ajouter filtre recherche)

### 3. Vue liste améliorée 📋
- [ ] Ouvrir la modale de détail depuis la vue liste (actuellement uniquement en grille) - **À FAIRE** : La modale est accessible depuis la grille mais pas depuis la liste
- [ ] Actions rapides (supprimer, modifier) directement dans la liste
- [ ] Colonnes personnalisables
- [x] Tri par colonne ✅ **FAIT** - Tri disponible par nom, type, collection, taille, date (boutons cliquables dans les en-têtes)
- [x] Sélection multiple améliorée ✅ **FAIT** - Sélection multiple avec selectMode implémentée (checkboxes dans la vue liste)

**Fichiers concernés :**
- `resources/views/livewire/media-library.blade.php` (section vue liste)
- `resources/views/tables/columns/media-column.blade.php` (améliorer)

## 🎨 Améliorations UX/UI

### 4. Drag & drop pour réorganiser 🎯
- [ ] Réorganiser les médias dans une collection par glisser-déposer - **À FAIRE** : Interface drag & drop manquante (la propriété `allowReordering` existe mais pas l'implémentation)
- [x] Modifier l'ordre d'affichage ✅ **FAIT** - Colonne `order` existe dans MediaAttachment, ordre géré automatiquement
- [ ] Feedback visuel pendant le drag - **À FAIRE** : Nécessite l'implémentation du drag & drop
- [x] Sauvegarde automatique de l'ordre ✅ **FAIT** - L'ordre est géré automatiquement lors de l'ajout via `maxOrder + 1`
- [x] Infrastructure prête ✅ **FAIT** - `allowReordering()` existe dans MediaPickerUnified, colonne `order` en base

**Fichiers concernés :**
- `src/Livewire/MediaLibrary.php` (méthode `reorderMedia()` - À IMPLÉMENTER)
- `resources/views/livewire/media-library.blade.php` (ajouter drag & drop - À IMPLÉMENTER)
- `src/Models/MediaAttachment.php` (colonne `order` ✅ DÉJÀ PRÉSENTE)

### 5. Prévisualisation améliorée 🖼️
- [ ] Lightbox pour les images en grand format
- [ ] Navigation précédent/suivant dans la modale de détail
- [ ] Zoom sur les images (pinch-to-zoom sur mobile)
- [ ] Rotation d'images
- [ ] Mode plein écran

**Fichiers concernés :**
- `resources/views/livewire/media-library.blade.php` (modale de détail)
- `src/Livewire/MediaLibrary.php` (méthodes `previousMedia()`, `nextMedia()`)

### 6. Métadonnées EXIF 📸
- [ ] Afficher les données EXIF dans la modale de détail
- [ ] Extraction automatique des métadonnées à l'upload
- [ ] Filtrage par appareil photo, date de prise de vue
- [ ] Affichage des coordonnées GPS (si disponibles)
- [ ] Informations sur l'appareil (marque, modèle, ISO, etc.)

**Fichiers concernés :**
- `src/Models/MediaFile.php` (ajouter colonne `exif_data` JSON)
- `src/Services/MediaUploadService.php` (extraction EXIF)
- `resources/views/livewire/media-library.blade.php` (section métadonnées)

## 🚀 Fonctionnalités métier

### 7. Système de tags 🏷️
- [ ] Implémenter la fonctionnalité de tags (actuellement en TODO)
- [ ] Interface de gestion des tags
- [ ] Filtrage par tags dans la bibliothèque
- [ ] Gestion des tags dans la modale de détail
- [ ] Tags suggérés automatiquement
- [ ] Support de spatie/laravel-tags (optionnel)

**Fichiers concernés :**
- `src/Models/MediaFile.php` (relation tags)
- `src/Livewire/MediaLibrary.php` (méthodes `bulkAddTags()`, `getAvailableTags()`)
- `resources/views/livewire/media-library.blade.php` (interface tags)
- Migration pour table `tags` et `taggables`

### 8. Duplication de médias 📋
- [ ] Bouton "Dupliquer" pour créer une copie
- [ ] Utile pour créer des variantes
- [ ] Option de dupliquer avec ou sans conversions
- [ ] Duplication en masse

**Fichiers concernés :**
- `src/Livewire/MediaLibrary.php` (méthode `duplicateMedia()`)
- `resources/views/livewire/media-library.blade.php` (bouton duplication)

### 9. Watermarking 🎨
- [ ] Ajout de watermark sur les images
- [ ] Configurable par collection
- [ ] Support texte et image
- [ ] Positionnement personnalisable
- [ ] Opacité configurable

**Fichiers concernés :**
- `src/Services/ImageWatermarkService.php` (à créer)
- `config/media-library-pro.php` (configuration watermark)
- `src/Livewire/MediaLibrary.php` (méthode `applyWatermark()`)

## ⚡ Performance et optimisation

### 10. Lazy loading et pagination infinie ♾️
- [x] Lazy loading des images dans la grille ✅ **FAIT** - `loading="lazy"` sur les images
- [ ] Pagination infinie (scroll infini)
- [ ] Cache des conversions
- [ ] Préchargement intelligent des images

**Fichiers concernés :**
- `resources/views/livewire/media-library.blade.php` (lazy loading)
- `src/Livewire/MediaLibrary.php` (pagination infinie)

### 11. Optimisation des requêtes 🔧
- [x] Eager loading pour éviter N+1 ✅ **FAIT** - `->with(['attachments', 'folder'])` dans getMediaProperty()
- [x] Index sur les colonnes fréquemment filtrées ✅ **FAIT** - Index présents dans les migrations (media_file_id, collection_name, order, etc.)
- [ ] Cache des résultats de filtres - **À FAIRE** : Pas encore implémenté
- [ ] Requêtes optimisées avec `select()` spécifique - **À FAIRE** : Pourrait améliorer les performances

**Fichiers concernés :**
- `src/Livewire/MediaLibrary.php` (méthode `getMediaQuery()`)
- Migrations pour ajouter des index

## 🔒 Sécurité et permissions

### 12. Permissions granulaires 🔐
- [ ] Permissions par collection
- [ ] Contrôle d'accès par utilisateur/rôle
- [ ] Audit log des actions
- [ ] Intégration avec Filament Policies

**Fichiers concernés :**
- `src/Policies/MediaFilePolicy.php` (à créer)
- `src/Models/MediaFile.php` (méthodes de permission)
- Migration pour table `media_permissions`

### 13. Audit log 📊
- [ ] Enregistrer toutes les actions (upload, suppression, modification)
- [ ] Interface pour consulter l'historique
- [ ] Export des logs
- [ ] Filtrage par utilisateur, date, action

**Fichiers concernés :**
- Migration pour table `media_audit_logs`
- `src/Models/MediaAuditLog.php` (à créer)
- `src/Services/MediaAuditService.php` (à créer)

## 🌐 Intégrations

### 14. Intégration CDN ☁️
- [ ] Support pour Cloudflare, AWS CloudFront, etc.
- [ ] Upload direct vers S3/Cloud Storage
- [ ] Configuration par collection
- [ ] Synchronisation automatique

**Fichiers concernés :**
- `src/Services/MediaStorageService.php` (support CDN)
- `config/media-library-pro.php` (configuration CDN)

### 15. Webhooks 🔔
- [ ] Événements déclenchés (upload, suppression, modification)
- [ ] Configuration des webhooks
- [ ] Retry automatique en cas d'échec
- [ ] Intégration avec des services externes

**Fichiers concernés :**
- `src/Events/MediaUploaded.php` (à créer)
- `src/Events/MediaDeleted.php` (à créer)
- `src/Events/MediaUpdated.php` (à créer)
- `src/Listeners/SendWebhook.php` (à créer)

## 🧪 Tests et qualité

### 16. Tests complets ✅
- [ ] Tests unitaires pour les services
- [ ] Tests d'intégration pour les composants Livewire
- [ ] Tests E2E pour les workflows
- [ ] Tests de performance
- [ ] Coverage > 80%

**Fichiers concernés :**
- `tests/Unit/` (tests unitaires)
- `tests/Feature/` (tests d'intégration)
- `phpunit.xml` (configuration)

### 17. Documentation améliorée 📚
- [ ] Exemples vidéo/GIF
- [ ] Guide de migration depuis Spatie
- [ ] API documentation avec examples
- [ ] Tutoriels pas à pas
- [ ] FAQ étendue

**Fichiers concernés :**
- `docs/` (nouveau dossier)
- `README.md` (améliorer)
- `MIGRATION.md` (à créer)

## 🛠️ Améliorations techniques

### 18. Refactoring 🔨
- [ ] Extraire la logique métier dans des Actions (Laravel Actions)
- [ ] Utiliser des Form Requests pour la validation
- [ ] Events/Listeners pour les actions importantes
- [ ] Services plus modulaires
- [ ] Réduction de la complexité cyclomatique

**Fichiers concernés :**
- `src/Actions/` (nouveau dossier)
- `src/Http/Requests/` (nouveau dossier)
- Refactoring de `src/Livewire/MediaLibrary.php`

### 19. Monitoring 📈
- [ ] Logging des erreurs structuré
- [ ] Métriques de performance
- [ ] Dashboard de statistiques
- [ ] Alertes en cas de problème

**Fichiers concernés :**
- `src/Services/MediaMetricsService.php` (à créer)
- `src/Http/Controllers/MediaStatsController.php` (à créer)

## ♿ Accessibilité et internationalisation

### 20. Amélioration de l'accessibilité ♿
- [ ] ARIA labels complets
- [ ] Navigation au clavier améliorée
- [ ] Support des lecteurs d'écran
- [ ] Contraste des couleurs conforme WCAG
- [ ] Focus visible sur tous les éléments interactifs

**Fichiers concernés :**
- `resources/views/livewire/media-library.blade.php` (améliorer ARIA)
- `resources/css/media-library-pro.css` (améliorer focus states)

### 21. Internationalisation 🌍
- [ ] Traductions pour toutes les chaînes
- [ ] Support multi-langue
- [ ] Fichiers de traduction (fr, en, es, etc.)
- [ ] Format de dates localisé

**Fichiers concernés :**
- `resources/lang/` (nouveau dossier)
- `resources/views/` (remplacer les chaînes par `__()`)

## 📊 Statistiques et rapports

### 22. Dashboard de statistiques 📊
- [ ] Nombre total de médias
- [ ] Taille totale du stockage
- [ ] Répartition par type MIME
- [ ] Évolution dans le temps
- [ ] Médias les plus utilisés

**Fichiers concernés :**
- `src/Http/Controllers/MediaStatsController.php` (à créer)
- `resources/views/pages/media-stats-page.blade.php` (à créer)

## 🔄 Maintenance et nettoyage

### 23. Nettoyage automatique 🧹
- [ ] Suppression des médias orphelins
- [ ] Nettoyage des conversions non utilisées
- [ ] Commande artisan pour le nettoyage
- [ ] Planification automatique

**Fichiers concernés :**
- `src/Console/Commands/CleanupOrphanedMedia.php` (à créer)
- `src/Console/Commands/CleanupUnusedConversions.php` (à créer)

---

## 📊 Récapitulatif

### ✅ Fonctionnalités Implémentées (Partiellement ou Complètement)

1. **Gestion des dossiers** ✅ - Navigation, création, upload dans un dossier, breadcrumb
2. **Tri par colonne** ✅ - Dans la vue liste (nom, type, collection, taille, date)
3. **Sélection multiple** ✅ - Mode sélection avec checkboxes
4. **Lazy loading** ✅ - Images chargées en lazy loading
5. **Eager loading** ✅ - Prévention N+1 avec relations chargées
6. **Index de base de données** ✅ - Index sur colonnes fréquemment utilisées
7. **Colonne order** ✅ - Support de l'ordre dans MediaAttachment
8. **Modale de détail** ✅ - Accessible depuis la vue grille (pas encore depuis la liste)
9. **Filtres avancés** ✅ - Par collection, type MIME, date, taille
10. **Upload avec prévisualisation** ✅ - Aperçu avant upload
11. **Vue grille et liste** ✅ - Deux modes d'affichage

### ❌ Fonctionnalités Non Implémentées (Priorité Haute)

1. **Recherche en temps réel** - Barre de recherche manquante
2. **Compression d'images** - Service de compression non créé
3. **Navigation précédent/suivant** - Dans la modale de détail
4. **Lightbox** - Pour les images en grand format
5. **Zoom et rotation** - Sur les images
6. **Drag & drop réorganisation** - Interface manquante (ordre existe mais pas l'UI)
7. **Métadonnées EXIF** - Extraction et affichage
8. **Système de tags** - Non implémenté
9. **Duplication de médias** - Non implémenté
10. **Watermarking** - Non implémenté

### 📝 Notes

- Les fonctionnalités sont organisées par priorité et catégorie
- Chaque item peut être développé indépendamment
- Les dates de livraison seront définies selon les besoins
- Les contributions sont les bienvenues pour toutes ces fonctionnalités

## 🤝 Contribution

Si vous souhaitez contribuer à l'une de ces fonctionnalités, consultez [CONTRIBUTING.md](./CONTRIBUTING.md) pour les guidelines.

