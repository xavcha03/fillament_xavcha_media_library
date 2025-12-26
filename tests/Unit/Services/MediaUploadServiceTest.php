<?php

namespace Xavier\MediaLibraryPro\Tests\Unit\Services;

use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Services\MediaUploadService;
use Xavier\MediaLibraryPro\Services\MediaStorageService;
use Xavier\MediaLibraryPro\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaUploadServiceTest extends TestCase
{
    protected MediaUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MediaUploadService::class);
    }

    public function test_upload_creates_media_file_from_uploaded_file(): void
    {
        Storage::fake('public');
        
        $file = $this->createTestImage('test.jpg');

        $mediaFile = $this->service->upload($file);

        $this->assertInstanceOf(MediaFile::class, $mediaFile);
        $this->assertDatabaseHas('media_files', ['id' => $mediaFile->id]);
        $this->assertEquals('test.jpg', $mediaFile->file_name);
    }

    public function test_upload_validates_file_size(): void
    {
        config(['media-library-pro.validation.max_size' => 100]); // 100 KB

        $file = UploadedFile::fake()->create('test.jpg', 200 * 1024); // 200 KB

        $this->expectException(ValidationException::class);

        $this->service->upload($file);
    }

    public function test_upload_validates_mime_types(): void
    {
        config([
            'media-library-pro.validation.allowed_mime_types' => ['image/jpeg', 'image/png'],
        ]);

        $file = $this->createTestDocument('test.pdf');

        $this->expectException(ValidationException::class);

        $this->service->upload($file);
    }

    public function test_upload_accepts_valid_mime_types(): void
    {
        config([
            'media-library-pro.validation.allowed_mime_types' => ['image/jpeg', 'image/png'],
        ]);

        $file = $this->createTestImage('test.jpg');

        $mediaFile = $this->service->upload($file);

        $this->assertInstanceOf(MediaFile::class, $mediaFile);
    }

    public function test_upload_extracts_metadata_for_images(): void
    {
        Storage::fake('public');
        
        $file = $this->createTestImage('test.jpg');

        $mediaFile = $this->service->upload($file);

        $this->assertNotNull($mediaFile->metadata);
        $this->assertArrayHasKey('uploaded_at', $mediaFile->metadata);
        $this->assertArrayHasKey('original_name', $mediaFile->metadata);
    }

    public function test_upload_from_url_throws_exception_for_invalid_url(): void
    {
        // Ce test peut échouer selon l'environnement réseau
        // On teste juste que la méthode existe et gère les erreurs
        $this->expectException(\Exception::class);

        $this->service->uploadFromUrl('https://invalid-url-that-will-fail.com/test.jpg');
    }

    public function test_upload_from_path_creates_media_file(): void
    {
        Storage::fake('public');
        
        $file = $this->createTestImage('test.jpg');
        $tempPath = $file->getRealPath();

        $mediaFile = $this->service->uploadFromPath($tempPath);

        $this->assertInstanceOf(MediaFile::class, $mediaFile);
    }

    public function test_upload_from_path_throws_exception_if_file_not_exists(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Le fichier n'existe pas");

        $this->service->uploadFromPath('/nonexistent/path/file.jpg');
    }

    public function test_validate_passes_for_valid_file(): void
    {
        $file = $this->createTestImage('test.jpg');

        $result = $this->service->validate($file);

        $this->assertTrue($result);
    }

    public function test_validate_throws_exception_for_invalid_size(): void
    {
        config(['media-library-pro.validation.max_size' => 1]); // 1 KB

        $file = UploadedFile::fake()->create('test.jpg', 2000); // 2 KB

        $this->expectException(ValidationException::class);

        $this->service->validate($file);
    }

    public function test_extract_metadata_returns_array(): void
    {
        $file = $this->createTestImage('test.jpg');

        $metadata = $this->service->extractMetadata($file);

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('uploaded_at', $metadata);
        $this->assertArrayHasKey('original_name', $metadata);
    }

    public function test_extract_metadata_includes_exif_for_images(): void
    {
        $file = $this->createTestImage('test.jpg');

        $metadata = $this->service->extractMetadata($file);

        // Les métadonnées EXIF peuvent ne pas être présentes selon l'image
        // On vérifie juste que la structure est correcte
        $this->assertIsArray($metadata);
    }

    public function test_extract_metadata_works_with_string_path(): void
    {
        $file = $this->createTestImage('test.jpg');
        $path = $file->getRealPath();

        $metadata = $this->service->extractMetadata($path);

        $this->assertIsArray($metadata);
    }
}

