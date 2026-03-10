<?php

namespace Xavier\MediaLibraryPro\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Xavier\MediaLibraryPro\Models\MediaFile;

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
     * Optimise un MediaFile existant (bouton "Optimiser l'image").
     */
    public function optimizeMediaFile(MediaFile $mediaFile): bool
    {
        if (! $this->enabled) {
            return false;
        }

        if (! $mediaFile->isImage()) {
            return false;
        }

        try {
            $originalSize = $mediaFile->size;

            $newPath = $this->optimizeFile(
                $mediaFile->path,
                $mediaFile->mime_type,
                $mediaFile->disk,
                true,
            );

            if ($newPath && $newPath !== $mediaFile->path) {
                $mediaFile->path = $newPath;
                if ($this->shouldConvertToWebp($mediaFile->mime_type)) {
                    $mediaFile->mime_type = 'image/webp';
                }
            }

            $absolutePath = Storage::disk($mediaFile->disk)->path($mediaFile->path);

            if (file_exists($absolutePath)) {
                $imageInfo = @getimagesize($absolutePath);
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

            Log::info('ImageOptimizationService: Image optimisée', [
                'media_id' => $mediaFile->id,
                'original_size' => $originalSize,
                'new_size' => $newSize,
                'saved' => $savedBytes,
                'saved_percent' => $savedPercent,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('ImageOptimizationService: Erreur lors de l\'optimisation du MediaFile', [
                'media_id' => $mediaFile->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Rotation manuelle d'une image (gauche/droite) + régénération des conversions.
     */
    public function rotateMediaFile(MediaFile $mediaFile, string $direction = 'right'): bool
    {
        if (! $mediaFile->isImage()) {
            return false;
        }

        $direction = $direction === 'left' ? 'left' : 'right';

        try {
            $disk = $mediaFile->disk;
            $relativePath = $mediaFile->path;
            $absolutePath = Storage::disk($disk)->path($relativePath);

            if (! file_exists($absolutePath)) {
                return false;
            }

            // Convention attendue UI:
            // - "gauche" = rotation anti-horaire
            // - "droite" = rotation horaire
            // Intervention et GD n'utilisent pas la même convention de signe,
            // et la branche GD inverse déjà le signe plus bas (imagerotate(..., -$angle)).
            $angle = $direction === 'left' ? -90 : 90;

            if (class_exists(\Intervention\Image\ImageManager::class)) {
                $manager = new \Intervention\Image\ImageManager(
                    new \Intervention\Image\Drivers\Gd\Driver()
                );

                $image = $manager->read($absolutePath);
                $image->rotate($angle);
                $image->save($absolutePath, $this->quality);
            } else {
                $imageInfo = @getimagesize($absolutePath);
                if (! $imageInfo) {
                    return false;
                }

                $sourceType = $imageInfo[2];

                $sourceImage = match ($sourceType) {
                    IMAGETYPE_JPEG => @imagecreatefromjpeg($absolutePath),
                    IMAGETYPE_PNG => @imagecreatefrompng($absolutePath),
                    IMAGETYPE_GIF => @imagecreatefromgif($absolutePath),
                    IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : null,
                    default => null,
                };

                if (! $sourceImage) {
                    return false;
                }

                $bgColor = imagecolorallocatealpha($sourceImage, 0, 0, 0, 127);
                $rotated = imagerotate($sourceImage, -$angle, $bgColor);
                imagesavealpha($rotated, true);

                switch ($sourceType) {
                    case IMAGETYPE_JPEG:
                        imagejpeg($rotated, $absolutePath, $this->quality);
                        break;
                    case IMAGETYPE_PNG:
                        imagepng($rotated, $absolutePath, 9);
                        break;
                    case IMAGETYPE_GIF:
                        imagegif($rotated, $absolutePath);
                        break;
                    case IMAGETYPE_WEBP:
                        if (function_exists('imagewebp')) {
                            imagewebp($rotated, $absolutePath, $this->quality);
                        }
                        break;
                }

                imagedestroy($sourceImage);
                imagedestroy($rotated);
            }

            $imageInfo = @getimagesize($absolutePath);
            if ($imageInfo) {
                $mediaFile->width = $imageInfo[0];
                $mediaFile->height = $imageInfo[1];
            }
            $mediaFile->size = filesize($absolutePath);
            $mediaFile->save();

            if ($mediaFile->conversions()->exists()) {
                $conversionService = app(MediaConversionService::class);
                foreach ($mediaFile->conversions as $conversion) {
                    $conversionService->regenerate($conversion);
                }
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('ImageOptimizationService: Erreur lors de la rotation de l\'image', [
                'media_id' => $mediaFile->id,
                'direction' => $direction,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Optimise une image pendant le pipeline d'upload.
     *
     * @return string|null Chemin relatif du nouveau fichier si conversion WebP, sinon null.
     */
    public function optimize(string $filePath, string $mimeType, ?string $disk = null): ?string
    {
        return $this->optimizeFile($filePath, $mimeType, $disk, false);
    }

    /**
     * Coeur de l'optimisation (appelée à l'upload et manuellement).
     */
    protected function optimizeFile(string $filePath, string $mimeType, ?string $disk = null, bool $force = false): ?string
    {
        if (! $this->enabled || (! $this->autoOptimize && ! $force)) {
            return null;
        }

        if (! str_starts_with($mimeType, 'image/')) {
            return null;
        }

        try {
            $absolutePath = $this->getAbsolutePath($filePath, $disk);

            if (! file_exists($absolutePath)) {
                Log::warning("ImageOptimizationService: Fichier introuvable: {$absolutePath}");

                return null;
            }

            $this->resizeIfNeeded($absolutePath);

            $newRelativePath = null;
            if ($this->convertToWebp && $this->shouldConvertToWebp($mimeType)) {
                $newRelativePath = $this->convertToWebp($filePath, $absolutePath, $disk);
            }

            $finalAbsolutePath = $newRelativePath
                ? $this->getAbsolutePath($newRelativePath, $disk)
                : $absolutePath;

            $this->optimizeWithSpatie($finalAbsolutePath, $mimeType);

            return $newRelativePath;
        } catch (\Throwable $e) {
            Log::error('ImageOptimizationService: Erreur lors de l\'optimisation', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Redimensionne l'image si elle dépasse les dimensions max.
     */
    protected function resizeIfNeeded(string $filePath): void
    {
        if (! $this->maxWidth && ! $this->maxHeight) {
            return;
        }

        try {
            $imageInfo = @getimagesize($filePath);
            if (! $imageInfo) {
                return;
            }

            $currentWidth = $imageInfo[0];
            $currentHeight = $imageInfo[1];

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

            if (! $needsResize) {
                return;
            }

            if (class_exists(\Intervention\Image\ImageManager::class)) {
                $this->resizeWithIntervention($filePath, $newWidth, $newHeight);
            } else {
                $this->resizeWithGD($filePath, $newWidth, $newHeight);
            }
        } catch (\Throwable $e) {
            Log::warning('ImageOptimizationService: Erreur lors du redimensionnement', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function resizeWithIntervention(string $filePath, int $width, int $height): void
    {
        $manager = new \Intervention\Image\ImageManager(
            new \Intervention\Image\Drivers\Gd\Driver()
        );

        $image = $manager->read($filePath);
        $image->scale($width, $height);

        $saveQuality = max(75, $this->quality - 5);
        $image->save($filePath, $saveQuality);
    }

    protected function resizeWithGD(string $filePath, int $width, int $height): void
    {
        $imageInfo = @getimagesize($filePath);
        if (! $imageInfo) {
            return;
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $sourceType = $imageInfo[2];

        $sourceImage = match ($sourceType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($filePath),
            IMAGETYPE_PNG => @imagecreatefrompng($filePath),
            IMAGETYPE_GIF => @imagecreatefromgif($filePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($filePath) : null,
            default => null,
        };

        if (! $sourceImage) {
            return;
        }

        $newImage = imagecreatetruecolor($width, $height);

        if ($sourceType === IMAGETYPE_PNG || $sourceType === IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefill($newImage, 0, 0, $transparent);
        }

        imagecopyresampled(
            $newImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $width,
            $height,
            $sourceWidth,
            $sourceHeight,
        );

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $saveQuality = max(75, $this->quality - 5);

        match ($extension) {
            'jpg', 'jpeg' => imagejpeg($newImage, $filePath, $saveQuality),
            'png' => imagepng($newImage, $filePath, 9),
            'webp' => function_exists('imagewebp') ? imagewebp($newImage, $filePath, $saveQuality) : null,
            'gif' => imagegif($newImage, $filePath),
            default => imagejpeg($newImage, $filePath, $saveQuality),
        };

        imagedestroy($sourceImage);
        imagedestroy($newImage);
    }

    protected function convertToWebp(string $relativePath, string $absolutePath, ?string $disk = null): ?string
    {
        if (! function_exists('imagewebp')) {
            Log::warning('ImageOptimizationService: imagewebp() non disponible');

            return null;
        }

        try {
            $imageInfo = @getimagesize($absolutePath);
            if (! $imageInfo) {
                return null;
            }

            $sourceType = $imageInfo[2];

            $sourceImage = match ($sourceType) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($absolutePath),
                IMAGETYPE_PNG => @imagecreatefrompng($absolutePath),
                default => null,
            };

            if (! $sourceImage) {
                return null;
            }

            $newRelativePath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $relativePath);
            $newAbsolutePath = $this->getAbsolutePath($newRelativePath, $disk);

            $dir = dirname($newAbsolutePath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $webpQuality = max(75, $this->quality - 5);
            imagewebp($sourceImage, $newAbsolutePath, $webpQuality);
            imagedestroy($sourceImage);

            if (! $this->preserveOriginal) {
                @unlink($absolutePath);
                if ($disk) {
                    try {
                        Storage::disk($disk)->delete($relativePath);
                    } catch (\Throwable $e) {
                        // Ignorer
                    }
                }
            }

            return $newRelativePath;
        } catch (\Throwable $e) {
            Log::error('ImageOptimizationService: Erreur lors de la conversion WebP', [
                'file' => $relativePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function shouldConvertToWebp(string $mimeType): bool
    {
        return in_array($mimeType, ['image/jpeg', 'image/png'], true) && $this->convertToWebp;
    }

    protected function optimizeWithSpatie(string $filePath, string $mimeType): void
    {
        if (! class_exists(\Spatie\ImageOptimizer\OptimizerChainFactory::class)) {
            return;
        }

        try {
            $optimizerChain = new \Spatie\ImageOptimizer\OptimizerChain();

            if (in_array($mimeType, ['image/jpeg', 'image/jpg'], true)) {
                $jpegQuality = max(70, $this->quality - 5);

                $optimizerChain->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Jpegoptim([
                    '--max='.$jpegQuality,
                    '--strip-all',
                    '--all-progressive',
                ]));
            }

            if ($mimeType === 'image/png') {
                $optimizerChain->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Pngquant([
                    '--quality='.max(65, $this->quality - 10).'-'.$this->quality,
                    '--force',
                ]));

                $optimizerChain->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Optipng([
                    '-i0',
                    '-o2',
                    '-quiet',
                ]));
            }

            if ($mimeType === 'image/webp') {
                $optimizerChain->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Cwebp([
                    '-m 6',
                    '-pass 10',
                    '-mt',
                    '-q '.$this->quality,
                ]));
            }

            if (count($optimizerChain->getOptimizers()) === 0) {
                $optimizerChain = \Spatie\ImageOptimizer\OptimizerChainFactory::create();
            }

            $optimizerChain->optimize($filePath);
        } catch (\Throwable $e) {
            Log::debug('ImageOptimizationService: Spatie Image Optimizer non disponible', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function getAbsolutePath(string $filePath, ?string $disk = null): string
    {
        if ($disk) {
            return Storage::disk($disk)->path($filePath);
        }

        if (str_starts_with($filePath, '/')) {
            return $filePath;
        }

        $defaultDisk = config('media-library-pro.storage.disk', 'public');

        return Storage::disk($defaultDisk)->path($filePath);
    }
}

