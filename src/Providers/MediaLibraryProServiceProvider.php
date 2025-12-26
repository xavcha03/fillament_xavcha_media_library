<?php

namespace Xavier\MediaLibraryPro\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Filament\Http\Middleware\Authenticate;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;

class MediaLibraryProServiceProvider extends ServiceProvider
{
    public static string $name = 'media-library-pro';

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'media-library-pro'
        );

        // Publier la configuration
        $this->publishes([
            __DIR__ . '/../../config/media-library-pro.php' => config_path('media-library-pro.php'),
        ], 'media-library-pro-config');

        // Publier les migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'media-library-pro-migrations');

        // Enregistrer les composants Livewire
        Livewire::component('media-library-pro::media-library', \Xavier\MediaLibraryPro\Livewire\MediaLibrary::class);
        Livewire::component('media-library-pro::media-library-picker', \Xavier\MediaLibraryPro\Livewire\MediaLibraryPicker::class);
        
        // Enregistrer aussi avec un nom simple pour compatibilité
        Livewire::component('media-library-picker', \Xavier\MediaLibraryPro\Livewire\MediaLibraryPicker::class);

        // Enregistrer les assets CSS pour Filament
        $this->app->booted(function () {
            FilamentAsset::register(
                [
                    Css::make('media-library-pro', __DIR__ . '/../../resources/css/media-library-pro.css'),
                ],
                package: 'media-library-pro'
            );
            
            // Charger les routes après que l'application soit complètement chargée
            $this->loadRoutes();
        });
    }

    public function register(): void
    {
        $configPath = config_path('media-library-pro.php');
        
        if (file_exists($configPath)) {
            $this->mergeConfigFrom(
                $configPath,
                'media-library-pro'
            );
        } else {
            // Utiliser la config par défaut du package si elle n'existe pas
            $defaultConfigPath = __DIR__ . '/../../config/media-library-pro.php';
            if (file_exists($defaultConfigPath)) {
                $this->mergeConfigFrom(
                    $defaultConfigPath,
                    'media-library-pro'
                );
            }
        }

        // Enregistrer les services en singletons
        $this->app->singleton(\Xavier\MediaLibraryPro\Services\MediaStorageService::class);
        $this->app->singleton(\Xavier\MediaLibraryPro\Services\MediaUploadService::class);
        $this->app->singleton(\Xavier\MediaLibraryPro\Services\MediaConversionService::class);
        
        // Note: ImageConversionService a été remplacé par MediaConversionService
        // Il est conservé pour compatibilité mais n'est plus utilisé
    }

    protected function loadRoutes(): void
    {
        // Enregistrer les routes pour servir les médias
        Route::middleware(['web'])
            ->prefix('media-library-pro')
            ->name('media-library-pro.')
            ->group(function () {
                // Route pour servir les conversions (UUID ou ID)
                Route::get('/conversion/{media}/{conversion}', [\Xavier\MediaLibraryPro\Http\Controllers\MediaConversionController::class, 'show'])
                    ->name('conversion');
                
                // Route pour servir les médias (UUID ou ID)
                Route::get('/serve/{media}', [\Xavier\MediaLibraryPro\Http\Controllers\MediaServeController::class, 'show'])
                    ->where('media', '[0-9a-fA-F\-]+') // UUID format
                    ->name('serve');
            });
    }
}

