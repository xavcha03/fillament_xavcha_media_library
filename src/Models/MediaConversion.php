<?php

namespace Xavier\MediaLibraryPro\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MediaConversion extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Xavier\MediaLibraryPro\Tests\Factories\MediaConversionFactory::new();
    }
    protected $table = 'media_conversions';

    protected $fillable = [
        'media_file_id',
        'conversion_name',
        'file_name',
        'disk',
        'path',
        'width',
        'height',
        'size',
        'quality',
        'format',
        'generated_at',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'size' => 'integer',
        'quality' => 'integer',
        'generated_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'media_file_id');
    }

    /**
     * Méthodes utilitaires
     */
    public function getUrl(): string
    {
        $storage = Storage::disk($this->disk);
        
        if ($this->disk === 'public' || $this->disk === 'local') {
            return $storage->url($this->path);
        }

        // Pour les disques cloud (S3, etc.) - futur
        return $storage->url($this->path);
    }

    public function getPath(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    public function getStorageDisk()
    {
        return Storage::disk($this->disk);
    }

    /**
     * Régénère la conversion
     */
    public function regenerate(): bool
    {
        // Cette méthode sera appelée par MediaConversionService
        // pour régénérer la conversion si nécessaire
        return true;
    }

    /**
     * Supprime le fichier de conversion
     */
    public function deleteFile(): bool
    {
        $storage = $this->getStorageDisk();

        if ($storage->exists($this->path)) {
            return $storage->delete($this->path);
        }

        return false;
    }

    /**
     * Supprime le modèle et le fichier
     */
    public function delete(): ?bool
    {
        $this->deleteFile();
        return parent::delete();
    }
}

