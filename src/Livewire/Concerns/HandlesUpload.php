<?php

namespace Xavier\MediaLibraryPro\Livewire\Concerns;

use Xavier\MediaLibraryPro\Services\MediaUploadService;

trait HandlesUpload
{
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
            $this->uploadedFiles = array_values($this->uploadedFiles);
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

        return round($total / 1024 / 1024, 2);
    }

    public function getFileValidationErrors(): array
    {
        $errors = [];
        $maxSize = config('media-library-pro.validation.max_size', 10240) * 1024;
        $acceptedTypes = config('media-library-pro.validation.accepted_types', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        foreach ($this->uploadedFiles as $index => $file) {
            $fileErrors = [];

            if (method_exists($file, 'getSize') && $file->getSize() > $maxSize) {
                $fileErrors[] = 'Fichier trop volumineux (max ' . config('media-library-pro.validation.max_size', 10240) . 'MB)';
            }

            if (method_exists($file, 'getMimeType') && ! in_array($file->getMimeType(), $acceptedTypes, true)) {
                $fileErrors[] = 'Format non supporté';
            }

            if (! empty($fileErrors)) {
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
                $mediaFile = $mediaUploadService->upload($file, [
                    'name' => $file->getClientOriginalName(),
                ]);

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

        $this->uploadedFiles = [];
        $this->closeUploadModal();

        if ($uploadedCount > 0) {
            $this->resetPage();
            unset($this->media);

            session()->flash('notify', [
                'type' => 'success',
                'message' => $uploadedCount . ' fichier(s) uploadé(s) avec succès',
            ]);
        }
    }
}

