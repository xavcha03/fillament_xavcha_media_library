<?php

namespace Xavier\MediaLibraryPro\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Services\MediaUploadService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MediaLibrary extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $view = 'grid';
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

    protected $queryString = [
        'view' => ['except' => 'grid'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(bool $pickerMode = false, bool $multiple = false, array $acceptedTypes = []): void
    {
        $this->pickerMode = $pickerMode;
        $this->multiple = $multiple;
        $this->acceptedTypes = $acceptedTypes;
        
        if (!$pickerMode) {
            $this->view = Session::get('media-library-view', config('media-library-pro.view.default', 'grid'));
        } else {
            $this->view = 'grid';
        }
        
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

        // Filtre par type de modèle (via attachments)
        if (!empty($this->filters['model_type'])) {
            $query->whereHas('attachments', function ($q) {
                $q->where('model_type', $this->filters['model_type']);
            });
        } else {
            // Par défaut, afficher tous les MediaFile (avec ou sans attachments)
            // Les fichiers uploadés directement n'ont pas d'attachment et doivent être visibles
        }

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

        // Tri
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query;
    }

    public function getMediaProperty()
    {
        return $this->getMediaQuery()
            ->with('attachments') // Charger les attachments pour éviter N+1
            ->paginate(24);
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

    public function render()
    {
        return view('media-library-pro::livewire.media-library', [
            'media' => $this->media,
        ]);
    }
}

