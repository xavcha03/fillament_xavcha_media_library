<?php

namespace Xavier\MediaLibraryPro\Services;

use Xavier\MediaLibraryPro\Models\MediaFolder;
use Illuminate\Support\Str;

class MediaFolderService
{
    /**
     * Crée un nouveau dossier
     */
    public function create(string $name, ?MediaFolder $parent = null): MediaFolder
    {
        $this->validateName($name);

        // Vérifier qu'un dossier avec le même nom n'existe pas dans le même parent
        $existingFolder = MediaFolder::where('parent_id', $parent?->id)
            ->where('name', $name)
            ->first();

        if ($existingFolder) {
            throw new \InvalidArgumentException("Un dossier avec le nom '{$name}' existe déjà dans ce répertoire");
        }

        $folder = new MediaFolder([
            'name' => $name,
            'parent_id' => $parent?->id,
        ]);

        $folder->save();

        return $folder;
    }

    /**
     * Supprime un dossier
     */
    public function delete(MediaFolder $folder): bool
    {
        return $folder->deleteWithContents();
    }

    /**
     * Déplace un dossier vers un nouveau parent
     */
    public function move(MediaFolder $folder, ?MediaFolder $newParent): bool
    {
        return $folder->moveTo($newParent);
    }

    /**
     * Génère un chemin unique pour un dossier
     */
    public function getPath(MediaFolder $folder): string
    {
        return $folder->getFullPath();
    }

    /**
     * Valide un nom de dossier
     */
    public function validatePath(string $path): bool
    {
        // Vérifier que le chemin ne contient pas de caractères interdits
        if (preg_match('/[<>:"|?*]/', $path)) {
            return false;
        }

        // Vérifier que le chemin ne commence/termine pas par un slash
        if (str_starts_with($path, '/') || str_ends_with($path, '/')) {
            return false;
        }

        // Vérifier qu'il n'y a pas de segments vides
        $segments = explode('/', $path);
        foreach ($segments as $segment) {
            if (empty(trim($segment))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Valide un nom de dossier
     */
    protected function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Le nom du dossier ne peut pas être vide');
        }

        if (strlen($name) > 255) {
            throw new \InvalidArgumentException('Le nom du dossier ne peut pas dépasser 255 caractères');
        }

        // Vérifier les caractères interdits
        if (preg_match('/[<>:"|?*\/\\\]/', $name)) {
            throw new \InvalidArgumentException('Le nom du dossier contient des caractères interdits');
        }
    }

    /**
     * Trouve ou crée un dossier à partir d'un chemin
     */
    public function findOrCreateByPath(string $path): MediaFolder
    {
        if (!$this->validatePath($path)) {
            throw new \InvalidArgumentException("Le chemin '{$path}' n'est pas valide");
        }

        $segments = explode('/', $path);
        $currentParent = null;

        foreach ($segments as $segment) {
            $folder = MediaFolder::where('name', $segment)
                ->where('parent_id', $currentParent?->id)
                ->first();

            if (!$folder) {
                $folder = $this->create($segment, $currentParent);
            }

            $currentParent = $folder;
        }

        return $currentParent;
    }

    /**
     * Récupère tous les dossiers racine (sans parent)
     */
    public function getRootFolders(): \Illuminate\Database\Eloquent\Collection
    {
        return MediaFolder::whereNull('parent_id')->orderBy('name')->get();
    }

    /**
     * Récupère les dossiers enfants d'un parent
     */
    public function getChildFolders(?MediaFolder $parent = null): \Illuminate\Database\Eloquent\Collection
    {
        return MediaFolder::where('parent_id', $parent?->id)
            ->orderBy('name')
            ->get();
    }
}






