<?php

namespace Xavier\MediaLibraryPro\Livewire\Concerns;

use Xavier\MediaLibraryPro\Models\MediaFolder;
use Xavier\MediaLibraryPro\Services\MediaFolderService;

trait HandlesFolders
{
    /**
     * Navigue vers un dossier
     */
    public function navigateToFolder(?int $folderId): void
    {
        $this->currentFolderId = $folderId;
        $this->resetPage();
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
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (preg_match('/[<>:"|?*\/\\\\]/', (string) $value)) {
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
}

