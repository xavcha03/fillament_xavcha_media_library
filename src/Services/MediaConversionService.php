<?php

namespace Xavier\MediaLibraryPro\Services;

use Xavier\MediaLibraryPro\Models\MediaConversion;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaConversionService
{
    protected MediaStorageService $storageService;

    public function __construct(MediaStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Génère une conversion pour un MediaFile
     */
    public function convert(MediaFile $mediaFile, string $conversionName, ?array $config = null): MediaConversion
    {
        // Vérifier si c'est une image
        if (!$mediaFile->isImage()) {
            throw new \InvalidArgumentException("Les conversions ne sont disponibles que pour les images.");
        }

        // Vérifier si la conversion existe déjà
        $existing = $mediaFile->conversions()
            ->where('conversion_name', $conversionName)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Récupérer la configuration
        $config = $config ?? $this->getConversionConfig($conversionName);

        if (!$config) {
            throw new \InvalidArgumentException("Configuration de conversion '{$conversionName}' introuvable.");
        }

        // Générer la conversion
        $conversion = $this->generateConversion($mediaFile, $conversionName, $config);

        return $conversion;
    }

    /**
     * Régénère une conversion existante
     */
    public function regenerate(MediaConversion $conversion): MediaConversion
    {
        // Supprimer l'ancien fichier
        $conversion->deleteFile();

        // Récupérer la configuration
        $config = $this->getConversionConfig($conversion->conversion_name);

        // Régénérer
        $newConversion = $this->generateConversion(
            $conversion->mediaFile,
            $conversion->conversion_name,
            $config
        );

        // Supprimer l'ancien enregistrement
        $conversion->delete();

        return $newConversion;
    }

    /**
     * Récupère une conversion existante
     */
    public function getConversion(MediaFile $mediaFile, string $conversionName): ?MediaConversion
    {
        return $mediaFile->conversions()
            ->where('conversion_name', $conversionName)
            ->first();
    }

    /**
     * Vérifie si une conversion existe
     */
    public function hasConversion(MediaFile $mediaFile, string $conversionName): bool
    {
        return $this->getConversion($mediaFile, $conversionName) !== null;
    }

    /**
     * Génère la conversion d'image
     */
    protected function generateConversion(MediaFile $mediaFile, string $conversionName, array $config): MediaConversion
    {
        $sourcePath = $this->storageService->getPath($mediaFile);
        $sourceDisk = $mediaFile->disk;

        // Utiliser Intervention Image si disponible, sinon GD
        if (class_exists(\Intervention\Image\ImageManager::class)) {
            return $this->generateWithIntervention($mediaFile, $conversionName, $config, $sourcePath, $sourceDisk);
        } else {
            return $this->generateWithGD($mediaFile, $conversionName, $config, $sourcePath, $sourceDisk);
        }
    }

    /**
     * Génère avec Intervention Image
     */
    protected function generateWithIntervention(
        MediaFile $mediaFile,
        string $conversionName,
        array $config,
        string $sourcePath,
        string $sourceDisk
    ): MediaConversion {
        $manager = new \Intervention\Image\ImageManager(
            new \Intervention\Image\Drivers\Gd\Driver()
        );

        $image = $manager->read($sourcePath);

        // Appliquer les transformations
        $fit = $config['fit'] ?? 'contain';
        $width = $config['width'] ?? null;
        $height = $config['height'] ?? null;
        $quality = $config['quality'] ?? 85;

        if ($fit === 'crop' && $width && $height) {
            $image->cover($width, $height);
        } elseif ($fit === 'contain') {
            $image->scale($width, $height);
        } else {
            if ($width) {
                $image->resize($width, null);
            }
            if ($height) {
                $image->resize(null, $height);
            }
        }

        // Générer le nom de fichier
        $extension = $config['format'] ?? $mediaFile->getExtension();
        $fileName = Str::random(20) . '.' . $extension;
        $conversionPath = 'conversions/' . date('Y/m') . '/' . $fileName;

        // Sauvegarder
        $conversionDisk = $config['disk'] ?? $sourceDisk;
        $storage = Storage::disk($conversionDisk);
        $fullPath = config('media-library-pro.storage.path', 'media') . '/' . $conversionPath;

        $image->save($storage->path($fullPath), $quality);

        // Créer l'enregistrement
        $conversion = MediaConversion::create([
            'media_file_id' => $mediaFile->id,
            'conversion_name' => $conversionName,
            'file_name' => $fileName,
            'disk' => $conversionDisk,
            'path' => config('media-library-pro.storage.path', 'media') . '/' . $conversionPath,
            'width' => $image->width(),
            'height' => $image->height(),
            'size' => filesize($storage->path($fullPath)),
            'quality' => $quality,
            'format' => $extension,
            'generated_at' => now(),
        ]);

        return $conversion;
    }

    /**
     * Génère avec GD natif (fallback)
     */
    protected function generateWithGD(
        MediaFile $mediaFile,
        string $conversionName,
        array $config,
        string $sourcePath,
        string $sourceDisk
    ): MediaConversion {
        // Lire l'image source
        $sourceInfo = getimagesize($sourcePath);
        if (!$sourceInfo) {
            throw new \RuntimeException("Impossible de lire l'image source.");
        }

        $sourceWidth = $sourceInfo[0];
        $sourceHeight = $sourceInfo[1];
        $sourceType = $sourceInfo[2];

        // Créer l'image source selon le type
        switch ($sourceType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new \RuntimeException("Type d'image non supporté.");
        }

        // Calculer les dimensions
        $fit = $config['fit'] ?? 'contain';
        $targetWidth = $config['width'] ?? $sourceWidth;
        $targetHeight = $config['height'] ?? $sourceHeight;

        if ($fit === 'crop' && $targetWidth && $targetHeight) {
            $newWidth = $targetWidth;
            $newHeight = $targetHeight;
        } else {
            $ratio = $sourceWidth / $sourceHeight;
            if ($targetWidth && $targetHeight) {
                $newWidth = min($targetWidth, $sourceWidth);
                $newHeight = min($targetHeight, $sourceHeight);
                if ($newWidth / $newHeight > $ratio) {
                    $newWidth = $newHeight * $ratio;
                } else {
                    $newHeight = $newWidth / $ratio;
                }
            } elseif ($targetWidth) {
                $newWidth = $targetWidth;
                $newHeight = $targetWidth / $ratio;
            } elseif ($targetHeight) {
                $newHeight = $targetHeight;
                $newWidth = $targetHeight * $ratio;
            } else {
                $newWidth = $sourceWidth;
                $newHeight = $sourceHeight;
            }
        }

        // Créer la nouvelle image
        $newImage = imagecreatetruecolor((int)$newWidth, (int)$newHeight);

        // Préserver la transparence pour PNG et GIF
        if ($sourceType === IMAGETYPE_PNG || $sourceType === IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefill($newImage, 0, 0, $transparent);
        }

        // Redimensionner
        imagecopyresampled(
            $newImage,
            $sourceImage,
            0, 0, 0, 0,
            (int)$newWidth, (int)$newHeight,
            $sourceWidth, $sourceHeight
        );

        // Générer le nom de fichier
        $extension = $config['format'] ?? 'jpg';
        $fileName = Str::random(20) . '.' . $extension;
        $conversionPath = 'conversions/' . date('Y/m') . '/' . $fileName;

        // Sauvegarder
        $conversionDisk = $config['disk'] ?? $sourceDisk;
        $storage = Storage::disk($conversionDisk);
        $fullPath = config('media-library-pro.storage.path', 'media') . '/' . $conversionPath;
        $fullPathAbsolute = $storage->path($fullPath);

        // Créer le dossier si nécessaire
        $dir = dirname($fullPathAbsolute);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $quality = $config['quality'] ?? 85;

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($newImage, $fullPathAbsolute, $quality);
                break;
            case 'png':
                imagepng($newImage, $fullPathAbsolute, 9);
                break;
            case 'webp':
                imagewebp($newImage, $fullPathAbsolute, $quality);
                break;
            default:
                imagejpeg($newImage, $fullPathAbsolute, $quality);
        }

        // Libérer la mémoire
        imagedestroy($sourceImage);
        imagedestroy($newImage);

        // Créer l'enregistrement
        $conversion = MediaConversion::create([
            'media_file_id' => $mediaFile->id,
            'conversion_name' => $conversionName,
            'file_name' => $fileName,
            'disk' => $conversionDisk,
            'path' => config('media-library-pro.storage.path', 'media') . '/' . $conversionPath,
            'width' => (int)$newWidth,
            'height' => (int)$newHeight,
            'size' => filesize($fullPathAbsolute),
            'quality' => $quality,
            'format' => $extension,
            'generated_at' => now(),
        ]);

        return $conversion;
    }

    /**
     * Récupère la configuration d'une conversion
     */
    protected function getConversionConfig(string $conversionName): ?array
    {
        $presets = config('media-library-pro.conversions.presets', []);

        return $presets[$conversionName] ?? null;
    }
}





