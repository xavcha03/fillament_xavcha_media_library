<?php

namespace Xavier\MediaLibraryPro\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Xavier\MediaLibraryPro\Models\MediaFile;

class MediaPickerUnified extends Field
{
    use HasPlaceholder;

    protected string $view = 'media-library-pro::forms.components.media-picker-unified';

    protected bool | Closure $multiple = false;
    protected array | Closure $acceptedFileTypes = [];
    protected string | Closure $collection = 'default';
    protected int | Closure | null $maxFiles = null;
    protected int | Closure $minFiles = 0;
    protected bool | Closure $showUpload = true;
    protected bool | Closure $showLibrary = true;
    protected string | Closure | null $conversion = null;
    protected int | Closure | null $maxFileSize = null; // En KB
    protected bool | Closure $allowReordering = false;
    protected bool | Closure $downloadable = false;

    public static function make(?string $name = null): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function multiple(bool | Closure $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function acceptedFileTypes(array | Closure $types): static
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    public function collection(string | Closure $collection): static
    {
        $this->collection = $collection;

        return $this;
    }

    public function maxFiles(int | Closure | null $maxFiles): static
    {
        $this->maxFiles = $maxFiles;

        return $this;
    }

    public function minFiles(int | Closure $minFiles): static
    {
        $this->minFiles = $minFiles;

        return $this;
    }

    public function showUpload(bool | Closure $showUpload = true): static
    {
        $this->showUpload = $showUpload;

        return $this;
    }

    public function showLibrary(bool | Closure $showLibrary = true): static
    {
        $this->showLibrary = $showLibrary;

        return $this;
    }

    public function conversion(string | Closure | null $conversion): static
    {
        $this->conversion = $conversion;

        return $this;
    }

    /**
     * Définit la taille maximale d'un fichier en KB
     */
    public function maxFileSize(int | Closure | null $maxSize): static
    {
        $this->maxFileSize = $maxSize;

        return $this;
    }

    /**
     * Permet de réorganiser les fichiers sélectionnés (drag & drop)
     */
    public function allowReordering(bool | Closure $allow = true): static
    {
        $this->allowReordering = $allow;

        return $this;
    }

    /**
     * Permet de télécharger les fichiers depuis l'aperçu
     */
    public function downloadable(bool | Closure $downloadable = true): static
    {
        $this->downloadable = $downloadable;

        return $this;
    }

    /**
     * Méthode de convenance pour sélection unique (équivalent à multiple(false))
     */
    public function single(): static
    {
        $this->multiple = false;
        $this->maxFiles = 1;
        $this->minFiles = 0;

        return $this;
    }

    /**
     * Méthode de convenance pour sélection multiple avec limites
     */
    public function limit(int $min, ?int $max = null): static
    {
        $this->multiple = true;
        $this->minFiles = $min;
        $this->maxFiles = $max;

        return $this;
    }

    /**
     * Définit un nombre exact de fichiers requis
     */
    public function exactFiles(int $count): static
    {
        $this->multiple = true;
        $this->minFiles = $count;
        $this->maxFiles = $count;

        return $this;
    }

    public function getAcceptedFileTypes(): array
    {
        return $this->evaluate($this->acceptedFileTypes);
    }

    public function isMultiple(): bool
    {
        return $this->evaluate($this->multiple);
    }

    public function getCollection(): string
    {
        return $this->evaluate($this->collection);
    }

    public function getMaxFiles(): ?int
    {
        return $this->evaluate($this->maxFiles);
    }

    public function getMinFiles(): int
    {
        return $this->evaluate($this->minFiles);
    }

    public function getShowUpload(): bool
    {
        return $this->evaluate($this->showUpload);
    }

    public function getShowLibrary(): bool
    {
        return $this->evaluate($this->showLibrary);
    }

    public function getConversion(): ?string
    {
        return $this->evaluate($this->conversion);
    }

    public function getMaxFileSize(): ?int
    {
        return $this->evaluate($this->maxFileSize);
    }

    public function canReorder(): bool
    {
        return $this->evaluate($this->allowReordering);
    }

    public function isDownloadable(): bool
    {
        return $this->evaluate($this->downloadable);
    }

    public function getSelectedMedia(): array
    {
        $value = $this->getState();

        if (blank($value)) {
            return [];
        }

        if ($this->isMultiple()) {
            if (is_array($value)) {
                // Convertir en entiers pour cohérence
                return array_map('intval', array_filter($value, fn($id) => !empty($id)));
            }
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                // Convertir en entiers pour cohérence
                return array_map('intval', array_filter($decoded, fn($id) => !empty($id)));
            }
            return [];
        }

        // Pour single, convertir en entier
        return [(int) $value];
    }

    public function getSelectedMediaFiles(): array
    {
        $ids = $this->getSelectedMedia();
        
        if (empty($ids)) {
            return [];
        }

        $files = MediaFile::whereIn('id', $ids)
            ->with('conversions')
            ->get()
            ->keyBy(function ($file) {
                // Utiliser l'ID comme entier pour la clé
                return (int) $file->id;
            })
            ->map(function ($file) {
                return [
                    'id' => (int) $file->id, // S'assurer que c'est un entier
                    'uuid' => $file->uuid,
                    'file_name' => $file->file_name,
                    'url' => route('media-library-pro.serve', ['media' => $file->uuid]),
                    'conversions' => $file->conversions->keyBy('conversion_name')->map(function ($conv) {
                        return route('media-library-pro.conversion', ['media' => $conv->mediaFile->uuid, 'conversion' => $conv->conversion_name]);
                    })->toArray(),
                ];
            })
            ->toArray();

        return $files;
    }
}

