<?php

namespace Xavier\MediaLibraryPro\Tests\Feature\Http;

use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Tests\Factories\MediaFileFactory;
use Xavier\MediaLibraryPro\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class MediaServeControllerTest extends TestCase
{
    public function test_serve_returns_file_by_uuid(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        Storage::disk('public')->put('media/test.jpg', 'test image content');

        $response = $this->get('/media-library-pro/serve/' . $mediaFile->uuid);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
        $response->assertHeader('Content-Disposition', 'inline; filename="' . $mediaFile->file_name . '"');
    }

    public function test_serve_returns_file_by_id(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        Storage::disk('public')->put('media/test.jpg', 'test image content');

        $response = $this->get('/media-library-pro/serve/' . $mediaFile->id);

        $response->assertStatus(200);
    }

    public function test_serve_returns_404_for_nonexistent_media(): void
    {
        $response = $this->get('/media-library-pro/serve/nonexistent-uuid');

        $response->assertStatus(404);
    }

    public function test_serve_returns_404_if_file_not_exists(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/nonexistent.jpg',
        ]);

        $response = $this->get('/media-library-pro/serve/' . $mediaFile->uuid);

        $response->assertStatus(404);
    }

    public function test_serve_sets_correct_content_type(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.pdf',
            'mime_type' => 'application/pdf',
            'file_name' => 'test.pdf',
        ]);

        Storage::disk('public')->put('media/test.pdf', 'test pdf content');

        $response = $this->get('/media-library-pro/serve/' . $mediaFile->uuid);

        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_serve_sets_cache_headers(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
        ]);

        Storage::disk('public')->put('media/test.jpg', 'test content');

        $response = $this->get('/media-library-pro/serve/' . $mediaFile->uuid);

        $response->assertHeader('Cache-Control', 'public, max-age=31536000');
    }
}

