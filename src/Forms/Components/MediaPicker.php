<?php

namespace Xavier\MediaLibraryPro\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Xavier\MediaLibraryPro\Models\MediaFile;

class MediaPicker extends Field
{
    // Field inclut déjà : CanBeMarkedAsRequired, HasHelperText, HasHint, HasLabel
    use HasPlaceholder;

    protected string $view = 'media-library-pro::forms.components.media-picker';

    protected bool | Closure $multiple = false;

    protected array | Closure $acceptedFileTypes = [];

    protected string | Closure | null $relationship = null;

    protected string | Closure | null $relationshipTitleAttribute = null;

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

    public function relationship(string | Closure | null $name, string | Closure | null $titleAttribute = null): static
    {
        $this->relationship = $name;
        $this->relationshipTitleAttribute = $titleAttribute;

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

    public function getRelationship(): ?Relation
    {
        $name = $this->getRelationshipName();

        if (blank($name)) {
            return null;
        }

        return $this->getModelInstance()->{$name}();
    }

    public function getRelationshipName(): ?string
    {
        return $this->evaluate($this->relationship);
    }

    public function getSelectedMedia(): array
    {
        $value = $this->getState();

        if (blank($value)) {
            return [];
        }

        if ($this->isMultiple()) {
            return is_array($value) ? $value : [$value];
        }

        return [$value];
    }

    public function getMediaOptions(): array
    {
        $acceptedTypes = $this->getAcceptedFileTypes();
        
        $query = MediaFile::query();

        if (!empty($acceptedTypes)) {
            $query->where(function ($q) use ($acceptedTypes) {
                foreach ($acceptedTypes as $type) {
                    if (str_ends_with($type, '/*')) {
                        $mimePrefix = str_replace('/*', '', $type);
                        $q->orWhere('mime_type', 'like', $mimePrefix . '/%');
                    } else {
                        $q->orWhere('mime_type', $type);
                    }
                }
            });
        }

        return $query->limit(100)->get()->mapWithKeys(function ($media) {
            return [$media->id => $media->file_name];
        })->toArray();
    }
}
