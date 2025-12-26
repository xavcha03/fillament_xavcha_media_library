<?php

namespace Xavier\MediaLibraryPro\Tests\Unit\Models;

use Xavier\MediaLibraryPro\Models\MediaAttachment;
use Xavier\MediaLibraryPro\Models\MediaConversion;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Tests\Factories\MediaAttachmentFactory;
use Xavier\MediaLibraryPro\Tests\Factories\MediaConversionFactory;
use Xavier\MediaLibraryPro\Tests\Factories\MediaFileFactory;
use Xavier\MediaLibraryPro\Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaFileTest extends TestCase
{
    public function test_uuid_is_generated_automatically_on_creation(): void
    {
        $mediaFile = MediaFileFactory::new()->create(['uuid' => null]);

        $this->assertNotNull($mediaFile->uuid);
        $this->assertTrue(Str::isUuid($mediaFile->uuid));
    }

    public function test_can_have_attachments(): void
    {
        $mediaFile = MediaFileFactory::new()->create();
        $attachment = MediaAttachmentFactory::new()->create(['media_file_id' => $mediaFile->id]);

        $this->assertTrue($mediaFile->attachments->contains($attachment));
        $this->assertEquals(1, $mediaFile->attachments->count());
    }

    public function test_can_have_conversions(): void
    {
        $mediaFile = MediaFileFactory::new()->image()->create();
        $conversion = MediaConversionFactory::new()->forMediaFile($mediaFile)->create();

        $this->assertTrue($mediaFile->conversions->contains($conversion));
        $this->assertEquals(1, $mediaFile->conversions->count());
    }

    public function test_get_url_returns_public_url_for_public_disk(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
        ]);

        Storage::disk('public')->put('media/test.jpg', 'test content');

        $url = $mediaFile->getUrl();
        
        $this->assertStringContainsString('media/test.jpg', $url);
    }

    public function test_get_path_returns_physical_path(): void
    {
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
        ]);

        $path = $mediaFile->getPath();
        
        $this->assertStringContainsString('media/test.jpg', $path);
    }

    public function test_get_storage_disk_returns_storage_instance(): void
    {
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
        ]);

        $disk = $mediaFile->getStorageDisk();
        
        $this->assertInstanceOf(\Illuminate\Contracts\Filesystem\Filesystem::class, $disk);
    }

    public function test_is_image_detects_image_mime_types(): void
    {
        $imageFile = MediaFileFactory::new()->image()->create(['mime_type' => 'image/jpeg']);
        $videoFile = MediaFileFactory::new()->video()->create();
        $documentFile = MediaFileFactory::new()->document()->create();

        $this->assertTrue($imageFile->isImage());
        $this->assertFalse($videoFile->isImage());
        $this->assertFalse($documentFile->isImage());
    }

    public function test_is_video_detects_video_mime_types(): void
    {
        $imageFile = MediaFileFactory::new()->image()->create();
        $videoFile = MediaFileFactory::new()->video()->create(['mime_type' => 'video/mp4']);

        $this->assertTrue($videoFile->isVideo());
        $this->assertFalse($imageFile->isVideo());
    }

    public function test_is_audio_detects_audio_mime_types(): void
    {
        $audioFile = MediaFileFactory::new()->create(['mime_type' => 'audio/mpeg']);
        $imageFile = MediaFileFactory::new()->image()->create();

        $this->assertTrue($audioFile->isAudio());
        $this->assertFalse($imageFile->isAudio());
    }

    public function test_is_document_detects_document_mime_types(): void
    {
        $documentFile = MediaFileFactory::new()->document()->create(['mime_type' => 'application/pdf']);
        $imageFile = MediaFileFactory::new()->image()->create();

        $this->assertTrue($documentFile->isDocument());
        $this->assertFalse($imageFile->isDocument());
    }

    public function test_get_dimensions_returns_width_and_height_for_images(): void
    {
        $imageFile = MediaFileFactory::new()->image()->create([
            'width' => 800,
            'height' => 600,
        ]);

        $dimensions = $imageFile->getDimensions();

        $this->assertIsArray($dimensions);
        $this->assertEquals(800, $dimensions['width']);
        $this->assertEquals(600, $dimensions['height']);
    }

    public function test_get_dimensions_returns_null_for_non_images(): void
    {
        $documentFile = MediaFileFactory::new()->document()->create();

        $this->assertNull($documentFile->getDimensions());
    }

    public function test_get_formatted_size_formats_bytes_correctly(): void
    {
        $smallFile = MediaFileFactory::new()->create(['size' => 2048]); // 2 KB
        $mediumFile = MediaFileFactory::new()->create(['size' => 1024 * 1024 * 2]); // 2 MB
        $largeFile = MediaFileFactory::new()->create(['size' => 1024 * 1024 * 1024 * 2]); // 2 GB

        $this->assertStringContainsString('KB', $smallFile->getFormattedSize());
        $this->assertStringContainsString('MB', $mediumFile->getFormattedSize());
        $this->assertStringContainsString('GB', $largeFile->getFormattedSize());
    }

    public function test_get_extension_returns_file_extension(): void
    {
        $file = MediaFileFactory::new()->create(['file_name' => 'test.jpg']);

        $this->assertEquals('jpg', $file->getExtension());
    }

    public function test_get_conversion_url_returns_url_for_existing_conversion(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->image()->create();
        $conversion = MediaConversionFactory::new()
            ->forMediaFile($mediaFile)
            ->conversion('thumb')
            ->create([
                'path' => 'media/conversions/thumb.jpg',
            ]);

        Storage::disk('public')->put('media/conversions/thumb.jpg', 'test');

        $url = $mediaFile->getConversionUrl('thumb');

        $this->assertNotNull($url);
        $this->assertStringContainsString('thumb.jpg', $url);
    }

    public function test_get_conversion_url_returns_null_for_non_existing_conversion(): void
    {
        $mediaFile = MediaFileFactory::new()->image()->create();

        $this->assertNull($mediaFile->getConversionUrl('nonexistent'));
    }

    public function test_delete_file_removes_physical_file_and_conversions(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
        ]);
        
        $conversion = MediaConversionFactory::new()
            ->forMediaFile($mediaFile)
            ->create([
                'disk' => 'public',
                'path' => 'media/conversions/test.jpg',
            ]);

        Storage::disk('public')->put('media/test.jpg', 'test content');
        Storage::disk('public')->put('media/conversions/test.jpg', 'conversion content');

        $mediaFile->deleteFile();

        $this->assertFalse(Storage::disk('public')->exists('media/test.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('media/conversions/test.jpg'));
    }

    public function test_delete_removes_model_and_physical_file(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
        ]);

        Storage::disk('public')->put('media/test.jpg', 'test content');

        $mediaFile->delete();

        // Avec SoftDeletes, on vérifie que le modèle est soft deleted
        $this->assertSoftDeleted('media_files', ['id' => $mediaFile->id]);
        $this->assertFalse(Storage::disk('public')->exists('media/test.jpg'));
    }
}

