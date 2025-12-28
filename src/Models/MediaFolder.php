<?php

namespace Xavier\MediaLibraryPro\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MediaFolder extends Model
{
    use HasFactory;

    protected $table = 'media_folders';

    protected $fillable = [
        'name',
        'path',
        'parent_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($folder) {
            if (empty($folder->path)) {
                $folder->path = $folder->generatePath();
            }
        });

        static::updating(function ($folder) {
            if ($folder->isDirty('name') || $folder->isDirty('parent_id')) {
                $folder->path = $folder->generatePath();
            }
        });
    }

    /**
     * Relations
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MediaFolder::class, 'parent_id');
    }

    public function mediaFiles(): HasMany
    {
        return $this->hasMany(MediaFile::class, 'folder_id');
    }

    /**
     * Génère le chemin complet du dossier
     */
    public function generatePath(): string
    {
        if ($this->parent_id) {
            $parent = $this->parent ?? MediaFolder::find($this->parent_id);
            if ($parent) {
                return rtrim($parent->path, '/') . '/' . $this->sanitizeName($this->name);
            }
        }

        return $this->sanitizeName($this->name);
    }

    /**
     * Nettoie le nom du dossier pour le chemin
     */
    protected function sanitizeName(string $name): string
    {
        // Remplacer les caractères invalides par des underscores
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        // Supprimer les underscores multiples
        $name = preg_replace('/_+/', '_', $name);
        // Supprimer les underscores en début/fin
        return trim($name, '_');
    }

    /**
     * Retourne le chemin complet du dossier
     */
    public function getFullPath(): string
    {
        return $this->path;
    }

    /**
     * Retourne le breadcrumb (chemin complet avec séparateurs)
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $folder = $this;

        while ($folder) {
            array_unshift($breadcrumbs, [
                'id' => $folder->id,
                'name' => $folder->name,
                'path' => $folder->path,
            ]);
            $folder = $folder->parent;
        }

        // Ajouter la racine
        array_unshift($breadcrumbs, [
            'id' => null,
            'name' => 'Racine',
            'path' => '',
        ]);

        return $breadcrumbs;
    }

    /**
     * Déplace le dossier vers un nouveau parent
     */
    public function moveTo(?MediaFolder $newParent): bool
    {
        // Vérifier qu'on ne crée pas de boucle
        if ($newParent && $this->isDescendantOf($newParent)) {
            throw new \InvalidArgumentException('Impossible de déplacer un dossier dans son propre descendant');
        }

        $this->parent_id = $newParent?->id;
        $this->save();

        // Mettre à jour le chemin de tous les enfants
        $this->updateChildrenPaths();

        return true;
    }

    /**
     * Vérifie si ce dossier est un descendant de l'autre
     */
    protected function isDescendantOf(MediaFolder $folder): bool
    {
        $current = $this->parent;
        while ($current) {
            if ($current->id === $folder->id) {
                return true;
            }
            $current = $current->parent;
        }
        return false;
    }

    /**
     * Met à jour les chemins de tous les enfants récursivement
     */
    protected function updateChildrenPaths(): void
    {
        foreach ($this->children as $child) {
            $child->path = $child->generatePath();
            $child->save();
            $child->updateChildrenPaths();
        }
    }

    /**
     * Supprime le dossier et tous ses contenus
     */
    public function deleteWithContents(): bool
    {
        // Déplacer les fichiers vers le parent ou la racine
        foreach ($this->mediaFiles as $mediaFile) {
            $mediaFile->folder_id = $this->parent_id;
            $mediaFile->save();
        }

        // Supprimer récursivement les enfants
        foreach ($this->children as $child) {
            $child->deleteWithContents();
        }

        // Supprimer le dossier lui-même
        return $this->delete();
    }

    /**
     * Vérifie si le dossier est la racine (pas de parent)
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }
}





