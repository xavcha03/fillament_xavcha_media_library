<?php

namespace Xavier\MediaLibraryPro\Http\Controllers;

use Xavier\MediaLibraryPro\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaDownloadController extends Controller
{
    /**
     * Télécharge un fichier média via son UUID
     */
    public function download(Request $request, string $media): BinaryFileResponse|\Illuminate\Http\Response
    {
        try {
            // Chercher par UUID ou ID
            $mediaFile = MediaFile::where('uuid', $media)
                ->orWhere('id', $media)
                ->first();

            if (!$mediaFile) {
                abort(404, 'Média non trouvé');
            }

            // Vérifier les permissions pour les fichiers privés
            if (!$mediaFile->is_public) {
                if (!auth()->check()) {
                    abort(403, 'Accès non autorisé. Vous devez être connecté pour télécharger ce fichier.');
                }

                $policy = Gate::getPolicyFor($mediaFile);
                if ($policy !== null) {
                    $user = auth()->user();
                    $allowed = $user->can('download', $mediaFile) || $user->can('view', $mediaFile);
                    if (!$allowed) {
                        abort(403, 'Accès non autorisé à ce fichier.');
                    }
                }
            }

            $storage = Storage::disk($mediaFile->disk);
            $path = $mediaFile->path;

            // Vérifier que le fichier existe
            if (!$storage->exists($path)) {
                abort(404, 'Fichier non trouvé: ' . $path);
            }

            // Obtenir le chemin complet du fichier
            if ($mediaFile->disk === 'public') {
                $publicPath = 'storage/' . $path;
                $filePath = public_path($publicPath);
                
                if (!file_exists($filePath)) {
                    $filePath = $storage->path($path);
                }
            } else {
                $filePath = $storage->path($path);
            }
            
            if (!file_exists($filePath)) {
                abort(404, 'Fichier physique non trouvé: ' . $filePath);
            }
            
            // Forcer le téléchargement avec Content-Disposition: attachment
            return response()->download($filePath, $mediaFile->file_name, [
                'Content-Type' => $mediaFile->mime_type,
            ]);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                \Log::error('MediaDownloadController: Exception', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            abort(404, 'Erreur: ' . $e->getMessage());
        }
    }
}






