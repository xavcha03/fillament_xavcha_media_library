<?php

namespace Xavier\MediaLibraryPro\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Models\MediaFolder;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Xavier\MediaLibraryPro\Livewire\Concerns\HandlesSelection;
use Xavier\MediaLibraryPro\Livewire\Concerns\HandlesFolders;
use Xavier\MediaLibraryPro\Livewire\Concerns\HandlesUpload;
use Xavier\MediaLibraryPro\Livewire\Concerns\HandlesDetailMedia;

class MediaLibrary extends Component
{
    use WithPagination;
    use WithFileUploads;
    use HandlesSelection;
    use HandlesFolders;
    use HandlesUpload;
    use HandlesDetailMedia;

    public string $view = 'grid';
    public array $filters = [];
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    /** @var array<int> IDs des médias sélectionnés (sélection directe, pas de mode) */
    public array $selectedMediaIds = [];
    /** @var int|null Dernier média sélectionné pour Shift+clic (plage) */
    public ?int $lastSelectedId = null;
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
    public ?int $detailIndex = null;
    /** @var array<int> */
    public array $detailMediaIdsOnPage = [];
    public ?int $currentFolderId = null;
    public bool $showRenameModal = false;
    public string $renameFileName = '';
    public bool $showCreateFolderModal = false;
    public string $folderName = '';
    public ?int $folderParentId = null;
    public bool $showMoveModal = false;
    public ?int $moveFolderId = null;

    protected $queryString = [
        'view' => ['except' => 'grid'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'currentFolderId' => ['except' => null],
    ];

    public function mount(bool $pickerMode = false, bool $multiple = false, array $acceptedTypes = []): void
    {
        $this->pickerMode = $pickerMode;
        $this->multiple = $multiple;
        $this->acceptedTypes = $acceptedTypes;

        // Vue par défaut : grille, avec possibilité de mémoriser le dernier choix
        if (config('media-library-pro.view.remember', true)) {
            $rememberedView = Session::get('media-library-view');
            if (in_array($rememberedView, ['list', 'grid'], true)) {
                $this->view = $rememberedView;
            } else {
                $this->view = 'grid';
            }
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
        if ($this->sortBy === 'collection_name') {
            $sub = \Xavier\MediaLibraryPro\Models\MediaAttachment::select('collection_name')
                ->whereColumn('media_file_id', 'media_files.id')
                ->limit(1);
            $query->orderBy($sub, $this->sortDirection);
        } elseif ($this->sortBy === 'name') {
            $query->orderBy('file_name', $this->sortDirection);
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $query;
    }

    public function getMediaProperty()
    {
        return $this->getMediaQuery()
            ->with(['attachments', 'folder']) // Charger les attachments et le dossier pour éviter N+1
            ->paginate(24);
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
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

