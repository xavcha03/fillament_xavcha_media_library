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
    public ?string $filterCollection = null;
    public ?string $defaultCollection = null; // Collection par défaut pour l'association (pas pour le filtre)
    
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
        bool $uploadMode = false,
        ?string $filterCollection = null
    ): void {
        $this->pickerMode = $pickerMode;
        $this->multiple = $multiple;
        $this->acceptedTypes = $acceptedTypes;
        $this->selectedIds = $selectedIds;
        $this->uploadMode = $uploadMode;
        // filterCollection est utilisé pour l'association, pas pour filtrer l'affichage
        // On ne filtre pas par défaut pour montrer tous les médias disponibles
        $this->defaultCollection = $filterCollection; // Garder pour l'association
        $this->filterCollection = null; // Ne pas filtrer par défaut
        $this->uploadCollection = $filterCollection ?? 'default';
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
        // Les événements Livewire sont dispatchés globalement par défaut
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
                // Les événements Livewire sont dispatchés globalement par défaut
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
            
            // Forcer le rafraîchissement de la liste
            $this->resetPage();
            // Invalider la propriété computed pour forcer le rafraîchissement
            unset($this->media);
            
            // Dispatcher un événement pour rafraîchir tous les composants MediaLibraryPicker
            $this->dispatch('refresh-media-list');

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

        // Filtre par collection si spécifié ET si l'utilisateur a changé le filtre manuellement
        // Par défaut, on affiche TOUS les médias (même ceux sans attachments)
        // Le filtre par collection dans la vue permet de filtrer, mais n'est pas appliqué automatiquement
        if (!empty($this->filterCollection)) {
            // Filtrer les médias qui ont des attachments avec cette collection
            // OU les médias qui n'ont pas encore d'attachments (disponibles pour utilisation)
            $query->where(function ($q) {
                $q->whereHas('attachments', function ($subQ) {
                    $subQ->where('collection_name', $this->filterCollection);
                })->orDoesntHave('attachments'); // Inclure aussi les médias sans attachments
            });
        }
        // Si filterCollection est vide, on affiche tous les médias (pas de filtre)

        // Filtres pour picker mode (types MIME)
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

    protected $listeners = ['refresh-media-list' => '$refresh'];

    public function getMediaProperty()
    {
        return $this->getMediaQuery()->paginate(24);
    }

    public function isSelected(int $mediaId): bool
    {
        return in_array($mediaId, $this->selectedIds);
    }

    public function removeUploadedFile(int $index): void
    {
        if (isset($this->uploadedFiles[$index])) {
            unset($this->uploadedFiles[$index]);
            $this->uploadedFiles = array_values($this->uploadedFiles);
        }
    }

    public function render()
    {
        return view('media-library-pro::livewire.media-library-picker', [
            'media' => $this->media,
        ]);
    }
}

