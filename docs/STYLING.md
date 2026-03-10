# Guide de Styling pour Packages Filament v4

## ⚠️ Problème Principal

**Filament compile son propre Tailwind CSS, mais ne scanne PAS automatiquement les vues des packages tiers.**

Cela signifie que :
- Les classes Tailwind utilisées dans les vues du package ne sont **pas compilées** par Filament
- Les classes doivent être **définies manuellement** dans le CSS du package
- `@apply` de Tailwind **ne fonctionne pas** car Filament compile son propre Tailwind séparément

> **Conséquence pratique (à retenir)** : dans les vues Blade du package, une classe peut apparaître dans le HTML… **sans aucun style appliqué**. Un layout “cassé” n’est pas forcément un bug HTML : c’est très souvent une **utility manquante**.

## ✅ Solution Recommandée

### 1. Structure du CSS

Le fichier CSS du package (`resources/css/media-library-pro.css`) doit contenir **toutes les classes Tailwind utilisées** dans les vues du package.

## 🧭 Diagnostic express (symptôme → cause → action)

### 1) “Mes 2 colonnes n’occupent que ~50% de la modale”
- **Symptôme** : tu as une grille du type `md:grid-cols-4`, et deux blocs censés “span” (ex: `md:col-span-3` / `md:col-span-1`). Visuellement, chaque bloc ressemble à une colonne étroite et il reste du vide.
- **Cause probable** : les utilities `col-span-*` (ou la variante responsive `md:col-span-*`) **ne sont pas présentes** dans le CSS effectivement chargé.
  - Dans ce cas, chaque enfant reste sur **1 colonne** par défaut.
  - Exemple concret : `grid-cols-4` → 4 colonnes ; si les spans manquent : 1/4 + 1/4 = **50%** utilisé, 2 colonnes vides.
- **Action recommandée (la plus robuste)** : ne pas dépendre des spans Tailwind pour les layouts structurants dans un package.
  - Utiliser des **classes préfixées** (ex: `mlp-*`) + un **CSS “scopé”** qui définit explicitement la grille (ratio 3/1, 2/1, etc.).
  - Avantage : tu n’as **pas** à maintenir des dizaines de classes utilitaires juste pour tenir une mise en page.

### 2) “Les styles d’une classe existent dans le HTML mais ne font rien”
- **Cause probable** : la classe n’est pas compilée / pas incluse dans le CSS Filament chargé.
- **Action** : soit tu ajoutes la règle dans `resources/css/media-library-pro.css`, soit tu remplaces la dépendance à l’utility par un petit CSS dédié (souvent plus simple pour les layouts).

### 3) “Responsive/dark/hover ne marche pas”
- **Cause probable** : tu as la classe dans le HTML (`md:*`, `dark:*`, `hover:*`, `focus:*`) mais tu n’as pas la variante correspondante dans ton CSS.
- **Action** : ajouter la variante explicitement dans le CSS (media queries, sélecteurs `.dark`, pseudo-classes).

## ✅ Pattern recommandé pour éviter de perdre du temps (layouts “structurants”)

### Principe
- **Dans les vues Blade du package** : garder Tailwind pour le “décor” (espacements, couleurs, typo), mais éviter d’en faire dépendre la **structure** (grilles, spans, ratios) si tu n’es pas sûr que toutes les utilities sont disponibles.
- **Pour la structure** : utiliser **des classes métier préfixées** + un CSS minimal, lisible, stable.

### Pattern “grille 2 colonnes ratio 3/1”
- **Quand l’utiliser** : preview + aside, header à gauche / actions à droite, etc.
- **Pourquoi** : remplace `grid-cols-*` + `col-span-*` (fragiles en contexte package) par une règle unique.

Checklist d’implémentation (sans rentrer dans le code ici) :
- Créer un wrapper avec une classe du type `mlp-detail-grid`
- Définir en CSS :
  - 1 colonne en mobile
  - 2 colonnes en desktop avec `grid-template-columns: 3fr 1fr`
- Ajouter `min-width: 0` aux colonnes si tu as du `truncate` / du texte long (sinon ça déborde)
- Placer ce CSS :
  - soit dans `resources/css/media-library-pro.css` (recommandé si réutilisé)
  - soit “scopé” dans la vue avec `@once <style> ... </style> @endonce` (utile pour un fix local rapide, sans polluer le CSS global)

## 🧪 Mini-checklist “avant de chercher 30 minutes”
- Inspecter l’élément et vérifier : **la classe est bien dans le DOM** ?
- Si oui, vérifier dans l’onglet “Styles” : **une règle CSS existe** pour cette classe ?
  - **Aucune règle** → utility manquante → ajouter la règle ou remplacer par CSS préfixé.
- Si une règle existe mais est barrée : **conflit de spécificité** (Filament peut surcharger). Solution : scope plus précis, classe préfixée, ou réorganiser le DOM.

### 2. Organisation du CSS

Organiser le CSS par sections logiques :

```css
/* ============================================
   CLASSES CRITIQUES POUR LA MODALE
   ============================================ */

/* Backdrop et overlay */
.bg-black\/70 {
    background-color: rgba(0, 0, 0, 0.7);
}

.backdrop-blur-md {
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

/* Transform et scale */
.scale-\[0\.97\] {
    transform: scale(0.97);
}

/* ... etc ... */
```

### 3. Classes à Toujours Inclure

#### Classes avec valeurs spéciales (échappement nécessaire)
- `bg-black/70` → `.bg-black\/70`
- `scale-[0.97]` → `.scale-\[0\.97\]`
- `border-4` → `.border-4`
- `space-y-1.5` → `.space-y-1\.5`
- `ml-13` → `.ml-13` (valeurs personnalisées)

#### Classes de base essentielles
- Layout : `flex`, `grid`, `flex-col`, `items-center`, `justify-between`
- Spacing : `p-*`, `px-*`, `py-*`, `m-*`, `mt-*`, `mb-*`, `space-y-*`, `gap-*`
- Typography : `text-*`, `font-*`, `text-center`, `truncate`
- Colors : `bg-*`, `text-*`, `border-*` (avec variantes dark mode)
- Borders : `border`, `border-2`, `border-4`, `border-dashed`, `rounded-*`
- Shadows : `shadow-*`, `ring-*`
- Positioning : `relative`, `absolute`, `fixed`, `inset-0`, `z-*`
- Sizing : `w-*`, `h-*`, `max-w-*`, `max-h-*`
- Transitions : `transition-*`, `duration-*`
- Transforms : `transform`, `scale-*`
- Opacity : `opacity-*`
- Overflow : `overflow-*`
- Cursor : `cursor-pointer`
- Utilities : `sr-only`, `pointer-events-*`

#### Classes responsive

```css
@media (min-width: 640px) {
    .sm\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    
    .sm\:text-sm {
        font-size: 0.875rem;
        line-height: 1.25rem;
    }
}
```

#### Classes dark mode

```css
.dark .bg-gray-800,
.dark\:bg-gray-800 {
    background-color: rgb(31 41 55);
}

.dark .text-white,
.dark\:text-white {
    color: rgb(255 255 255);
}
```

#### Classes hover/focus

```css
.hover\:bg-gray-50:hover {
    background-color: rgb(249 250 251);
}

.focus\:ring-2:focus {
    --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
    --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
    box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}
```

### 4. Enregistrement du CSS dans le ServiceProvider

```php
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;

// Dans la méthode boot()
$this->app->booted(function () {
    FilamentAsset::register(
        [
            Css::make('media-library-pro', __DIR__ . '/../../resources/css/media-library-pro.css'),
        ],
        package: 'media-library-pro'
    );
});
```

## 🔍 Comment Identifier les Classes Manquantes

### Méthode 1 : Inspection du navigateur
1. Ouvrir les outils de développement (F12)
2. Inspecter l'élément qui ne s'affiche pas correctement
3. Vérifier si les classes Tailwind sont appliquées
4. Si les classes sont présentes dans le HTML mais pas de styles → elles manquent dans le CSS

### Méthode 2 : Recherche dans les vues

```bash
# Chercher toutes les classes utilisées dans les vues
grep -r "class=" packages/xavcha/fillament-xavcha-media-library/resources/views/
```

### Méthode 3 : Test systématique

1. Ouvrir la page avec la modale
2. Si quelque chose ne s'affiche pas → vérifier les classes dans le HTML
3. Ajouter les classes manquantes au CSS
4. Recompiler : `ddev artisan view:clear && ddev artisan filament:assets`

## ❌ Erreurs à Éviter

### 1. Ne PAS utiliser `@apply`

```css
/* ❌ NE FONCTIONNE PAS */
.my-class {
    @apply bg-primary-500 text-white;
}

/* ✅ CORRECT */
.my-class {
    background-color: rgb(245 158 11);
    color: rgb(255 255 255);
}
```

### 2. Ne PAS supprimer trop de classes
- Ne pas simplifier le CSS en pensant que Filament les compile
- Filament ne scanne PAS les vues des packages
- Toutes les classes utilisées doivent être définies

### 3. Ne PAS oublier les variantes
- Dark mode : `.dark .class` ET `.dark\:class`
- Responsive : `@media (min-width: 640px) { .sm\:class }`
- Hover : `.hover\:class:hover`
- Focus : `.focus\:class:focus`
- Group hover : `.group-hover\:class:hover`

### 4. Ne PAS oublier l'échappement

```css
/* ❌ INCORRECT */
.bg-black/70 { }

/* ✅ CORRECT */
.bg-black\/70 { }
```

## 📋 Checklist lors de l'ajout de nouvelles classes

- [ ] Vérifier si la classe existe déjà dans le CSS
- [ ] Ajouter la classe avec sa définition complète
- [ ] Ajouter les variantes dark mode si nécessaire
- [ ] Ajouter les variantes responsive si nécessaire
- [ ] Ajouter les variantes hover/focus si nécessaire
- [ ] Tester dans le navigateur
- [ ] Recompiler les assets : `ddev artisan view:clear && ddev artisan filament:assets`

## 🎨 Classes pour la sélection et le drag-select

Les cartes média et la zone de sélection rectangulaire utilisent des classes dédiées :

| Classe | Usage |
|--------|-------|
| `.media-card` | Carte média dans la grille (transition, états hover/sélectionné) |
| `.media-card.ring-2` | État sélectionné (bordure/ring coloré) |
| `.media-drag-select-box` | Rectangle de sélection lors du drag-select (position fixe, z-index élevé) |

Ces classes sont définies dans `resources/css/media-library-pro.css`. Si vous personnalisez l’apparence des cartes ou du rectangle de sélection, modifiez ces règles.

## 🎨 Exemple Complet : Zone de Drag & Drop

### Dans la vue Blade :

```blade
<label
    class="relative block border-2 border-dashed rounded-2xl p-12 text-center transition-all duration-300 cursor-pointer hover:border-primary-400 hover:bg-gradient-to-br hover:from-primary-50 hover:to-white dark:hover:from-primary-900/10 dark:hover:to-gray-800 hover:shadow-lg group min-h-[280px] flex items-center justify-center"
>
```

### Dans le CSS (toutes ces classes doivent être définies) :

```css
/* Layout */
.relative { position: relative; }
.block { display: block; }
.flex { display: flex; }
.items-center { align-items: center; }
.justify-center { justify-content: center; }

/* Borders */
.border-2 { border-width: 2px; }
.border-dashed { border-style: dashed; }
.rounded-2xl { border-radius: 1rem; }

/* Spacing */
.p-12 { padding: 3rem; }

/* Typography */
.text-center { text-align: center; }

/* Transitions */
.transition-all { transition-property: all; ... }
.duration-300 { transition-duration: 300ms; }

/* Cursor */
.cursor-pointer { cursor: pointer; }

/* Hover states */
.hover\:border-primary-400:hover { border-color: rgb(251 191 36); }
.hover\:bg-gradient-to-br:hover { background-image: linear-gradient(...); }
.hover\:from-primary-50:hover { --tw-gradient-from: rgb(255 247 237); ... }
.hover\:to-white:hover { --tw-gradient-to: rgb(255 255 255); }
.hover\:shadow-lg:hover { box-shadow: ...; }

/* Dark mode hover */
.dark .hover\:from-primary-900\/10:hover { --tw-gradient-from: rgb(120 53 15 / 0.1); ... }
.dark .hover\:to-gray-800:hover { --tw-gradient-to: rgb(31 41 55); }

/* Sizing personnalisé */
.min-h-\[280px\] { min-height: 280px; }

/* Group */
.group { /* pour group-hover */ }
```

## 🔄 Workflow de Développement

1. **Modifier la vue Blade** avec les classes Tailwind souhaitées
2. **Vérifier dans le navigateur** si les styles s'appliquent
3. **Si les styles ne s'appliquent pas** :
   - Inspecter l'élément dans le navigateur
   - Identifier les classes manquantes
   - Ajouter les classes au CSS avec leurs définitions complètes
4. **Recompiler les assets** :

```bash
ddev artisan view:clear
ddev artisan filament:assets
```

5. **Tester à nouveau** dans le navigateur

## 📚 Références

- [Documentation Filament CSS Hooks](https://filamentphp.com/docs/4.x/styling/css-hooks)
- [Documentation Tailwind CSS](https://tailwindcss.com/docs)
- [Filament Asset Registration](https://filamentphp.com/docs/4.x/advanced/assets)

## 💡 Astuces

1. **Grouper les classes similaires** : regrouper toutes les classes `bg-*`, `text-*`, etc.
2. **Commenter les sections** : utiliser des commentaires pour organiser le CSS
3. **Tester régulièrement** : après chaque ajout de classes, tester dans le navigateur
4. **Utiliser les outils de développement** : inspecter les éléments pour voir quelles classes sont appliquées
5. **Maintenir une liste** : garder une liste des classes utilisées dans chaque composant

## ⚡ Commandes Utiles

```bash
# Vider le cache des vues
ddev artisan view:clear

# Recompiler les assets Filament
ddev artisan filament:assets

# Les deux en une fois
ddev artisan view:clear && ddev artisan filament:assets
```

---

**Rappel Important** : Filament ne compile PAS automatiquement les classes Tailwind des packages. Toutes les classes utilisées dans les vues doivent être définies manuellement dans le CSS du package.

## 🖼️ Images, rotation et cache dans l’admin

### 1. Rotation manuelle ≠ simple “pivot visuel”

Dans la modale de détail :

- Les actions **« Pivoter à gauche / à droite »** utilisent le service `ImageOptimizationService` pour **réécrire physiquement le fichier** sur le disque (et régénérer les conversions).
- Ce n’est pas une simple rotation CSS : le fichier original (et ses thumbnails) changent réellement de dimensions / orientation.

### 2. Cache HTTP et paramètre `t` (versionning)

Pour éviter de se battre avec le cache :

- Toutes les URLs générées par le package vers `media-library-pro.serve` / `media-library-pro.conversion` ajoutent un paramètre `t` basé sur :
  - `updated_at->timestamp` si disponible
  - sinon `size` puis `time()` en fallback
- Les contrôleurs appliquent la règle suivante :
  - **avec `t`** : `Cache-Control: public, max-age=..., immutable`
  - **sans `t`** : `Cache-Control: no-cache, no-store, must-revalidate`

En pratique : après une rotation ou une optimisation, l’`updated_at` change → **nouvelle URL** → le navigateur recharge l’image corrigée.

### 3. Désactiver complètement le cache dans l’admin Filament

Si tu développes principalement en contexte admin (Filament), tu peux forcer le **no-cache global** pour ce package :

```php
// config/media-library-pro.php

'http_cache' => [
    // false = pas de cache navigateur/CDN sur les routes du package
    'enabled' => false,
    'max_age' => 31536000, // utile uniquement si enabled = true
],
```

- Recommandé pour : **environnements de dev / préprod / back-office uniquement**.
- Pour un front public, tu peux laisser `enabled = true` et profiter du cache long tout en gardant la sécurité du paramètre `t`.

