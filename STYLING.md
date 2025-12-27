# Guide de Styling pour Packages Filament v4

## ⚠️ Problème Principal

**Filament compile son propre Tailwind CSS, mais ne scanne PAS automatiquement les vues des packages tiers.**

Cela signifie que :
- Les classes Tailwind utilisées dans les vues du package ne sont **pas compilées** par Filament
- Les classes doivent être **définies manuellement** dans le CSS du package
- `@apply` de Tailwind **ne fonctionne pas** car Filament compile son propre Tailwind séparément

## ✅ Solution Recommandée

### 1. Structure du CSS

Le fichier CSS du package (`resources/css/media-library-pro.css`) doit contenir **toutes les classes Tailwind utilisées** dans les vues du package.

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

