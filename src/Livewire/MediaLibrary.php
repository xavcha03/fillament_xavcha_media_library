<?php

namespace Xavier\MediaLibraryPro\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Models\MediaFolder;
use Xavier\MediaLibraryPro\Services\MediaUploadService;
use Xavier\MediaLibraryPro\Services\MediaFolderService;
use Xavier\MediaLibraryPro\Services\ImageOptimizationService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MediaLibrary extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $view = 'list';
    public array $filters = [];
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public array $selectedItems = [];
    public bool $selectMode = false;
    public bool $pickerMode = false;
    public bool $multiple = false;
    public array $acceptedTypes = [];
    public ?string $moveCollection = null;
    public $uploadedFiles = [];
    public bool $showUploadModal = false;
    public ?string $uploadCollection = null;
    public bool $showDetailModal = false;
    public ?MediaFile $detailMedia = null;
    public string $detailAltText = '';
    public string $detailDescription = '';
    public ?int $currentFolderId = null;
    public bool $showRenameModal = false;
    public string $renameFileName = '';
    public bool $showCreateFolderModal = false;
    public string $folderName = '';
    public ?int $folderParentId = null;
    public bool $showMoveModal = false;
    public ?int $moveFolderId = null;

    protected $queryString = [
        'view' => ['except' => 'list'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'currentFolderId' => ['except' => null],
    ];

    public function mount(bool $pickerMode = false, bool $multiple = false, array $acceptedTypes = []): void
    {
        $this->pickerMode = $pickerMode;
        $this->multiple = $multiple;
        $this->acceptedTypes = $acceptedTypes;
        
        // Toujours commencer en mode liste par défaut
        $this->view = 'list';
        
        $this->sortBy = config('media-library-pro.sorters.default', 'created_at');
        $this->sortDirection = config('media-library-pro.sorters.direction', 'desc');
    }

    public function toggleView(): void
    {
        $this->view = $this->view === 'grid' ? 'list' : 'grid';
        
        if (config('media-library-pro.view.remember', true)) {
            Session::put('media-library-view', $this->view);
        }
    }

    public function setGridView(): void
    {
        $this->view = 'grid';
        
        if (config('media-library-pro.view.remember', true)) {
            Session::put('media-library-view', $this->view);
        }
    }

    public function setListView(): void
    {
        $this->view = 'list';
        
        if (config('media-library-pro.view.remember', true)) {
            Session::put('media-library-view', $this->view);
        }
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function toggleSelectMode(): void
    {
        $this->selectMode = !$this->selectMode;
        $this->selectedItems = [];
    }

    public function toggleSelection(string $mediaUuid): void
    {
        if (in_array($mediaUuid, $this->selectedItems)) {
            $this->selectedItems = array_values(array_diff($this->selectedItems, [$mediaUuid]));
        } else {
            $this->selectedItems[] = $mediaUuid;
        }
    }

    public function selectAll(): void
    {
        $this->selectedItems = $this->getMediaQuery()->pluck('uuid')->toArray();
    }

    public function deselectAll(): void
    {
        $this->selectedItems = [];
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Aucun élément sélectionné',
            ]);
            return;
        }

        $count = count($this->selectedItems);
        
        // Supprimer les MediaFile par UUID (cela supprimera aussi les attachments et conversions)
        MediaFile::whereIn('uuid', $this->selectedItems)->each(function ($mediaFile) {
            $mediaFile->delete(); // Utilise la méthode delete() du modèle qui supprime aussi le fichier
        });
        
        $this->selectedItems = [];
        $this->selectMode = false;
        
        session()->flash('notify', [
            'type' => 'success',
            'message' => $count . ' média(s) supprimé(s)',
        ]);
    }

    public function bulkMoveCollection(string $collection): void
    {
        if (empty($this->selectedItems)) {
            session()->flash('notify', [
                'type' => 'warning',
                'message' => 'Aucun élément sélectionné',
            ]);
            return;
        }

        // Récupérer les IDs des MediaFile à partir des UUIDs
        $mediaFileIds = MediaFile::whereIn('uuid', $this->selectedItems)->pluck('id')->toArray();
        
        // Mettre à jour les attachments pour changer la collection
        \Xavier\MediaLibraryPro\Models\MediaAttachment::whereIn('media_file_id', $mediaFileIds)
            ->update(['collection_name' => $collection]);
        
        $this->selectedItems = [];
        $this->selectMode = false;
        
        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Médias déplacés vers la collection ' . $collection,
        ]);
    }

    public function bulkAddTags(array $tags): void
    {
        if (empty($this->selectedItems)) {
            session()->flash('notify', [
                'type' => 'warning',
                'message' => 'Aucun élément sélectionné',
            ]);
            return;
        }

        if (!config('media-library-pro.tags.enabled', false)) {
            session()->flash('notify', [
                'type' => 'error',
                'message' => 'Les tags ne sont pas activés',
            ]);
            return;
        }

        if (!class_exists(\Spatie\Tags\Tag::class)) {
            session()->flash('notify', [
                'type' => 'error',
                'message' => 'Le package spatie/laravel-tags n\'est pas installé',
            ]);
            return;
        }

        // Fonctionnalité future : système de tags pour organiser les médias
        // Cette fonctionnalité sera disponible dans une prochaine version
        
        $this->selectedItems = [];
        $this->selectMode = false;
        
        session()->flash('notify', [
            'type' => 'info',
            'message' => 'La fonctionnalité de tags sera disponible dans une prochaine version',
        ]);
    }

    public function getAvailableTags(): array
    {
        // Fonctionnalité future : retourner la liste des tags disponibles
        // Cette fonctionnalité sera disponible dans une prochaine version
        return [];
    }

    protected function getMediaQuery()
    {
        $query = MediaFile::query();

        // Filtres
        if (!empty($this->filters['collection'])) {
            // Filtrer par collection via les attachments
            $query->whereHas('attachments', function ($q) {
                $q->where('collection_name', $this->filters['collection']);
            });
        }

        if (!empty($this->filters['type'])) {
            $mimeTypes = config("media-library-pro.filters.types.{$this->filters['type']}", []);
            if (!empty($mimeTypes)) {
                $query->whereIn('mime_type', $mimeTypes);
            }
        }

        if (!empty($this->filters['mime_type'])) {
            $query->where('mime_type', 'like', "%{$this->filters['mime_type']}%");
        }

        // Filtre par type de modèle retiré - plus nécessaire

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['size_min'])) {
            $query->where('size', '>=', $this->filters['size_min'] * 1024);
        }

        if (!empty($this->filters['size_max'])) {
            $query->where('size', '<=', $this->filters['size_max'] * 1024);
        }

        // Filtres pour picker mode
        if ($this->pickerMode && !empty($this->acceptedTypes)) {
            $query->where(function ($q) {
                foreach ($this->acceptedTypes as $type) {
                    if (str_ends_with($type, '/*')) {
                        $mimePrefix = str_replace('/*', '', $type);
                        $q->orWhere('mime_type', 'like', $mimePrefix . '/%');
                    } else {
                        $q->orWhere('mime_type', $type);
                    }
                }
            });
        }

        // Filtre par dossier
        if ($this->currentFolderId !== null) {
            $query->where('folder_id', $this->currentFolderId);
        } else {
            // Si on est à la racine, afficher uniquement les fichiers sans dossier
            $query->whereNull('folder_id');
        }

        // Tri
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query;
    }

    public function getMediaProperty()
    {
        return $this->getMediaQuery()
            ->with(['attachments', 'folder']) // Charger les attachments et le dossier pour éviter N+1
            ->paginate(24);
    }

    /**
     * Navigue vers un dossier
     */
    public function navigateToFolder(?int $folderId): void
    {
        $this->currentFolderId = $folderId;
        $this->resetPage();
    }

    /**
     * Ouvre la modale de renommage
     */
    public function openRenameModal(): void
    {
        if ($this->detailMedia) {
            $this->renameFileName = pathinfo($this->detailMedia->file_name, PATHINFO_FILENAME);
            $this->showRenameModal = true;
        }
    }

    /**
     * Ferme la modale de renommage
     */
    public function closeRenameModal(): void
    {
        $this->showRenameModal = false;
        $this->renameFileName = '';
    }

    /**
     * Renomme un fichier média
     */
    public function renameMedia(): void
    {
        if (!$this->detailMedia) {
            return;
        }

        $this->validate([
            'renameFileName' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Caractères interdits : < > : " | ? * / \
                    if (preg_match('/[<>:"|?*\/\\\\]/', $value)) {
                        $fail('Le nom du fichier contient des caractères interdits (< > : " | ? * / \\)');
                    }
                },
            ],
        ], [
            'renameFileName.required' => 'Le nom du fichier est requis',
        ]);

        try {
            $this->detailMedia->rename($this->renameFileName);
            
            // Rafraîchir le média
            $this->detailMedia->refresh();
            
            $this->closeRenameModal();
            
            session()->flash('notify', [
                'type' => 'success',
                'message' => 'Le fichier a été renommé avec succès',
            ]);
        } catch (\Exception $e) {
            session()->flash('notify', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Ouvre la modale de déplacement
     */
    public function openMoveModal(): void
    {
        if ($this->detailMedia) {
            $this->moveFolderId = $this->detailMedia->folder_id;
            $this->showMoveModal = true;
        }
    }

    /**
     * Ferme la modale de déplacement
     */
    public function closeMoveModal(): void
    {
        $this->showMoveModal = false;
        $this->moveFolderId = null;
    }

    /**
     * Déplace un fichier vers un dossier
     */
    public function moveMedia(): void
    {
        if (!$this->detailMedia) {
            return;
        }

        try {
            $folder = $this->moveFolderId ? MediaFolder::find($this->moveFolderId) : null;
            $this->detailMedia->folder_id = $folder?->id;
            $this->detailMedia->save();
            
            // Rafraîchir le média
            $this->detailMedia->refresh();
            
            $this->closeMoveModal();
            
            $destination = $folder ? $folder->name : 'la racine';
            session()->flash('notify', [
                'type' => 'success',
                'message' => "Le fichier a été déplacé vers {$destination}",
            ]);
        } catch (\Exception $e) {
            session()->flash('notify', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Récupère le dossier actuel
     */
    public function getCurrentFolderProperty(): ?MediaFolder
    {
        if ($this->currentFolderId === null) {
            return null;
        }
        
        return MediaFolder::find($this->currentFolderId);
    }

    /**
     * Récupère les dossiers enfants du dossier actuel
     */
    public function getChildFoldersProperty()
    {
        $folderService = app(MediaFolderService::class);
        return $folderService->getChildFolders($this->currentFolder);
    }

    /**
     * Supprime un fichier média
     */
    public function deleteMedia(string $mediaUuid): void
    {
        try {
            $mediaFile = MediaFile::where('uuid', $mediaUuid)->firstOrFail();
            $fileName = $mediaFile->file_name;
            $mediaFile->delete();
            
            $this->closeDetailModal();
            
            session()->flash('notify', [
                'type' => 'success',
                'message' => "Le fichier '{$fileName}' a été supprimé avec succès",
            ]);
        } catch (\Exception $e) {
            session()->flash('notify', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Optimise une image existante
     */
    public function optimizeImage(string $mediaUuid): void
    {
        try {
            $mediaFile = MediaFile::where('uuid', $mediaUuid)->firstOrFail();
            
            if (!$mediaFile->isImage()) {
                session()->flash('notify', [
                    'type' => 'error',
                    'message' => 'Seules les images peuvent être optimisées',
                ]);
                return;
            }

            $originalSize = $mediaFile->size;
            $optimizationService = app(ImageOptimizationService::class);
            
            $success = $optimizationService->optimizeMediaFile($mediaFile);
            
            if ($success) {
                $newSize = $mediaFile->fresh()->size;
                $savedBytes = $originalSize - $newSize;
                $savedPercent = $originalSize > 0 ? round(($savedBytes / $originalSize) * 100, 1) : 0;
                $savedSize = $this->formatBytes($savedBytes);
                
                // Rafraîchir les données dans la modale
                $this->detailMedia = $mediaFile->fresh();
                
                session()->flash('notify', [
                    'type' => 'success',
                    'message' => "Image optimisée avec succès ! Taille réduite de {$savedSize} ({$savedPercent}%)",
                ]);
            } else {
                session()->flash('notify', [
                    'type' => 'error',
                    'message' => 'Erreur lors de l\'optimisation de l\'image',
                ]);
            }
        } catch (\Exception $e) {
            session()->flash('notify', [
                'type' => 'error',
                'message' => 'Erreur : ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Formate les bytes en format lisible
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Ouvre la modale de création de dossier
     */
    public function openCreateFolderModal(): void
    {
        $this->folderName = '';
        $this->folderParentId = $this->currentFolderId;
        $this->showCreateFolderModal = true;
    }

    /**
     * Ferme la modale de création de dossier
     */
    public function closeCreateFolderModal(): void
    {
        $this->showCreateFolderModal = false;
        $this->folderName = '';
        $this->folderParentId = null;
    }

    /**
     * Crée un nouveau dossier
     */
    public function createFolder(): void
    {
        $this->validate([
            'folderName' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Caractères interdits : < > : " | ? * / \
                    if (preg_match('/[<>:"|?*\/\\\\]/', $value)) {
                        $fail('Le nom du dossier contient des caractères interdits (< > : " | ? * / \\)');
                    }
                },
            ],
        ], [
            'folderName.required' => 'Le nom du dossier est requis',
        ]);

        try {
            $folderService = app(MediaFolderService::class);
            $parent = $this->folderParentId ? MediaFolder::find($this->folderParentId) : null;
            $folder = $folderService->create($this->folderName, $parent);
            
            $this->closeCreateFolderModal();
            
            // Si on était dans un dossier, naviguer vers le nouveau dossier créé
            if ($this->currentFolderId === $this->folderParentId) {
                $this->navigateToFolder($folder->id);
            }
            
            session()->flash('notify', [
                'type' => 'success',
                'message' => "Le dossier '{$folder->name}' a été créé avec succès",
            ]);
        } catch (\Exception $e) {
            session()->flash('notify', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function openUploadModal(): void
    {
        $this->showUploadModal = true;
        $this->uploadedFiles = [];
        $this->uploadCollection = null;
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->uploadedFiles = [];
        $this->uploadCollection = null;
    }

    public function removeFile(int $index): void
    {
        if (isset($this->uploadedFiles[$index])) {
            unset($this->uploadedFiles[$index]);
            $this->uploadedFiles = array_values($this->uploadedFiles); // Réindexer le tableau
        }
    }

    public function clearUploadedFiles(): void
    {
        $this->uploadedFiles = [];
    }

    public function getTotalFileSize(): float
    {
        $total = 0;
        foreach ($this->uploadedFiles as $file) {
            if (method_exists($file, 'getSize')) {
                $total += $file->getSize();
            }
        }
        return round($total / 1024 / 1024, 2); // Retourne en MB
    }

    public function getFileValidationErrors(): array
    {
        $errors = [];
        $maxSize = config('media-library-pro.validation.max_size', 10240) * 1024; // en bytes
        $acceptedTypes = config('media-library-pro.validation.accepted_types', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        foreach ($this->uploadedFiles as $index => $file) {
            $fileErrors = [];
            
            if (method_exists($file, 'getSize') && $file->getSize() > $maxSize) {
                $fileErrors[] = 'Fichier trop volumineux (max ' . config('media-library-pro.validation.max_size', 10240) . 'MB)';
            }
            
            if (method_exists($file, 'getMimeType') && !in_array($file->getMimeType(), $acceptedTypes)) {
                $fileErrors[] = 'Format non supporté';
            }
            
            if (!empty($fileErrors)) {
                $errors[$index] = implode(' • ', $fileErrors);
            }
        }

        return $errors;
    }

    public function uploadFiles(): void
    {
        $this->validate([
            'uploadedFiles.*' => 'required|file|max:' . (config('media-library-pro.validation.max_size', 10240) * 1024),
        ]);

        $collection = $this->uploadCollection ?: 'default';
        $uploadedCount = 0;

        $mediaUploadService = app(MediaUploadService::class);

        foreach ($this->uploadedFiles as $file) {
            try {
                // Uploader le fichier
                $mediaFile = $mediaUploadService->upload($file, [
                    'name' => $file->getClientOriginalName(),
                ]);

                // Associer le fichier au dossier courant si on est dans un dossier
                if ($this->currentFolderId !== null) {
                    $mediaFile->folder_id = $this->currentFolderId;
                    $mediaFile->save();
                }

                $uploadedCount++;
            } catch (\Exception $e) {
                session()->flash('notify', [
                    'type' => 'error',
                    'message' => 'Erreur lors de l\'upload: ' . $e->getMessage(),
                ]);
                continue;
            }
        }

        // Nettoyer les fichiers temporaires
        $this->uploadedFiles = [];
        $this->closeUploadModal();
        
        if ($uploadedCount > 0) {
            // Forcer le rafraîchissement de la liste
            $this->resetPage();
            // Forcer Livewire à rafraîchir en réinitialisant la propriété computed
            unset($this->media);
            
            session()->flash('notify', [
                'type' => 'success',
                'message' => $uploadedCount . ' fichier(s) uploadé(s) avec succès',
            ]);
        }
    }

    public function openDetailModal(string $mediaUuid): void
    {
        $this->detailMedia = MediaFile::where('uuid', $mediaUuid)->firstOrFail();
        $this->detailAltText = $this->detailMedia->alt_text ?? '';
        $this->detailDescription = $this->detailMedia->description ?? '';
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailMedia = null;
    }

    public function updateMediaDetails(): void
    {
        if (!$this->detailMedia) {
            return;
        }

        $this->detailMedia->alt_text = $this->detailAltText;
        $this->detailMedia->description = $this->detailDescription;
        $this->detailMedia->save();

        $this->showDetailModal = false;
    }

    /**
     * Get the media image URL for display
     */
    public function getMediaImageUrl(MediaFile $media): string
    {
        try {
            return route('media-library-pro.serve', ['media' => $media->uuid]);
        } catch (\Exception $e) {
            return url('/media-library-pro/serve/' . $media->uuid);
        }
    }

    public function render()
    {
        return view('media-library-pro::livewire.media-library', [
            'media' => $this->media,
            'currentFolder' => $this->currentFolder,
            'childFolders' => $this->childFolders,
        ]);
    }
}

