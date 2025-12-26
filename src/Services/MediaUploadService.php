<?php

namespace Xavier\MediaLibraryPro\Services;

use Xavier\MediaLibraryPro\Models\MediaFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MediaUploadService
{
    protected MediaStorageService $storageService;

    public function __construct(MediaStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Upload un fichier
     */
    public function upload(UploadedFile $file, array $options = []): MediaFile
    {
        // Valider le fichier
        $this->validate($file, $options['rules'] ?? []);

        // Stocker le fichier
        $mediaFile = $this->storageService->store(
            $file,
            $options['disk'] ?? null,
            $options['name'] ?? null
        );

        // Extraire les métadonnées
        $metadata = $this->extractMetadata($file);
        if (!empty($metadata)) {
            $mediaFile->update(['metadata' => $metadata]);
        }

        return $mediaFile;
    }

    /**
     * Upload depuis une URL
     */
    public function uploadFromUrl(string $url, array $options = []): MediaFile
    {
        // Télécharger le fichier
        $contents = file_get_contents($url);
        if ($contents === false) {
            throw new \RuntimeException("Impossible de télécharger le fichier depuis l'URL: {$url}");
        }

        // Créer un fichier temporaire
        $tempFile = tempnam(sys_get_temp_dir(), 'media_upload_');
        file_put_contents($tempFile, $contents);

        // Déterminer le nom et le type MIME
        $name = $options['name'] ?? basename(parse_url($url, PHP_URL_PATH));
        $mimeType = mime_content_type($tempFile);

        // Créer un UploadedFile factice
        $uploadedFile = new UploadedFile(
            $tempFile,
            $name,
            $mimeType,
            null,
            true // test mode
        );

        try {
            $mediaFile = $this->upload($uploadedFile, $options);
        } finally {
            // Nettoyer le fichier temporaire
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        return $mediaFile;
    }

    /**
     * Upload depuis un chemin de fichier
     */
    public function uploadFromPath(string $path, array $options = []): MediaFile
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Le fichier n'existe pas: {$path}");
        }

        $name = $options['name'] ?? basename($path);
        $mimeType = mime_content_type($path);

        // Créer un UploadedFile factice
        $uploadedFile = new UploadedFile(
            $path,
            $name,
            $mimeType,
            null,
            true // test mode
        );

        return $this->upload($uploadedFile, $options);
    }

    /**
     * Valide un fichier
     */
    public function validate(UploadedFile $file, array $customRules = []): bool
    {
        $defaultRules = [
            'file',
            'max:' . (config('media-library-pro.validation.max_size', 10240) * 1024), // En bytes
        ];

        // Vérifier les types MIME autorisés
        $allowedMimeTypes = config('media-library-pro.validation.allowed_mime_types', []);
        if (!empty($allowedMimeTypes)) {
            $defaultRules[] = 'mimetypes:' . implode(',', $allowedMimeTypes);
        }

        $rules = array_merge($defaultRules, $customRules);

        $validator = Validator::make(['file' => $file], ['file' => $rules]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return true;
    }

    /**
     * Extrait les métadonnées d'un fichier
     */
    public function extractMetadata(UploadedFile|string $file): array
    {
        $metadata = [];

        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $mimeType = $file instanceof UploadedFile ? $file->getMimeType() : mime_content_type($file);

        // Métadonnées EXIF pour les images
        if (str_starts_with($mimeType, 'image/') && function_exists('exif_read_data')) {
            try {
                $exif = @exif_read_data($path);
                if ($exif) {
                    $metadata['exif'] = [
                        'camera' => $exif['Make'] ?? null,
                        'model' => $exif['Model'] ?? null,
                        'date' => $exif['DateTime'] ?? null,
                        'orientation' => $exif['Orientation'] ?? null,
                        'iso' => $exif['ISOSpeedRatings'] ?? null,
                        'aperture' => $exif['COMPUTED']['ApertureFNumber'] ?? null,
                        'focal_length' => $exif['FocalLength'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                // Ignorer les erreurs EXIF
            }
        }

        // Métadonnées de base
        $metadata['uploaded_at'] = now()->toIso8601String();
        $metadata['original_name'] = $file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file);

        return $metadata;
    }
}





