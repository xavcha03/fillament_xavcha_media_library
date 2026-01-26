<?php

namespace Xavier\MediaLibraryPro\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageOptimizationService
{
    protected bool $enabled;
    protected bool $autoOptimize;
    protected ?int $maxWidth;
    protected ?int $maxHeight;
    protected int $quality;
    protected bool $convertToWebp;
    protected bool $preserveOriginal;
    protected bool $queueEnabled;

    public function __construct()
    {
        $config = config('media-library-pro.optimization', []);
        $this->enabled = $config['enabled'] ?? false;
        $this->autoOptimize = $config['auto_optimize'] ?? true;
        $this->maxWidth = $config['max_width'] ?? 1920;
        $this->maxHeight = $config['max_height'] ?? 1920;
        $this->quality = $config['quality'] ?? 85;
        $this->convertToWebp = $config['convert_to_webp'] ?? false;
        $this->preserveOriginal = $config['preserve_original'] ?? false;
        $this->queueEnabled = $config['queue'] ?? false;
    }

    /**
     * Optimise un MediaFile existant
     * 
     * @param \Xavier\MediaLibraryPro\Models\MediaFile $mediaFile
     * @return bool True si l'optimisation a réussi
     */
    public function optimizeMediaFile(\Xavier\MediaLibraryPro\Models\MediaFile $mediaFile): bool
    {
        // Pour l'optimisation manuelle, on vérifie seulement que le service est activé
        // (pas besoin que auto_optimize soit true)
        if (!$this->enabled) {
            return false;
        }

        if (!$mediaFile->isImage()) {
            return false;
        }

        try {
            $originalSize = $mediaFile->size;
            $originalPath = $mediaFile->path;
            
            // Optimiser l'image (forcer l'optimisation même si auto_optimize est false)
            $newPath = $this->optimizeFile($mediaFile->path, $mediaFile->mime_type, $mediaFile->disk, true);
            
            // Si conversion WebP, mettre à jour le MediaFile
            if ($newPath && $newPath !== $mediaFile->path) {
                $mediaFile->path = $newPath;
                $mediaFile->mime_type = 'image/webp';
                $mediaFile->stored_name = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $mediaFile->stored_name);
            }
            
            // Mettre à jour les dimensions et la taille
            $absolutePath = Storage::disk($mediaFile->disk)->path($mediaFile->path);
            if (file_exists($absolutePath)) {
                $imageInfo = getimagesize($absolutePath);
                if ($imageInfo) {
                    $mediaFile->width = $imageInfo[0];
                    $mediaFile->height = $imageInfo[1];
                }
                $mediaFile->size = filesize($absolutePath);
            }
            
            $mediaFile->save();
            
            $newSize = $mediaFile->size;
            $savedBytes = $originalSize - $newSize;
            $savedPercent = $originalSize > 0 ? round(($savedBytes / $originalSize) * 100, 1) : 0;
            
            Log::info("ImageOptimizationService: Image optimisée", [
                'media_id' => $mediaFile->id,
                'original_size' => $originalSize,
                'new_size' => $newSize,
                'saved' => $savedBytes,
                'saved_percent' => $savedPercent,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("ImageOptimizationService: Erreur lors de l'optimisation du MediaFile", [
                'media_id' => $mediaFile->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Optimise une image
     * 
     * @return string|null Retourne le nouveau chemin relatif si conversion WebP, null sinon
     */
    public function optimize(string $filePath, string $mimeType, ?string $disk = null): ?string
    {
        return $this->optimizeFile($filePath, $mimeType, $disk, false);
    }

    /**
     * Optimise une image (méthode interne)
     * 
     * @param string $filePath Chemin relatif du fichier
     * @param string $mimeType Type MIME
     * @param string|null $disk Disque de storage
     * @param bool $force Forcer l'optimisation même si auto_optimize est false
     * @return string|null Retourne le nouveau chemin relatif si conversion WebP, null sinon
     */
    protected function optimizeFile(string $filePath, string $mimeType, ?string $disk = null, bool $force = false): ?string
    {
        if (!$this->enabled || (!$this->autoOptimize && !$force)) {
            return null;
        }

        if (!str_starts_with($mimeType, 'image/')) {
            return null;
        }

        try {
            // Si c'est un chemin de storage, récupérer le chemin absolu
            $absolutePath = $this->getAbsolutePath($filePath, $disk);
            
            if (!file_exists($absolutePath)) {
                Log::warning("ImageOptimizationService: Fichier introuvable: {$absolutePath}");
                return null;
            }

            // Redimensionner si nécessaire
            $this->resizeIfNeeded($absolutePath, $mimeType);

            // Gérer l'orientation EXIF
            $this->fixOrientation($absolutePath, $mimeType);

            // Convertir en WebP si configuré
            $newRelativePath = null;
            if ($this->convertToWebp && $this->shouldConvertToWebp($mimeType)) {
                $newRelativePath = $this->convertToWebp($filePath, $absolutePath, $disk);
            }

            // Optimiser avec Spatie Image Optimizer (sur le fichier final)
            $finalPath = $newRelativePath ? $this->getAbsolutePath($newRelativePath, $disk) : $absolutePath;
            $this->optimizeWithSpatie($finalPath, $mimeType);

            return $newRelativePath;
        } catch (\Exception $e) {
            Log::error("ImageOptimizationService: Erreur lors de l'optimisation", [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Redimensionne l'image si elle dépasse les dimensions max
     */
    protected function resizeIfNeeded(string $filePath, string $mimeType): void
    {
        if (!$this->maxWidth && !$this->maxHeight) {
            return;
        }

        try {
            $imageInfo = @getimagesize($filePath);
            if (!$imageInfo) {
                return;
            }

            $currentWidth = $imageInfo[0];
            $currentHeight = $imageInfo[1];

            // Vérifier si un redimensionnement est nécessaire
            $needsResize = false;
            $newWidth = $currentWidth;
            $newHeight = $currentHeight;

            if ($this->maxWidth && $currentWidth > $this->maxWidth) {
                $needsResize = true;
                $ratio = $currentHeight / $currentWidth;
                $newWidth = $this->maxWidth;
                $newHeight = (int) ($this->maxWidth * $ratio);
            }

            if ($this->maxHeight && $newHeight > $this->maxHeight) {
                $needsResize = true;
                $ratio = $newWidth / $newHeight;
                $newHeight = $this->maxHeight;
                $newWidth = (int) ($this->maxHeight * $ratio);
            }

            if (!$needsResize) {
                return;
            }

            // Utiliser Intervention Image si disponible, sinon GD
            if (class_exists(\Intervention\Image\ImageManager::class)) {
                $this->resizeWithIntervention($filePath, $newWidth, $newHeight, $mimeType);
            } else {
                $this->resizeWithGD($filePath, $newWidth, $newHeight, $mimeType);
            }
        } catch (\Exception $e) {
            Log::warning("ImageOptimizationService: Erreur lors du redimensionnement", [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Redimensionne avec Intervention Image
     */
    protected function resizeWithIntervention(string $filePath, int $width, int $height, string $mimeType): void
    {
        $manager = new \Intervention\Image\ImageManager(
            new \Intervention\Image\Drivers\Gd\Driver()
        );

        $image = $manager->read($filePath);
        $image->scale($width, $height);
        // Utiliser une qualité légèrement réduite pour le redimensionnement (plus de compression)
        $saveQuality = max(75, $this->quality - 5);
        $image->save($filePath, $saveQuality);
    }

    /**
     * Redimensionne avec GD natif
     */
    protected function resizeWithGD(string $filePath, int $width, int $height, string $mimeType): void
    {
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            return;
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $sourceType = $imageInfo[2];

        // Créer l'image source
        $sourceImage = match ($sourceType) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($filePath),
            IMAGETYPE_PNG => imagecreatefrompng($filePath),
            IMAGETYPE_GIF => imagecreatefromgif($filePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($filePath),
            default => null,
        };

        if (!$sourceImage) {
            return;
        }

        // Créer la nouvelle image
        $newImage = imagecreatetruecolor($width, $height);

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
            $width, $height,
            $sourceWidth, $sourceHeight
        );

        // Sauvegarder avec qualité optimisée pour le web
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $saveQuality = max(75, $this->quality - 5); // Réduire légèrement pour plus de compression
        match (strtolower($extension)) {
            'jpg', 'jpeg' => imagejpeg($newImage, $filePath, $saveQuality),
            'png' => imagepng($newImage, $filePath, 9),
            'webp' => imagewebp($newImage, $filePath, $saveQuality),
            'gif' => imagegif($newImage, $filePath),
            default => imagejpeg($newImage, $filePath, $saveQuality),
        };

        // Libérer la mémoire
        imagedestroy($sourceImage);
        imagedestroy($newImage);
    }

    /**
     * Corrige l'orientation EXIF
     */
    protected function fixOrientation(string $filePath, string $mimeType): void
    {
        if (!function_exists('exif_read_data')) {
            return;
        }

        try {
            $exif = @exif_read_data($filePath);
            if (!$exif || !isset($exif['Orientation'])) {
                return;
            }

            $orientation = $exif['Orientation'];
            if ($orientation === 1) {
                return; // Pas de rotation nécessaire
            }

            // Utiliser Intervention Image si disponible
            if (class_exists(\Intervention\Image\ImageManager::class)) {
                $manager = new \Intervention\Image\ImageManager(
                    new \Intervention\Image\Drivers\Gd\Driver()
                );
                $image = $manager->read($filePath);
                
                $image->orientate();
                $image->save($filePath, $this->quality);
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs d'orientation
        }
    }

    /**
     * Convertit l'image en WebP
     * 
     * @param string $relativePath Chemin relatif du fichier original
     * @param string $absolutePath Chemin absolu du fichier original
     * @param string|null $disk Disque de storage
     * @return string|null Chemin relatif du nouveau fichier WebP
     */
    protected function convertToWebp(string $relativePath, string $absolutePath, ?string $disk = null): ?string
    {
        if (!function_exists('imagewebp')) {
            Log::warning("ImageOptimizationService: imagewebp() non disponible");
            return null;
        }

        try {
            $imageInfo = getimagesize($absolutePath);
            if (!$imageInfo) {
                return null;
            }

            $sourceType = $imageInfo[2];
            $sourceImage = match ($sourceType) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($absolutePath),
                IMAGETYPE_PNG => imagecreatefrompng($absolutePath),
                default => null,
            };

            if (!$sourceImage) {
                return null;
            }

            // Générer le nouveau chemin relatif
            $newRelativePath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $relativePath);
            $newAbsolutePath = $this->getAbsolutePath($newRelativePath, $disk);
            
            // Créer le répertoire si nécessaire
            $dir = dirname($newAbsolutePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Créer l'image WebP avec qualité optimisée
            $webpQuality = max(75, $this->quality - 5); // Légèrement réduite pour plus de compression
            imagewebp($sourceImage, $newAbsolutePath, $webpQuality);
            imagedestroy($sourceImage);

            // Supprimer l'ancien fichier si on ne préserve pas l'original
            if (!$this->preserveOriginal) {
                @unlink($absolutePath);
                // Supprimer aussi depuis le storage si c'est un disque
                if ($disk) {
                    try {
                        Storage::disk($disk)->delete($relativePath);
                    } catch (\Exception $e) {
                        // Ignorer
                    }
                }
            }

            return $newRelativePath;
        } catch (\Exception $e) {
            Log::error("ImageOptimizationService: Erreur lors de la conversion WebP", [
                'file' => $relativePath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Vérifie si on doit convertir en WebP
     */
    protected function shouldConvertToWebp(string $mimeType): bool
    {
        return in_array($mimeType, ['image/jpeg', 'image/png']) && $this->convertToWebp;
    }

    /**
     * Optimise avec Spatie Image Optimizer
     */
    protected function optimizeWithSpatie(string $filePath, string $mimeType): void
    {
        if (!class_exists(\Spatie\ImageOptimizer\OptimizerChainFactory::class)) {
            return;
        }

        try {
            // Créer une chaîne d'optimisation personnalisée avec des paramètres agressifs
            $optimizerChain = new \Spatie\ImageOptimizer\OptimizerChain();
            
            // Configuration pour JPEG avec compression agressive
            if (in_array($mimeType, ['image/jpeg', 'image/jpg'])) {
                // Utiliser une qualité légèrement inférieure pour jpegoptim (plus agressif)
                $jpegQuality = max(70, $this->quality - 5); // Réduire de 5 points pour plus de compression
                $optimizerChain->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Jpegoptim([
                    '--max=' . $jpegQuality, // Qualité max (légèrement réduite pour plus de compression)
                    '--strip-all', // Supprimer toutes les métadonnées
                    '--all-progressive', // JPEG progressif (meilleure compression)
                ]));
            }
            
            // Configuration pour PNG
            if ($mimeType === 'image/png') {
                // Utiliser pngquant d'abord (meilleure compression)
                $optimizerChain->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Pngquant([
                    '--quality=' . max(65, $this->quality - 10) . '-' . $this->quality, // Plus agressif
                    '--force',
                ]));
                // Puis optipng pour optimiser davantage
                $optimizerChain->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Optipng([
                    '-i0', // Pas d'interlacing
                    '-o2', // Niveau d'optimisation 2 (bon compromis vitesse/compression)
                    '-quiet',
                ]));
            }
            
            // Configuration pour WebP
            if ($mimeType === 'image/webp') {
                $optimizerChain->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Cwebp([
                    '-m 6', // Méthode de compression (0-6, 6 = meilleure compression)
                    '-pass 10', // Nombre de passes (plus = meilleure compression)
                    '-mt', // Multi-threading
                    '-q ' . $this->quality, // Qualité
                ]));
            }
            
            // Si aucune configuration spécifique, utiliser la chaîne par défaut
            if (count($optimizerChain->getOptimizers()) === 0) {
                $optimizerChain = \Spatie\ImageOptimizer\OptimizerChainFactory::create();
            }
            
            $optimizerChain->optimize($filePath);
        } catch (\Exception $e) {
            // Si les outils CLI ne sont pas installés, on ignore silencieusement
            // L'optimisation de base (redimensionnement, etc.) a déjà été faite
            Log::debug("ImageOptimizationService: Spatie Image Optimizer non disponible", [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Récupère le chemin absolu d'un fichier
     */
    protected function getAbsolutePath(string $filePath, ?string $disk = null): string
    {
        if ($disk) {
            return Storage::disk($disk)->path($filePath);
        }

        // Si c'est déjà un chemin absolu
        if (str_starts_with($filePath, '/')) {
            return $filePath;
        }

        // Sinon, essayer avec le disque par défaut
        $defaultDisk = config('media-library-pro.storage.disk', 'public');
        return Storage::disk($defaultDisk)->path($filePath);
    }
}
