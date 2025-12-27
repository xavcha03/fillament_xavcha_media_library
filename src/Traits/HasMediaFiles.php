<?php

namespace Xavier\MediaLibraryPro\Traits;

use Xavier\MediaLibraryPro\Models\MediaAttachment;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

trait HasMediaFiles
{
    /**
     * Relation vers les attachments
     */
    public function mediaAttachments(): MorphMany
    {
        return $this->morphMany(MediaAttachment::class, 'model');
    }

    /**
     * Relation vers les fichiers via les attachments
     */
    public function mediaFiles(): MorphToMany
    {
        return $this->morphToMany(
            MediaFile::class,
            'model',
            'media_attachments',
            'model_id',
            'media_file_id'
        )->withPivot(['collection_name', 'order', 'custom_properties', 'is_primary'])
          ->withTimestamps();
    }

    /**
     * Ajoute un fichier au modèle
     */
    public function addMediaFile(
        string|UploadedFile $file,
        string $collection = 'default',
        ?string $name = null,
        array $customProperties = []
    ): MediaAttachment {
        // Si c'est un UploadedFile, on doit d'abord le stocker via MediaUploadService
        if ($file instanceof UploadedFile) {
            $mediaUploadService = app(\Xavier\MediaLibraryPro\Services\MediaUploadService::class);
            $mediaFile = $mediaUploadService->upload($file, [
                'name' => $name,
            ]);
        } else {
            // Si c'est un chemin de fichier ou une URL
            throw new \InvalidArgumentException('File must be an UploadedFile instance. Use addMediaFromPath() or addMediaFromUrl() instead.');
        }

        return $this->attachMediaFile($mediaFile, $collection, $customProperties);
    }

    /**
     * Ajoute un fichier depuis une URL
     */
    public function addMediaFromUrl(
        string $url,
        string $collection = 'default',
        ?string $name = null,
        array $customProperties = []
    ): MediaAttachment {
        $mediaUploadService = app(\Xavier\MediaLibraryPro\Services\MediaUploadService::class);
        $mediaFile = $mediaUploadService->uploadFromUrl($url, [
            'name' => $name,
        ]);

        return $this->attachMediaFile($mediaFile, $collection, $customProperties);
    }

    /**
     * Ajoute un fichier depuis un chemin
     */
    public function addMediaFromPath(
        string $path,
        string $collection = 'default',
        ?string $name = null,
        array $customProperties = []
    ): MediaAttachment {
        $mediaUploadService = app(\Xavier\MediaLibraryPro\Services\MediaUploadService::class);
        $mediaFile = $mediaUploadService->uploadFromPath($path, [
            'name' => $name,
        ]);

        return $this->attachMediaFile($mediaFile, $collection, $customProperties);
    }

    /**
     * Attache un MediaFile existant au modèle
     */
    public function attachMediaFile(
        MediaFile $mediaFile,
        string $collection = 'default',
        array $customProperties = []
    ): MediaAttachment {
        // Vérifier si la collection est singleFile
        $collections = $this->getRegisteredMediaCollections();
        $isSingleFile = $collections[$collection]['singleFile'] ?? false;

        if ($isSingleFile) {
            // Supprimer les anciens attachments de cette collection
            $this->clearMediaCollection($collection);
        }

        // Déterminer l'ordre
        $maxOrder = $this->mediaAttachments()
            ->where('collection_name', $collection)
            ->max('order') ?? -1;

        // Créer l'attachment
        $attachment = $this->mediaAttachments()->create([
            'media_file_id' => $mediaFile->id,
            'collection_name' => $collection,
            'order' => $maxOrder + 1,
            'custom_properties' => $customProperties,
            'is_primary' => $isSingleFile, // Si singleFile, c'est automatiquement primary
        ]);

        return $attachment;
    }

    /**
     * Récupère tous les fichiers d'une collection
     */
    public function getMediaFiles(?string $collection = null): Collection
    {
        $query = $this->mediaAttachments();

        if ($collection) {
            $query->where('collection_name', $collection);
        }

        return $query->orderBy('order')->get();
    }

    /**
     * Récupère le premier fichier d'une collection
     */
    public function getFirstMediaFile(?string $collection = null): ?MediaAttachment
    {
        return $this->getMediaFiles($collection)->first();
    }

    /**
     * Récupère un fichier spécifique par index
     */
    public function getMediaFile(string $collection, int $index = 0): ?MediaAttachment
    {
        return $this->getMediaFiles($collection)->get($index);
    }

    /**
     * Vérifie si le modèle a des fichiers dans une collection
     */
    public function hasMediaFile(?string $collection = null): bool
    {
        return $this->getMediaFiles($collection)->isNotEmpty();
    }

    /**
     * Compte les fichiers d'une collection
     */
    public function getMediaFileCount(?string $collection = null): int
    {
        return $this->getMediaFiles($collection)->count();
    }

    /**
     * Vide une collection (supprime les attachments, pas les fichiers)
     */
    public function clearMediaCollection(string $collection): void
    {
        $this->mediaAttachments()
            ->where('collection_name', $collection)
            ->delete();
    }

    /**
     * Supprime un fichier spécifique
     */
    public function deleteMediaFile(int $attachmentId): bool
    {
        $attachment = $this->mediaAttachments()->find($attachmentId);
        
        if (!$attachment) {
            return false;
        }

        return $attachment->delete();
    }

    /**
     * Supprime tous les fichiers (attachments seulement)
     */
    public function clearAllMediaCollections(): void
    {
        $this->mediaAttachments()->delete();
    }

    /**
     * Récupère les collections enregistrées
     */
    protected function getRegisteredMediaCollections(): array
    {
        if (method_exists($this, 'registerMediaCollections')) {
            $result = $this->registerMediaCollections();
            // Si registerMediaCollections() retourne un array, l'utiliser
            if (is_array($result)) {
                return $result;
            }
            // Sinon, retourner un array vide (ancienne méthode void)
            return [];
        }

        return [];
    }

    /**
     * Méthode helper pour faciliter la migration depuis Spatie Media Library
     * 
     * Cette méthode est optionnelle et fournie uniquement pour faciliter la migration.
     * Elle est équivalente à getFirstMediaFile().
     * 
     * @param string|null $collection
     * @return MediaAttachment|null
     */
    public function getFirstMedia(?string $collection = null): ?MediaAttachment
    {
        return $this->getFirstMediaFile($collection);
    }

    /**
     * Méthode helper pour faciliter la migration depuis Spatie Media Library
     * 
     * Cette méthode est optionnelle et fournie uniquement pour faciliter la migration.
     * Elle est équivalente à getMediaFiles().
     * 
     * @param string|null $collection
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMedia(?string $collection = null): Collection
    {
        return $this->getMediaFiles($collection);
    }
}

