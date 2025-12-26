<?php

namespace Xavier\MediaLibraryPro\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaFile extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return \Xavier\MediaLibraryPro\Tests\Factories\MediaFileFactory::new();
    }

    protected $table = 'media_files';

    protected $fillable = [
        'uuid',
        'file_name',
        'stored_name',
        'disk',
        'path',
        'mime_type',
        'size',
        'width',
        'height',
        'duration',
        'metadata',
        'alt_text',
        'description',
        'is_public',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
        'is_public' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($mediaFile) {
            if (empty($mediaFile->uuid)) {
                $mediaFile->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relations
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(MediaAttachment::class, 'media_file_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(MediaConversion::class, 'media_file_id');
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

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'video/');
    }

    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'audio/');
    }

    public function isDocument(): bool
    {
        $documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        return in_array($this->mime_type, $documentMimes);
    }

    public function getDimensions(): ?array
    {
        if (!$this->isImage()) {
            return null;
        }

        return [
            'width' => $this->width,
            'height' => $this->height,
        ];
    }

    public function getFormattedSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getExtension(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function getConversionUrl(string $conversionName): ?string
    {
        $conversion = $this->conversions()
            ->where('conversion_name', $conversionName)
            ->first();

        if (!$conversion) {
            return null;
        }

        return $conversion->getUrl();
    }

    /**
     * Supprime le fichier physique et toutes les conversions
     */
    public function deleteFile(): bool
    {
        $storage = $this->getStorageDisk();

        // Supprimer le fichier principal
        if ($storage->exists($this->path)) {
            $storage->delete($this->path);
        }

        // Supprimer toutes les conversions
        foreach ($this->conversions as $conversion) {
            $conversion->deleteFile();
        }

        return true;
    }

    /**
     * Supprime le modèle et le fichier physique
     */
    public function delete(): ?bool
    {
        $this->deleteFile();
        return parent::delete();
    }
}

