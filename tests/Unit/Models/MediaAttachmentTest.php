<?php

namespace Xavier\MediaLibraryPro\Tests\Unit\Models;

use Xavier\MediaLibraryPro\Models\MediaAttachment;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Tests\Factories\MediaAttachmentFactory;
use Xavier\MediaLibraryPro\Tests\Factories\MediaFileFactory;
use Xavier\MediaLibraryPro\Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class MediaAttachmentTest extends TestCase
{
    public function test_belongs_to_media_file(): void
    {
        $mediaFile = MediaFileFactory::new()->create();
        $attachment = MediaAttachmentFactory::new()->create(['media_file_id' => $mediaFile->id]);

        $this->assertInstanceOf(MediaFile::class, $attachment->mediaFile);
        $this->assertEquals($mediaFile->id, $attachment->mediaFile->id);
    }

    public function test_has_polymorphic_model_relation(): void
    {
        // Créer un modèle de test
        $testModel = new class extends \Illuminate\Database\Eloquent\Model {
            protected $table = 'test_models';
            protected $fillable = ['name'];
        };
        
        // Pour le test, on simule juste la relation
        $attachment = MediaAttachmentFactory::new()->create([
            'model_type' => get_class($testModel),
            'model_id' => 1,
        ]);

        $this->assertEquals(get_class($testModel), $attachment->model_type);
        $this->assertEquals(1, $attachment->model_id);
    }

    public function test_get_url_delegates_to_media_file(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
        ]);
        
        $attachment = MediaAttachmentFactory::new()->create(['media_file_id' => $mediaFile->id]);
        
        Storage::disk('public')->put('media/test.jpg', 'test content');

        $url = $attachment->getUrl();

        $this->assertStringContainsString('media/test.jpg', $url);
    }

    public function test_get_conversion_url_delegates_to_media_file(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->image()->create();
        $attachment = MediaAttachmentFactory::new()->create(['media_file_id' => $mediaFile->id]);
        
        \Xavier\MediaLibraryPro\Tests\Factories\MediaConversionFactory::new()
            ->forMediaFile($mediaFile)
            ->conversion('thumb')
            ->create([
                'path' => 'media/conversions/thumb.jpg',
            ]);

        Storage::disk('public')->put('media/conversions/thumb.jpg', 'test');

        $url = $attachment->getConversionUrl('thumb');

        $this->assertNotNull($url);
    }

    public function test_get_path_delegates_to_media_file(): void
    {
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
        ]);
        
        $attachment = MediaAttachmentFactory::new()->create(['media_file_id' => $mediaFile->id]);

        $path = $attachment->getPath();

        $this->assertStringContainsString('media/test.jpg', $path);
    }

    public function test_file_name_accessor_delegates_to_media_file(): void
    {
        $mediaFile = MediaFileFactory::new()->create(['file_name' => 'test.jpg']);
        $attachment = MediaAttachmentFactory::new()->create(['media_file_id' => $mediaFile->id]);

        $this->assertEquals('test.jpg', $attachment->file_name);
    }

    public function test_mime_type_accessor_delegates_to_media_file(): void
    {
        $mediaFile = MediaFileFactory::new()->image()->create(['mime_type' => 'image/jpeg']);
        $attachment = MediaAttachmentFactory::new()->create(['media_file_id' => $mediaFile->id]);

        $this->assertEquals('image/jpeg', $attachment->mime_type);
    }

    public function test_size_accessor_delegates_to_media_file(): void
    {
        $mediaFile = MediaFileFactory::new()->create(['size' => 12345]);
        $attachment = MediaAttachmentFactory::new()->create(['media_file_id' => $mediaFile->id]);

        $this->assertEquals(12345, $attachment->size);
    }

    public function test_is_image_delegates_to_media_file(): void
    {
        $imageFile = MediaFileFactory::new()->image()->create();
        $attachment = MediaAttachmentFactory::new()->create(['media_file_id' => $imageFile->id]);

        $this->assertTrue($attachment->isImage());
    }

    public function test_is_video_delegates_to_media_file(): void
    {
        $videoFile = MediaFileFactory::new()->video()->create();
        $attachment = MediaAttachmentFactory::new()->create(['media_file_id' => $videoFile->id]);

        $this->assertTrue($attachment->isVideo());
    }

    public function test_is_audio_delegates_to_media_file(): void
    {
        $audioFile = MediaFileFactory::new()->create(['mime_type' => 'audio/mpeg']);
        $attachment = MediaAttachmentFactory::new()->create(['media_file_id' => $audioFile->id]);

        $this->assertTrue($attachment->isAudio());
    }

    public function test_delete_only_removes_attachment_not_file(): void
    {
        Storage::fake('public');
        
        $mediaFile = MediaFileFactory::new()->create([
            'disk' => 'public',
            'path' => 'media/test.jpg',
        ]);
        
        $attachment = MediaAttachmentFactory::new()->create(['media_file_id' => $mediaFile->id]);
        
        Storage::disk('public')->put('media/test.jpg', 'test content');

        $attachment->delete();

        // Avec SoftDeletes, on vérifie que l'attachment est soft deleted
        $this->assertSoftDeleted('media_attachments', ['id' => $attachment->id]);
        $this->assertDatabaseHas('media_files', ['id' => $mediaFile->id, 'deleted_at' => null]);
        $this->assertTrue(Storage::disk('public')->exists('media/test.jpg'));
    }
}

