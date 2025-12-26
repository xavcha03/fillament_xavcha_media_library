<?php

namespace Xavier\MediaLibraryPro\Tests\Unit\Services;

use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Services\MediaStorageService;
use Xavier\MediaLibraryPro\Tests\Factories\MediaFileFactory;
use Xavier\MediaLibraryPro\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaStorageServiceTest extends TestCase
{
    protected MediaStorageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MediaStorageService::class);
    }

    public function test_store_creates_media_file_from_uploaded_file(): void
    {
        Storage::fake('public');
        
        $file = $this->createTestImage('test.jpg');

        $mediaFile = $this->service->store($file);

        $this->assertInstanceOf(MediaFile::class, $mediaFile);
        $this->assertDatabaseHas('media_files', ['id' => $mediaFile->id]);
        $this->assertEquals('test.jpg', $mediaFile->file_name);
        $this->assertTrue(Storage::disk('public')->exists($mediaFile->path));
    }

    public function test_store_creates_media_file_from_path(): void
    {
        Storage::fake('public');
        
        // Créer un fichier temporaire réel avec une extension
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.jpg';
        file_put_contents($tempFile, 'test image content');

        $mediaFile = $this->service->store($tempFile);

        $this->assertInstanceOf(MediaFile::class, $mediaFile);
        // Le fichier est stocké dans le fake storage, donc on vérifie juste que le MediaFile est créé
        $this->assertDatabaseHas('media_files', ['id' => $mediaFile->id]);
        
        // Nettoyer
        @unlink($tempFile);
    }

    public function test_store_uses_custom_disk(): void
    {
        Storage::fake('local');
        
        $file = $this->createTestImage('test.jpg');

        $mediaFile = $this->service->store($file, 'local');

        $this->assertEquals('local', $mediaFile->disk);
        $this->assertTrue(Storage::disk('local')->exists($mediaFile->path));
    }

    public function test_store_uses_custom_name(): void
    {
        Storage::fake('public');
        
        $file = $this->createTestImage('original.jpg');

        $mediaFile = $this->service->store($file, null, 'custom-name.jpg');

        $this->assertEquals('custom-name.jpg', $mediaFile->file_name);
    }

    public function test_store_generates_hash_naming_strategy(): void
    {
        config(['media-library-pro.storage.naming' => 'hash']);
        
        Storage::fake('public');
        
        $file = $this->createTestImage('test.jpg');

        $mediaFile = $this->service->store($file);

        $this->assertNotEquals('test.jpg', $mediaFile->stored_name);
        $this->assertStringEndsWith('.jpg', $mediaFile->stored_name);
    }

    public function test_store_generates_date_naming_strategy(): void
    {
        config(['media-library-pro.storage.naming' => 'date']);
        
        Storage::fake('public');
        
        $file = $this->createTestImage('test.jpg');

        $mediaFile = $this->service->store($file);

        $this->assertStringContainsString(date('Y/m/d'), $mediaFile->path);
    }

    public function test_store_extracts_image_dimensions(): void
    {
        Storage::fake('public');
        
        $file = $this->createTestImage('test.jpg');

        $mediaFile = $this->service->store($file);

        $this->assertNotNull($mediaFile->width);
        $this->assertNotNull($mediaFile->height);
    }

    public function test_store_does_not_extract_dimensions_for_non_images(): void
    {
        Storage::fake('public');
        
        $file = $this->createTestDocument('test.pdf');

        $mediaFile = $this->service->store($file);

        $this->assertNull($mediaFile->width);
        $this->assertNull($mediaFile->height);
    }

    public function test_delete_removes_physical_file(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
        ]);

        Storage::disk('public')->put('media/test.jpg', 'test content');

        $result = $this->service->delete($mediaFile);

        $this->assertTrue($result);
        $this->assertFalse(Storage::disk('public')->exists('media/test.jpg'));
    }

    public function test_delete_returns_false_if_file_not_exists(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/nonexistent.jpg',
        ]);

        $result = $this->service->delete($mediaFile);

        $this->assertFalse($result);
    }

    public function test_get_url_returns_public_url(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
        ]);

        Storage::disk('public')->put('media/test.jpg', 'test content');

        $url = $this->service->getUrl($mediaFile);

        $this->assertStringContainsString('media/test.jpg', $url);
    }

    public function test_get_path_returns_physical_path(): void
    {
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
        ]);

        $path = $this->service->getPath($mediaFile);

        $this->assertStringContainsString('media/test.jpg', $path);
    }

    public function test_move_returns_false_for_now(): void
    {
        $mediaFile = MediaFileFactory::new()->create();

        $result = $this->service->move($mediaFile, 'local');

        $this->assertFalse($result);
    }
}

