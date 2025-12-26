<?php

namespace Xavier\MediaLibraryPro\Tests\Unit\Models;

use Xavier\MediaLibraryPro\Models\MediaConversion;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Tests\Factories\MediaConversionFactory;
use Xavier\MediaLibraryPro\Tests\Factories\MediaFileFactory;
use Xavier\MediaLibraryPro\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class MediaConversionTest extends TestCase
{
    public function test_belongs_to_media_file(): void
    {
        $mediaFile = MediaFileFactory::new()->image()->create();
        $conversion = MediaConversionFactory::new()->forMediaFile($mediaFile)->create();

        $this->assertInstanceOf(MediaFile::class, $conversion->mediaFile);
        $this->assertEquals($mediaFile->id, $conversion->mediaFile->id);
    }

    public function test_get_url_returns_public_url(): void
    {
        Storage::fake('public');
        
        $conversion = MediaConversionFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/conversions/test.jpg',
        ]);

        Storage::disk('public')->put('media/conversions/test.jpg', 'test content');

        $url = $conversion->getUrl();

        $this->assertStringContainsString('media/conversions/test.jpg', $url);
    }

    public function test_get_path_returns_physical_path(): void
    {
        $conversion = MediaConversionFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/conversions/test.jpg',
        ]);

        $path = $conversion->getPath();

        $this->assertStringContainsString('media/conversions/test.jpg', $path);
    }

    public function test_get_storage_disk_returns_storage_instance(): void
    {
        $conversion = MediaConversionFactory::new()->create([
            'disk' => 'public',
        ]);

        $disk = $conversion->getStorageDisk();

        $this->assertInstanceOf(\Illuminate\Contracts\Filesystem\Filesystem::class, $disk);
    }

    public function test_delete_file_removes_physical_file(): void
    {
        Storage::fake('public');
        
        $conversion = MediaConversionFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/conversions/test.jpg',
        ]);

        Storage::disk('public')->put('media/conversions/test.jpg', 'test content');

        $result = $conversion->deleteFile();

        $this->assertTrue($result);
        $this->assertFalse(Storage::disk('public')->exists('media/conversions/test.jpg'));
    }

    public function test_delete_file_returns_false_if_file_does_not_exist(): void
    {
        Storage::fake('public');
        
        $conversion = MediaConversionFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/conversions/nonexistent.jpg',
        ]);

        $result = $conversion->deleteFile();

        $this->assertFalse($result);
    }

    public function test_delete_removes_model_and_physical_file(): void
    {
        Storage::fake('public');
        
        $conversion = MediaConversionFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/conversions/test.jpg',
        ]);

        Storage::disk('public')->put('media/conversions/test.jpg', 'test content');

        $conversion->delete();

        $this->assertDatabaseMissing('media_conversions', ['id' => $conversion->id]);
        $this->assertFalse(Storage::disk('public')->exists('media/conversions/test.jpg'));
    }

    public function test_regenerate_returns_true(): void
    {
        $conversion = MediaConversionFactory::new()->create();

        $result = $conversion->regenerate();

        $this->assertTrue($result);
    }
}





