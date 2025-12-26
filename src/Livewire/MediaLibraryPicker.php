<?php

namespace Xavier\MediaLibraryPro\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Services\MediaUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MediaLibraryPicker extends Component
{
    use WithPagination;
    use WithFileUploads;

    public bool $pickerMode = true;
    public bool $multiple = false;
    public array $acceptedTypes = [];
    public array $selectedIds = [];
    public bool $uploadMode = false;
    
    public $uploadedFiles = [];
    public bool $isUploading = false;
    public string $uploadCollection = 'default';

    protected $queryString = [
        'uploadMode' => ['except' => false],
    ];

    public function mount(
        bool $pickerMode = true,
        bool $multiple = false,
        array $acceptedTypes = [],
        array $selectedIds = [],
        bool $uploadMode = false
    ): void {
        $this->pickerMode = $pickerMode;
        $this->multiple = $multiple;
        $this->acceptedTypes = $acceptedTypes;
        $this->selectedIds = $selectedIds;
        $this->uploadMode = $uploadMode;
        $this->uploadCollection = 'default';
    }

    public function selectMedia(string $mediaUuid): void
    {
        // Trouver le MediaFile par UUID
        $mediaFile = MediaFile::where('uuid', $mediaUuid)->first();
        if (!$mediaFile) {
            return;
        }
        
        // Toggle la sélection
        if ($this->multiple) {
            $index = array_search($mediaFile->id, $this->selectedIds);
            if ($index !== false) {
                unset($this->selectedIds[$index]);
                $this->selectedIds = array_values($this->selectedIds);
            } else {
                $this->selectedIds[] = $mediaFile->id;
            }
        } else {
            $this->selectedIds = [$mediaFile->id];
        }
        
        // Dispatch event pour le composant parent avec les deux identifiants
        $this->dispatch('media-library-picker-select', 
            mediaId: $mediaFile->id, 
            mediaUuid: $mediaFile->uuid,
            mediaFileName: $mediaFile->file_name,
            mediaUrl: route('media-library-pro.serve', ['media' => $mediaFile->uuid])
        );
    }

    public function uploadFiles(): void
    {
        $this->validate([
            'uploadedFiles.*' => 'required|file|max:' . (config('media-library-pro.validation.max_size', 10240) * 1024),
        ]);

        $this->isUploading = true;

        try {
            $mediaUploadService = app(MediaUploadService::class);
            $uploadedCount = 0;

            foreach ($this->uploadedFiles as $file) {
                $mediaFile = $mediaUploadService->upload($file, [
                    'name' => $file->getClientOriginalName(),
                ]);

                // Dispatch event avec les infos complètes du fichier
                $this->dispatch('media-library-picker-uploaded', 
                    mediaId: $mediaFile->id,
                    mediaUuid: $mediaFile->uuid,
                    mediaFileName: $mediaFile->file_name,
                    mediaUrl: route('media-library-pro.serve', ['media' => $mediaFile->uuid])
                );
                
                // Si multiple, ajouter automatiquement à la sélection
                if ($this->multiple) {
                    $this->selectedIds[] = $mediaFile->id;
                } else {
                    $this->selectedIds = [$mediaFile->id];
                }

                $uploadedCount++;
            }

            // Nettoyer
            $this->uploadedFiles = [];
            $this->isUploading = false;
            $this->resetPage();

            session()->flash('notify', [
                'type' => 'success',
                'message' => $uploadedCount . ' fichier(s) uploadé(s) avec succès',
            ]);
        } catch (\Exception $e) {
            $this->isUploading = false;
            session()->flash('notify', [
                'type' => 'error',
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage(),
            ]);
        }
    }

    protected function getMediaQuery()
    {
        $query = MediaFile::query();

        // Filtres pour picker mode
        if (!empty($this->acceptedTypes)) {
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

        // Tri par défaut
        $query->orderBy('created_at', 'desc');

        return $query;
    }

    public function getMediaProperty()
    {
        return $this->getMediaQuery()->paginate(24);
    }

    public function isSelected(int $mediaId): bool
    {
        return in_array($mediaId, $this->selectedIds);
    }

    public function render()
    {
        return view('media-library-pro::livewire.media-library-picker', [
            'media' => $this->media,
        ]);
    }
}

