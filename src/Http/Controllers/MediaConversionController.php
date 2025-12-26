<?php

namespace Xavier\MediaLibraryPro\Http\Controllers;

use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Models\MediaConversion;
use Xavier\MediaLibraryPro\Services\MediaConversionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaConversionController extends Controller
{
    protected MediaConversionService $conversionService;

    public function __construct(MediaConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    /**
     * Sert une conversion d'image
     */
    public function show(Request $request, string $media, string $conversion): BinaryFileResponse
    {
        // Chercher le MediaFile par UUID ou ID
        $mediaFile = MediaFile::where('uuid', $media)
            ->orWhere('id', $media)
            ->first();

        if (!$mediaFile) {
            abort(404, 'Média non trouvé');
        }

        // Vérifier que c'est une image
        if (!$mediaFile->isImage()) {
            abort(400, 'Les conversions ne sont disponibles que pour les images');
        }

        // Récupérer ou générer la conversion
        $conversionModel = $this->conversionService->getConversion($mediaFile, $conversion);

        if (!$conversionModel) {
            // Générer la conversion si elle n'existe pas
            try {
                $conversionModel = $this->conversionService->convert($mediaFile, $conversion);
            } catch (\Exception $e) {
                abort(500, 'Erreur lors de la génération de la conversion: ' . $e->getMessage());
            }
        }

        $storage = Storage::disk($conversionModel->disk);
        $path = $conversionModel->path;

        // Vérifier que le fichier existe
        if (!$storage->exists($path)) {
            // Régénérer si le fichier n'existe pas
            try {
                $conversionModel = $this->conversionService->regenerate($conversionModel);
                $path = $conversionModel->path;
            } catch (\Exception $e) {
                abort(500, 'Erreur lors de la régénération de la conversion: ' . $e->getMessage());
            }
        }

        // Servir le fichier
        $filePath = $storage->path($path);
        
        $mimeType = match($conversionModel->format) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
        
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $conversionModel->file_name . '"',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
