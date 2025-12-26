<?php

namespace Xavier\MediaLibraryPro\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MediaAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return \Xavier\MediaLibraryPro\Tests\Factories\MediaAttachmentFactory::new();
    }

    protected $table = 'media_attachments';

    protected $fillable = [
        'media_file_id',
        'model_type',
        'model_id',
        'collection_name',
        'order',
        'custom_properties',
        'is_primary',
    ];

    protected $casts = [
        'custom_properties' => 'array',
        'order' => 'integer',
        'is_primary' => 'boolean',
    ];

    /**
     * Relations
     */
    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'media_file_id');
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Méthodes utilitaires
     */
    public function getUrl(): string
    {
        return $this->mediaFile->getUrl();
    }

    public function getConversionUrl(string $conversionName): ?string
    {
        return $this->mediaFile->getConversionUrl($conversionName);
    }

    public function getPath(): string
    {
        return $this->mediaFile->getPath();
    }

    /**
     * Accès aux propriétés du MediaFile
     */
    public function getFileNameAttribute(): string
    {
        return $this->mediaFile->file_name;
    }

    public function getMimeTypeAttribute(): ?string
    {
        return $this->mediaFile->mime_type;
    }

    public function getSizeAttribute(): int
    {
        return $this->mediaFile->size;
    }

    public function isImage(): bool
    {
        return $this->mediaFile->isImage();
    }

    public function isVideo(): bool
    {
        return $this->mediaFile->isVideo();
    }

    public function isAudio(): bool
    {
        return $this->mediaFile->isAudio();
    }

    /**
     * Supprime l'attachment (pas le fichier)
     */
    public function delete(): ?bool
    {
        // Ne pas supprimer le fichier, juste le lien
        return parent::delete();
    }
}

