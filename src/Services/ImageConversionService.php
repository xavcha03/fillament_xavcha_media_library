<?php

namespace Xavier\MediaLibraryPro\Services;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Illuminate\Support\Facades\Storage;

class ImageConversionService
{
    protected string $driver = 'glide';

    public function __construct()
    {
        $this->driver = config('media-library-pro.conversions.driver', 'glide');
    }

    public function getConversionUrl(Media $media, string $conversionName = 'thumb'): ?string
    {
        if (!config('media-library-pro.conversions.enabled', true)) {
            return $media->getUrl();
        }

        // Vérifier si la conversion existe déjà
        if ($media->hasGeneratedConversion($conversionName)) {
            return $media->getUrl($conversionName);
        }

        // Générer la conversion à la volée avec Glide
        if ($this->driver === 'glide') {
            return $this->generateGlideUrl($media, $conversionName);
        }

        return $media->getUrl();
    }

    protected function generateGlideUrl(Media $media, string $conversionName): string
    {
        $presets = config('media-library-pro.conversions.presets', []);
        
        if (!isset($presets[$conversionName])) {
            return $media->getUrl();
        }

        $preset = $presets[$conversionName];
        $params = [];

        if (isset($preset['width'])) {
            $params['w'] = $preset['width'];
        }

        if (isset($preset['height'])) {
            $params['h'] = $preset['height'];
        }

        if (isset($preset['fit'])) {
            $params['fit'] = $preset['fit'];
        }

        if (isset($preset['quality'])) {
            $params['q'] = $preset['quality'];
        }

        // Construire l'URL Glide
        // Utiliser la route publique pour les conversions
        try {
            $baseUrl = route('media-library-pro.conversion', ['media' => $media->id, 'conversion' => $conversionName]);
        } catch (\Exception $e) {
            // Fallback si la route n'existe pas
            $baseUrl = url('/media-library-pro/conversion/' . $media->id . '/' . $conversionName);
        }
        
        return $baseUrl . '?' . http_build_query($params);
    }

    public function registerConversions(Media $media): void
    {
        if (!config('media-library-pro.conversions.enabled', true)) {
            return;
        }

        $presets = config('media-library-pro.conversions.presets', []);

        foreach ($presets as $conversionName => $preset) {
            $media->addMediaConversion($conversionName)
                ->width($preset['width'] ?? null)
                ->height($preset['height'] ?? null)
                ->fit($preset['fit'] ?? 'contain')
                ->quality($preset['quality'] ?? 90)
                ->performOnCollections($media->collection_name ?? '*');
        }
    }

    public function generateConversion(Media $media, string $conversionName): bool
    {
        try {
            $media->performConversions();
            return true;
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la génération de la conversion', [
                'media_id' => $media->id,
                'conversion' => $conversionName,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function getResponsiveUrls(Media $media, array $breakpoints = [640, 768, 1024, 1280, 1920]): array
    {
        $urls = [];

        foreach ($breakpoints as $breakpoint) {
            $urls[$breakpoint] = $this->getConversionUrl($media, "responsive-{$breakpoint}");
        }

        return $urls;
    }
}

