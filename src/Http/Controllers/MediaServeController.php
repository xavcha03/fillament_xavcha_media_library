<?php

namespace Xavier\MediaLibraryPro\Http\Controllers;

use Xavier\MediaLibraryPro\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaServeController extends Controller
{
    /**
     * Sert un fichier média via son UUID
     */
    public function show(Request $request, string $media)
    {
        try {
            // Chercher par UUID ou ID
            $mediaFile = MediaFile::where('uuid', $media)
                ->orWhere('id', $media)
                ->first();

            if (!$mediaFile) {
                if (config('app.debug')) {
                    \Log::error('MediaServeController: MediaFile not found', ['uuid' => $media]);
                }
                abort(404, 'Média non trouvé');
            }

            // Vérifier les permissions pour les fichiers privés
            if (!$mediaFile->is_public) {
                // Vérifier que l'utilisateur est authentifié
                if (!auth()->check()) {
                    abort(403, 'Accès non autorisé. Vous devez être connecté pour accéder à ce fichier.');
                }
                
                // Vérifier les permissions via les policies Laravel si elles existent
                // Vous pouvez créer une policy MediaFilePolicy pour personnaliser les permissions
                if (method_exists(auth()->user(), 'can') && auth()->user()->can('view', $mediaFile)) {
                    // Permission accordée via policy
                } else {
                    // Par défaut, seul le propriétaire ou un admin peut voir les fichiers privés
                    // Vous pouvez personnaliser cette logique selon vos besoins
                    abort(403, 'Accès non autorisé à ce fichier.');
                }
            }

            $storage = Storage::disk($mediaFile->disk);
            $path = $mediaFile->path;

            // Vérifier que le fichier existe
            if (!$storage->exists($path)) {
                if (config('app.debug')) {
                    \Log::error('MediaServeController: File not found', [
                        'uuid' => $mediaFile->uuid,
                        'path' => $path,
                        'disk' => $mediaFile->disk,
                        'full_path' => $storage->path($path),
                    ]);
                }
                abort(404, 'Fichier non trouvé: ' . $path);
            }

            // Servir le fichier
            // Pour le disque 'public', utiliser le chemin via public/storage (lien symbolique)
            if ($mediaFile->disk === 'public') {
                // Le chemin dans la DB est relatif à storage/app/public
                // Pour servir via HTTP, on utilise public/storage (lien symbolique)
                $publicPath = 'storage/' . $path;
                $filePath = public_path($publicPath);
                
                // Si le fichier n'existe pas dans public/storage, essayer storage/app/public directement
                if (!file_exists($filePath)) {
                    $filePath = $storage->path($path);
                }
            } else {
                $filePath = $storage->path($path);
            }
            
            if (!file_exists($filePath)) {
                if (config('app.debug')) {
                    \Log::error('MediaServeController: Physical file not found', [
                        'uuid' => $mediaFile->uuid,
                        'file_path' => $filePath,
                        'path_in_db' => $path,
                        'disk' => $mediaFile->disk,
                    ]);
                }
                abort(404, 'Fichier physique non trouvé: ' . $filePath);
            }
            
            return response()->file($filePath, [
                'Content-Type' => $mediaFile->mime_type,
                'Content-Disposition' => 'inline; filename="' . $mediaFile->file_name . '"',
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                \Log::error('MediaServeController: Exception', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            abort(404, 'Erreur: ' . $e->getMessage());
        }
    }
}
