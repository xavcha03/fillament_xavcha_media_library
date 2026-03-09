<?php

namespace Xavier\MediaLibraryPro\Livewire\Concerns;

use Xavier\MediaLibraryPro\Models\MediaFile;

trait HandlesSelection
{
    /**
     * Toggle sélection : clic simple ou Ctrl/Cmd + clic.
     * @param int $mediaId
     * @param string|null $modifier 'ctrl'|'shift'|null
     */
    public function toggleSelect(int $mediaId, ?string $modifier = null): void
    {
        $mediaId = (int) $mediaId;
        if ($modifier === 'shift' && $this->lastSelectedId !== null) {
            $this->rangeSelect($mediaId);

            return;
        }

        if ($modifier === 'ctrl' || $modifier === 'meta') {
            $idx = array_search($mediaId, $this->selectedMediaIds, true);
            if ($idx !== false) {
                $this->selectedMediaIds = array_values(array_diff($this->selectedMediaIds, [$mediaId]));
            } else {
                $this->selectedMediaIds[] = $mediaId;
            }
        } else {
            if (in_array($mediaId, $this->selectedMediaIds, true)) {
                $this->selectedMediaIds = array_values(array_diff($this->selectedMediaIds, [$mediaId]));
            } else {
                $this->selectedMediaIds = [$mediaId];
            }
        }

        $this->lastSelectedId = $mediaId;
    }

    /**
     * Sélection par plage (Shift + clic).
     */
    public function rangeSelect(int $mediaId): void
    {
        $mediaId = (int) $mediaId;
        $idsOnPage = $this->media->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $lastIdx = array_search($this->lastSelectedId, $idsOnPage, true);
        $currIdx = array_search($mediaId, $idsOnPage, true);

        if ($lastIdx === false || $currIdx === false) {
            $this->toggleSelect($mediaId, null);

            return;
        }

        $start = min($lastIdx, $currIdx);
        $end = max($lastIdx, $currIdx);
        $range = array_slice($idsOnPage, $start, $end - $start + 1);

        $this->selectedMediaIds = array_values(array_unique(array_merge($this->selectedMediaIds, $range)));
        $this->lastSelectedId = $mediaId;
    }

    public function clearSelection(): void
    {
        $this->selectedMediaIds = [];
        $this->lastSelectedId = null;
    }

    /** Sélectionner tous les médias de la page courante. */
    public function selectAllInPage(): void
    {
        $this->selectedMediaIds = $this->media->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        if (! empty($this->selectedMediaIds)) {
            $this->lastSelectedId = end($this->selectedMediaIds);
        }
    }

    /** Sélectionner tous les médias (tous les résultats de la requête). */
    public function selectAll(): void
    {
        $this->selectedMediaIds = $this->getMediaQuery()->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        if (! empty($this->selectedMediaIds)) {
            $this->lastSelectedId = end($this->selectedMediaIds);
        }
    }

    /**
     * Appliquer la sélection par drag (rectangle).
     * @param array<int> $mediaIds
     * @param string $mode 'replace'|'add'|'toggle'
     */
    public function applyDragSelection(array $mediaIds, string $mode = 'replace'): void
    {
        $mediaIds = array_map('intval', array_filter($mediaIds));

        if ($mode === 'replace') {
            $this->selectedMediaIds = array_values(array_unique($mediaIds));
        } elseif ($mode === 'add') {
            $this->selectedMediaIds = array_values(array_unique(array_merge($this->selectedMediaIds, $mediaIds)));
        } else {
            foreach ($mediaIds as $id) {
                $idx = array_search($id, $this->selectedMediaIds, true);
                if ($idx !== false) {
                    unset($this->selectedMediaIds[$idx]);
                } else {
                    $this->selectedMediaIds[] = $id;
                }
            }

            $this->selectedMediaIds = array_values($this->selectedMediaIds);
        }

        if (! empty($mediaIds)) {
            $this->lastSelectedId = end($mediaIds);
        }
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedMediaIds)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Aucun élément sélectionné',
            ]);

            return;
        }

        $count = count($this->selectedMediaIds);

        MediaFile::whereIn('id', $this->selectedMediaIds)->each(function (MediaFile $mediaFile): void {
            $mediaFile->delete();
        });

        $this->clearSelection();

        session()->flash('notify', [
            'type' => 'success',
            'message' => $count . ' média(s) supprimé(s)',
        ]);
    }

    public function bulkMoveCollection(string $collection): void
    {
        if (empty($this->selectedMediaIds)) {
            session()->flash('notify', [
                'type' => 'warning',
                'message' => 'Aucun élément sélectionné',
            ]);

            return;
        }

        \Xavier\MediaLibraryPro\Models\MediaAttachment::whereIn('media_file_id', $this->selectedMediaIds)
            ->update(['collection_name' => $collection]);

        $this->clearSelection();

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Médias déplacés vers la collection ' . $collection,
        ]);
    }

    public function bulkAddTags(array $tags): void
    {
        if (empty($this->selectedMediaIds)) {
            session()->flash('notify', [
                'type' => 'warning',
                'message' => 'Aucun élément sélectionné',
            ]);

            return;
        }

        if (! config('media-library-pro.tags.enabled', false)) {
            session()->flash('notify', [
                'type' => 'error',
                'message' => 'Les tags ne sont pas activés',
            ]);

            return;
        }

        if (! class_exists(\Spatie\Tags\Tag::class)) {
            session()->flash('notify', [
                'type' => 'error',
                'message' => 'Le package spatie/laravel-tags n\'est pas installé',
            ]);

            return;
        }

        // Fonctionnalité future : système de tags pour organiser les médias
        $this->clearSelection();

        session()->flash('notify', [
            'type' => 'info',
            'message' => 'La fonctionnalité de tags sera disponible dans une prochaine version',
        ]);
    }

    public function getAvailableTags(): array
    {
        // Fonctionnalité future : retourner la liste des tags disponibles
        return [];
    }
}

