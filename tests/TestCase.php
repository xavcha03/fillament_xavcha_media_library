<?php

namespace Xavier\MediaLibraryPro\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurer le storage pour les tests
        Storage::fake('public');
        Storage::fake('local');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Xavier\MediaLibraryPro\Providers\MediaLibraryProServiceProvider::class,
        ];
    }

    protected function defineRoutes($router)
    {
        // Enregistrer les routes pour les tests Feature
        $router->middleware(['web'])
            ->prefix('media-library-pro')
            ->name('media-library-pro.')
            ->group(function ($router) {
                $router->get('/conversion/{media}/{conversion}', [\Xavier\MediaLibraryPro\Http\Controllers\MediaConversionController::class, 'show'])
                    ->name('conversion');
                
                $router->get('/serve/{media}', [\Xavier\MediaLibraryPro\Http\Controllers\MediaServeController::class, 'show'])
                    ->where('media', '[0-9a-fA-F\-]+')
                    ->name('serve');
            });
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configuration de la base de données
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configuration du package
        $app['config']->set('media-library-pro', [
            'storage' => [
                'disk' => 'public',
                'path' => 'media',
                'naming' => 'hash',
            ],
            'conversions' => [
                'enabled' => true,
                'driver' => 'gd',
                'presets' => [
                    'thumb' => [
                        'width' => 150,
                        'height' => 150,
                        'fit' => 'crop',
                        'quality' => 85,
                        'format' => 'jpg',
                    ],
                ],
            ],
            'validation' => [
                'max_size' => 10240,
                'allowed_mime_types' => [],
            ],
        ]);

        // Enregistrer les routes pour les tests Feature
        $router = $app['router'];
        $router->middleware('web')
            ->prefix('media-library-pro')
            ->name('media-library-pro.')
            ->group(function () use ($router) {
                $router->get('/conversion/{media}/{conversion}', [\Xavier\MediaLibraryPro\Http\Controllers\MediaConversionController::class, 'show'])
                    ->name('conversion');
                
                $router->get('/serve/{media}', [\Xavier\MediaLibraryPro\Http\Controllers\MediaServeController::class, 'show'])
                    ->where('media', '[0-9a-fA-F\-]+')
                    ->name('serve');
            });
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Crée un fichier de test UploadedFile
     */
    protected function createTestFile(string $name = 'test.jpg', string $mimeType = 'image/jpeg', int $size = 100): UploadedFile
    {
        return UploadedFile::fake()->image($name, 800, 600);
    }

    /**
     * Crée un fichier image de test
     */
    protected function createTestImage(string $name = 'test.jpg'): UploadedFile
    {
        return UploadedFile::fake()->image($name, 800, 600);
    }

    /**
     * Crée un fichier document de test
     */
    protected function createTestDocument(string $name = 'test.pdf'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 100, 'application/pdf');
    }

    /**
     * Crée un fichier vidéo de test
     */
    protected function createTestVideo(string $name = 'test.mp4'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 1000, 'video/mp4');
    }

    /**
     * Crée un fichier audio de test
     */
    protected function createTestAudio(string $name = 'test.mp3'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 500, 'audio/mpeg');
    }
}

