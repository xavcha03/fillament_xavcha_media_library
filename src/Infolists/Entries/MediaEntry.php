<?php

namespace Xavier\MediaLibraryPro\Infolists\Entries;

use Filament\Infolists\Components\Entry;
use Xavier\MediaLibraryPro\Models\MediaAttachment;

class MediaEntry extends Entry
{
    protected string $view = 'media-library-pro::infolists.entries.media-entry';

    protected ?string $collection = null;
    protected ?string $conversion = null;
    protected int $size = 200;
    protected bool $multiple = false;

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function collection(?string $collection): static
    {
        $this->collection = $collection;

        return $this;
    }

    public function conversion(?string $conversion): static
    {
        $this->conversion = $conversion;

        return $this;
    }

    public function size(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getCollection(): ?string
    {
        return $this->collection;
    }

    public function getConversion(): ?string
    {
        return $this->conversion;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function getMediaItems($record): array
    {
        if (!$record) {
            return [];
        }

        // Vérifier si le modèle utilise le trait HasMediaFiles
        if (!method_exists($record, 'getMediaFiles')) {
            return [];
        }

        $collection = $this->collection ?? 'default';
        $attachments = $record->getMediaFiles($collection);

        if ($this->multiple) {
            return $attachments->all();
        }

        $first = $attachments->first();
        return $first ? [$first] : [];
    }
}
