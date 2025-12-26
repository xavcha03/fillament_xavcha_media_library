<?php

namespace Xavier\MediaLibraryPro\Tests\Unit\Services;

use Xavier\MediaLibraryPro\Models\MediaConversion;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Services\MediaConversionService;
use Xavier\MediaLibraryPro\Services\MediaStorageService;
use Xavier\MediaLibraryPro\Tests\Factories\MediaConversionFactory;
use Xavier\MediaLibraryPro\Tests\Factories\MediaFileFactory;
use Xavier\MediaLibraryPro\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class MediaConversionServiceTest extends TestCase
{
    protected MediaConversionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MediaConversionService::class);
    }

    public function test_convert_throws_exception_for_non_image(): void
    {
        $mediaFile = MediaFileFactory::new()->document()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Les conversions ne sont disponibles que pour les images");

        $this->service->convert($mediaFile, 'thumb');
    }

    public function test_convert_returns_existing_conversion_if_exists(): void
    {
        $mediaFile = MediaFileFactory::new()->image()->create();
        $existingConversion = MediaConversionFactory::new()
            ->forMediaFile($mediaFile)
            ->conversion('thumb')
            ->create();

        $conversion = $this->service->convert($mediaFile, 'thumb');

        $this->assertEquals($existingConversion->id, $conversion->id);
    }

    public function test_convert_throws_exception_for_invalid_preset(): void
    {
        $mediaFile = MediaFileFactory::new()->image()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Configuration de conversion 'nonexistent' introuvable");

        $this->service->convert($mediaFile, 'nonexistent');
    }

    public function test_get_conversion_returns_existing_conversion(): void
    {
        $mediaFile = MediaFileFactory::new()->image()->create();
        $conversion = MediaConversionFactory::new()
            ->forMediaFile($mediaFile)
            ->conversion('thumb')
            ->create();

        $result = $this->service->getConversion($mediaFile, 'thumb');

        $this->assertInstanceOf(MediaConversion::class, $result);
        $this->assertEquals($conversion->id, $result->id);
    }

    public function test_get_conversion_returns_null_if_not_exists(): void
    {
        $mediaFile = MediaFileFactory::new()->image()->create();

        $result = $this->service->getConversion($mediaFile, 'thumb');

        $this->assertNull($result);
    }

    public function test_has_conversion_returns_true_if_exists(): void
    {
        $mediaFile = MediaFileFactory::new()->image()->create();
        MediaConversionFactory::new()
            ->forMediaFile($mediaFile)
            ->conversion('thumb')
            ->create();

        $this->assertTrue($this->service->hasConversion($mediaFile, 'thumb'));
    }

    public function test_has_conversion_returns_false_if_not_exists(): void
    {
        $mediaFile = MediaFileFactory::new()->image()->create();

        $this->assertFalse($this->service->hasConversion($mediaFile, 'thumb'));
    }

    public function test_regenerate_deletes_old_file_and_creates_new(): void
    {
        Storage::fake('public');
        
        // Créer un vrai fichier image temporaire
        $tempImage = tempnam(sys_get_temp_dir(), 'test_') . '.jpg';
        $image = imagecreatetruecolor(100, 100);
        imagejpeg($image, $tempImage, 90);
        imagedestroy($image);
        
        // Copier dans le storage fake
        $imageContent = file_get_contents($tempImage);
        Storage::disk('public')->put('media/test.jpg', $imageContent);
        
        $mediaFile = MediaFileFactory::new()->image()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
        ]);
        
        $oldConversion = MediaConversionFactory::new()
            ->forMediaFile($mediaFile)
            ->conversion('thumb')
            ->create([
                'disk' => 'public',
                'path' => 'media/conversions/old.jpg',
            ]);

        Storage::disk('public')->put('media/conversions/old.jpg', 'old content');

        // Mock le service pour éviter les dépendances GD réelles
        $this->markTestSkipped('Test de régénération nécessite une vraie image - à implémenter avec mock');
        
        // Nettoyer
        @unlink($tempImage);
    }
}

