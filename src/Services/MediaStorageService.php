<?php

namespace Xavier\MediaLibraryPro\Services;

use Xavier\MediaLibraryPro\Models\MediaFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorageService
{
    protected string $defaultDisk;
    protected string $basePath;

    public function __construct()
    {
        $this->defaultDisk = config('media-library-pro.storage.disk', 'public');
        $this->basePath = config('media-library-pro.storage.path', 'media');
    }

    /**
     * Stocke un fichier et retourne un MediaFile
     */
    public function store(UploadedFile|string $file, ?string $disk = null, ?string $name = null): MediaFile
    {
        $disk = $disk ?? $this->defaultDisk;
        $storage = Storage::disk($disk);

        // Si c'est un UploadedFile
        if ($file instanceof UploadedFile) {
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();
            $extension = $file->getClientOriginalExtension();
        } else {
            // Si c'est un chemin de fichier
            $originalName = basename($file);
            $mimeType = mime_content_type($file);
            $size = filesize($file);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
        }

        // Générer le nom de stockage
        $storedName = $this->generateStoredName($originalName, $extension);
        
        // Générer le chemin
        $path = $this->generatePath($storedName);

        // Construire le chemin complet
        $fullPath = $this->basePath . '/' . $path;
        
        // Stocker le fichier
        if ($file instanceof UploadedFile) {
            $storedPath = $file->storeAs($this->basePath . '/' . dirname($path), basename($path), $disk);
            // storeAs() retourne le chemin relatif depuis la racine du disque
            // Utiliser ce chemin au lieu de construire manuellement
            $fullPath = $storedPath;
        } else {
            $content = file_get_contents($file);
            $storedPath = $storage->put($fullPath, $content);
            // put() retourne le chemin ou false
            if ($storedPath) {
                $fullPath = $storedPath;
            }
        }

        // Optimiser l'image si c'est une image et que l'optimisation est activée
        if (str_starts_with($mimeType, 'image/')) {
            try {
                $optimizationService = app(ImageOptimizationService::class);
                $newPath = $optimizationService->optimize($fullPath, $mimeType, $disk);
                
                // Si l'image a été convertie en WebP, mettre à jour le chemin et le type MIME
                if ($newPath) {
                    $fullPath = $newPath;
                    $mimeType = 'image/webp';
                    $extension = 'webp';
                    $storedName = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $storedName);
                }
            } catch (\Exception $e) {
                // Ignorer les erreurs d'optimisation, continuer avec le fichier original
            }
        }

        // Extraire les dimensions si c'est une image (après optimisation)
        $width = null;
        $height = null;
        if (str_starts_with($mimeType, 'image/')) {
            try {
                $absolutePath = $storage->path($fullPath);
                $imageInfo = getimagesize($absolutePath);
                if ($imageInfo) {
                    $width = $imageInfo[0];
                    $height = $imageInfo[1];
                }
            } catch (\Exception $e) {
                // Ignorer les erreurs d'extraction
            }
        }

        // Mettre à jour la taille après optimisation
        if ($storage->exists($fullPath)) {
            $size = $storage->size($fullPath);
        }

        // Créer le MediaFile
        $mediaFile = MediaFile::create([
            'file_name' => $name ?? $originalName,
            'stored_name' => $storedName,
            'disk' => $disk,
            'path' => $fullPath, // Utiliser le chemin retourné par storeAs/put
            'mime_type' => $mimeType,
            'size' => $size,
            'width' => $width,
            'height' => $height,
        ]);

        return $mediaFile;
    }

    /**
     * Supprime un fichier physique
     */
    public function delete(MediaFile $mediaFile): bool
    {
        $storage = Storage::disk($mediaFile->disk);

        if ($storage->exists($mediaFile->path)) {
            return $storage->delete($mediaFile->path);
        }

        return false;
    }

    /**
     * Récupère l'URL d'un fichier
     */
    public function getUrl(MediaFile $mediaFile): string
    {
        $storage = Storage::disk($mediaFile->disk);

        if ($mediaFile->disk === 'public' || $mediaFile->disk === 'local') {
            return $storage->url($mediaFile->path);
        }

        // Pour les disques cloud (S3, etc.) - futur
        return $storage->url($mediaFile->path);
    }

    /**
     * Récupère le chemin physique d'un fichier
     */
    public function getPath(MediaFile $mediaFile): string
    {
        return Storage::disk($mediaFile->disk)->path($mediaFile->path);
    }

    /**
     * Génère un nom de stockage unique
     */
    protected function generateStoredName(string $originalName, string $extension): string
    {
        $naming = config('media-library-pro.storage.naming', 'hash');

        switch ($naming) {
            case 'hash':
                return Str::random(40) . '.' . $extension;
            case 'date':
                return date('Y/m/d') . '/' . Str::random(20) . '.' . $extension;
            case 'original':
            default:
                return Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . Str::random(10) . '.' . $extension;
        }
    }

    /**
     * Génère le chemin de stockage
     */
    protected function generatePath(string $storedName): string
    {
        $naming = config('media-library-pro.storage.naming', 'hash');

        if ($naming === 'date') {
            return date('Y/m/d') . '/' . $storedName;
        }

        // Organiser par année/mois pour éviter trop de fichiers dans un seul dossier
        return date('Y/m') . '/' . $storedName;
    }

    /**
     * Déplace un fichier vers un autre disque (pour futur)
     */
    public function move(MediaFile $mediaFile, string $newDisk): bool
    {
        // Fonctionnalité future : migration de fichiers entre différents disques de stockage
        // Cette fonctionnalité sera disponible dans une prochaine version
        return false;
    }
}

