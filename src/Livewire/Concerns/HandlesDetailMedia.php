<?php

namespace Xavier\MediaLibraryPro\Livewire\Concerns;

use Filament\Notifications\Notification;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Models\MediaFolder;
use Xavier\MediaLibraryPro\Services\ImageOptimizationService;

trait HandlesDetailMedia
{
    public function openDetailModal(string $mediaUuid): void
    {
        $this->detailMedia = MediaFile::where('uuid', $mediaUuid)->firstOrFail();
        $this->detailAltText = $this->detailMedia->alt_text ?? '';
        $this->detailDescription = $this->detailMedia->description ?? '';

        $idsOnPage = $this->media->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $this->detailMediaIdsOnPage = $idsOnPage;

        $currentId = (int) $this->detailMedia->id;
        $index = array_search($currentId, $idsOnPage, true);
        $this->detailIndex = $index === false ? null : $index;

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailMedia = null;
        $this->detailIndex = null;
        $this->detailMediaIdsOnPage = [];
    }

    public function updateMediaDetails(): void
    {
        if (! $this->detailMedia) {
            return;
        }

        $this->detailMedia->alt_text = $this->detailAltText;
        $this->detailMedia->description = $this->detailDescription;
        $this->detailMedia->save();

        $this->showDetailModal = false;
    }

    public function openPreviousDetail(): void
    {
        if ($this->detailIndex === null || empty($this->detailMediaIdsOnPage)) {
            return;
        }

        if ($this->detailIndex <= 0) {
            return;
        }

        $newIndex = $this->detailIndex - 1;
        if (! isset($this->detailMediaIdsOnPage[$newIndex])) {
            return;
        }

        $mediaId = (int) $this->detailMediaIdsOnPage[$newIndex];
        $media = MediaFile::find($mediaId);
        if (! $media) {
            return;
        }

        $this->detailMedia = $media;
        $this->detailAltText = $this->detailMedia->alt_text ?? '';
        $this->detailDescription = $this->detailMedia->description ?? '';
        $this->detailIndex = $newIndex;
    }

    public function openNextDetail(): void
    {
        if ($this->detailIndex === null || empty($this->detailMediaIdsOnPage)) {
            return;
        }

        $lastIndex = count($this->detailMediaIdsOnPage) - 1;
        if ($this->detailIndex >= $lastIndex) {
            return;
        }

        $newIndex = $this->detailIndex + 1;
        if (! isset($this->detailMediaIdsOnPage[$newIndex])) {
            return;
        }

        $mediaId = (int) $this->detailMediaIdsOnPage[$newIndex];
        $media = MediaFile::find($mediaId);
        if (! $media) {
            return;
        }

        $this->detailMedia = $media;
        $this->detailAltText = $this->detailMedia->alt_text ?? '';
        $this->detailDescription = $this->detailMedia->description ?? '';
        $this->detailIndex = $newIndex;
    }

    public function openRenameModal(): void
    {
        if ($this->detailMedia) {
            $this->renameFileName = pathinfo($this->detailMedia->file_name, PATHINFO_FILENAME);
            $this->showRenameModal = true;
        }
    }

    public function closeRenameModal(): void
    {
        $this->showRenameModal = false;
        $this->renameFileName = '';
    }

    public function renameMedia(): void
    {
        if (! $this->detailMedia) {
            return;
        }

        $this->validate([
            'renameFileName' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (preg_match('/[<>:"|?*\/\\\\]/', (string) $value)) {
                        $fail('Le nom du fichier contient des caractères interdits (< > : " | ? * / \\)');
                    }
                },
            ],
        ], [
            'renameFileName.required' => 'Le nom du fichier est requis',
        ]);

        try {
            $this->detailMedia->rename($this->renameFileName);

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

    public function openMoveModal(): void
    {
        if ($this->detailMedia) {
            $this->moveFolderId = $this->detailMedia->folder_id;
            $this->showMoveModal = true;
        }
    }

    public function closeMoveModal(): void
    {
        $this->showMoveModal = false;
        $this->moveFolderId = null;
    }

    public function moveMedia(): void
    {
        if (! $this->detailMedia) {
            return;
        }

        try {
            $folder = $this->moveFolderId ? MediaFolder::find($this->moveFolderId) : null;
            $this->detailMedia->folder_id = $folder?->id;
            $this->detailMedia->save();

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

    public function optimizeImage(string $mediaUuid): void
    {
        try {
            $mediaFile = MediaFile::where('uuid', $mediaUuid)->firstOrFail();

            if (! $mediaFile->isImage()) {
                Notification::make()
                    ->title('Erreur')
                    ->body('Seules les images peuvent être optimisées')
                    ->danger()
                    ->send();

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

                $this->detailMedia = $mediaFile->fresh();

                Notification::make()
                    ->title('Succès')
                    ->body("Image optimisée avec succès ! Taille réduite de {$savedSize} ({$savedPercent}%)")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Erreur')
                    ->body("Erreur lors de l'optimisation de l'image")
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur')
                ->body('Erreur : ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function rotateLeft(string $mediaUuid): void
    {
        $this->rotateImage($mediaUuid, 'left');
    }

    public function rotateRight(string $mediaUuid): void
    {
        $this->rotateImage($mediaUuid, 'right');
    }

    protected function rotateImage(string $mediaUuid, string $direction): void
    {
        try {
            $mediaFile = MediaFile::where('uuid', $mediaUuid)->firstOrFail();

            if (! $mediaFile->isImage()) {
                session()->flash('notify', [
                    'type' => 'error',
                    'message' => 'Seules les images peuvent être pivotées',
                ]);

                return;
            }

            /** @var ImageOptimizationService $optimizationService */
            $optimizationService = app(ImageOptimizationService::class);
            $success = $optimizationService->rotateMediaFile($mediaFile, $direction);

            if ($success) {
                $this->detailMedia = $mediaFile->fresh();

                session()->flash('notify', [
                    'type' => 'success',
                    'message' => $direction === 'left'
                        ? 'Image pivotée vers la gauche'
                        : 'Image pivotée vers la droite',
                ]);
            } else {
                session()->flash('notify', [
                    'type' => 'error',
                    'message' => 'Impossible de pivoter cette image',
                ]);
            }
        } catch (\Exception $e) {
            session()->flash('notify', [
                'type' => 'error',
                'message' => 'Erreur : ' . $e->getMessage(),
            ]);
        }
    }

    public function getMediaImageUrl(MediaFile $media): string
    {
        try {
            $version = $media->updated_at?->timestamp ?? $media->size ?? time();

            return route('media-library-pro.serve', [
                'media' => $media->uuid,
                't' => $version,
            ]);
        } catch (\Exception $e) {
            $version = $media->updated_at?->timestamp ?? $media->size ?? time();

            return url('/media-library-pro/serve/' . $media->uuid . '?t=' . $version);
        }
    }
}

