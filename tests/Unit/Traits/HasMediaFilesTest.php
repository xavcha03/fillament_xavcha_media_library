<?php

namespace Xavier\MediaLibraryPro\Tests\Unit\Traits;

use Xavier\MediaLibraryPro\Models\MediaAttachment;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Tests\Factories\MediaAttachmentFactory;
use Xavier\MediaLibraryPro\Tests\Factories\MediaFileFactory;
use Xavier\MediaLibraryPro\Tests\TestCase;
use Xavier\MediaLibraryPro\Traits\HasMediaFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Modèle de test qui utilise le trait
class TestModel extends Model
{
    use HasMediaFiles;

    protected $table = 'test_models';
    protected $fillable = ['name'];

    public function registerMediaCollections(): array
    {
        return [
            'images' => [
                'singleFile' => true,
                'acceptedMimeTypes' => ['image/jpeg', 'image/png'],
            ],
            'gallery' => [
                'singleFile' => false,
                'acceptedMimeTypes' => ['image/*'],
            ],
        ];
    }
}

class HasMediaFilesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer la table de test
        \Illuminate\Support\Facades\Schema::create('test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function test_has_media_attachments_relation(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $attachment = MediaAttachmentFactory::new()->forModel($model)->create();

        $this->assertTrue($model->mediaAttachments->contains($attachment));
    }

    public function test_has_media_files_relation(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $mediaFile = MediaFileFactory::new()->create();
        $attachment = MediaAttachmentFactory::new()
            ->forModel($model)
            ->create(['media_file_id' => $mediaFile->id]);

        $this->assertTrue($model->mediaFiles->contains($mediaFile));
    }

    public function test_add_media_file_creates_attachment(): void
    {
        Storage::fake('public');
        
        $model = TestModel::create(['name' => 'Test']);
        $file = $this->createTestImage('test.jpg');

        $attachment = $model->addMediaFile($file, 'gallery');

        $this->assertInstanceOf(MediaAttachment::class, $attachment);
        $this->assertEquals('gallery', $attachment->collection_name);
        $this->assertEquals($model->id, $attachment->model_id);
    }

    public function test_add_media_file_clears_collection_if_single_file(): void
    {
        Storage::fake('public');
        
        $model = TestModel::create(['name' => 'Test']);
        $file1 = $this->createTestImage('test1.jpg');
        $file2 = $this->createTestImage('test2.jpg');

        $attachment1 = $model->addMediaFile($file1, 'images');
        $attachment2 = $model->addMediaFile($file2, 'images');

        // Avec SoftDeletes, on vérifie que le premier attachment est soft deleted
        $this->assertSoftDeleted('media_attachments', ['id' => $attachment1->id]);
        $this->assertDatabaseHas('media_attachments', ['id' => $attachment2->id, 'deleted_at' => null]);
        $this->assertTrue($attachment2->is_primary);
    }

    public function test_attach_media_file_creates_attachment(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $mediaFile = MediaFileFactory::new()->create();

        $attachment = $model->attachMediaFile($mediaFile, 'gallery');

        $this->assertInstanceOf(MediaAttachment::class, $attachment);
        $this->assertEquals($mediaFile->id, $attachment->media_file_id);
        $this->assertEquals('gallery', $attachment->collection_name);
    }

    public function test_attach_media_file_sets_order_correctly(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $file1 = MediaFileFactory::new()->create();
        $file2 = MediaFileFactory::new()->create();
        $file3 = MediaFileFactory::new()->create();

        $attachment1 = $model->attachMediaFile($file1, 'gallery');
        $attachment2 = $model->attachMediaFile($file2, 'gallery');
        $attachment3 = $model->attachMediaFile($file3, 'gallery');

        $this->assertEquals(0, $attachment1->order);
        $this->assertEquals(1, $attachment2->order);
        $this->assertEquals(2, $attachment3->order);
    }

    public function test_get_media_files_returns_collection_attachments(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $attachment1 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->withOrder(0)
            ->create();
        $attachment2 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->withOrder(1)
            ->create();
        $attachment3 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('images')
            ->create();

        $galleryFiles = $model->getMediaFiles('gallery');

        $this->assertCount(2, $galleryFiles);
        $this->assertTrue($galleryFiles->contains($attachment1));
        $this->assertTrue($galleryFiles->contains($attachment2));
        $this->assertFalse($galleryFiles->contains($attachment3));
    }

    public function test_get_media_files_returns_all_if_no_collection(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $attachment1 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->create();
        $attachment2 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('images')
            ->create();

        $allFiles = $model->getMediaFiles();

        $this->assertCount(2, $allFiles);
    }

    public function test_get_first_media_file_returns_first_attachment(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $attachment1 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->withOrder(0)
            ->create();
        $attachment2 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->withOrder(1)
            ->create();

        $first = $model->getFirstMediaFile('gallery');

        $this->assertEquals($attachment1->id, $first->id);
    }

    public function test_get_first_media_file_returns_null_if_empty(): void
    {
        $model = TestModel::create(['name' => 'Test']);

        $this->assertNull($model->getFirstMediaFile('gallery'));
    }

    public function test_get_media_file_returns_attachment_by_index(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $attachment1 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->withOrder(0)
            ->create();
        $attachment2 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->withOrder(1)
            ->create();

        $file = $model->getMediaFile('gallery', 1);

        $this->assertEquals($attachment2->id, $file->id);
    }

    public function test_has_media_file_returns_true_if_exists(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->create();

        $this->assertTrue($model->hasMediaFile('gallery'));
    }

    public function test_has_media_file_returns_false_if_empty(): void
    {
        $model = TestModel::create(['name' => 'Test']);

        $this->assertFalse($model->hasMediaFile('gallery'));
    }

    public function test_get_media_file_count_returns_correct_count(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->create();
        MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->create();

        $this->assertEquals(2, $model->getMediaFileCount('gallery'));
    }

    public function test_clear_media_collection_removes_attachments(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $attachment1 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->create();
        $attachment2 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('images')
            ->create();

        $model->clearMediaCollection('gallery');

        // Avec SoftDeletes, on vérifie que l'attachment est soft deleted
        $this->assertSoftDeleted('media_attachments', ['id' => $attachment1->id]);
        $this->assertDatabaseHas('media_attachments', ['id' => $attachment2->id, 'deleted_at' => null]);
    }

    public function test_delete_media_file_removes_attachment(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $attachment = MediaAttachmentFactory::new()
            ->forModel($model)
            ->create();

        $result = $model->deleteMediaFile($attachment->id);

        $this->assertTrue($result);
        // Avec SoftDeletes, on vérifie que l'attachment est soft deleted
        $this->assertSoftDeleted('media_attachments', ['id' => $attachment->id]);
    }

    public function test_delete_media_file_returns_false_if_not_found(): void
    {
        $model = TestModel::create(['name' => 'Test']);

        $result = $model->deleteMediaFile(999);

        $this->assertFalse($result);
    }

    public function test_clear_all_media_collections_removes_all_attachments(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $attachment1 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->create();
        $attachment2 = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('images')
            ->create();

        $model->clearAllMediaCollections();

        // Avec SoftDeletes, on vérifie que les attachments sont soft deleted
        $this->assertSoftDeleted('media_attachments', ['id' => $attachment1->id]);
        $this->assertSoftDeleted('media_attachments', ['id' => $attachment2->id]);
    }

    public function test_get_registered_media_collections_returns_collections(): void
    {
        $model = new TestModel();

        // Utiliser la réflexion pour accéder à la méthode protected
        $reflection = new \ReflectionClass($model);
        $method = $reflection->getMethod('getRegisteredMediaCollections');
        $method->setAccessible(true);
        $collections = $method->invoke($model);

        $this->assertArrayHasKey('images', $collections);
        $this->assertArrayHasKey('gallery', $collections);
        $this->assertTrue($collections['images']['singleFile']);
        $this->assertFalse($collections['gallery']['singleFile']);
    }

    public function test_get_first_media_alias_works(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $attachment = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->create();

        $first = $model->getFirstMedia('gallery');

        $this->assertEquals($attachment->id, $first->id);
    }

    public function test_get_media_alias_works(): void
    {
        $model = TestModel::create(['name' => 'Test']);
        $attachment = MediaAttachmentFactory::new()
            ->forModel($model)
            ->collection('gallery')
            ->create();

        $files = $model->getMedia('gallery');

        $this->assertCount(1, $files);
        $this->assertTrue($files->contains($attachment));
    }
}

