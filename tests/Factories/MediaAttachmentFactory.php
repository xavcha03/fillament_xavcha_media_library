<?php

namespace Xavier\MediaLibraryPro\Tests\Factories;

use Xavier\MediaLibraryPro\Models\MediaAttachment;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Xavier\MediaLibraryPro\Models\MediaAttachment>
 */
class MediaAttachmentFactory extends Factory
{
    protected $model = MediaAttachment::class;

    public function definition(): array
    {
        return [
            'media_file_id' => MediaFileFactory::new(),
            'model_type' => 'App\\Models\\TestModel',
            'model_id' => $this->faker->numberBetween(1, 1000),
            'collection_name' => 'default',
            'order' => 0,
            'custom_properties' => [],
            'is_primary' => false,
        ];
    }

    public function forModel($model): static
    {
        return $this->state(function (array $attributes) use ($model) {
            return [
                'model_type' => get_class($model),
                'model_id' => $model->id,
            ];
        });
    }

    public function collection(string $collectionName): static
    {
        return $this->state(function (array $attributes) use ($collectionName) {
            return [
                'collection_name' => $collectionName,
            ];
        });
    }

    public function primary(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_primary' => true,
            ];
        });
    }

    public function withOrder(int $order): static
    {
        return $this->state(function (array $attributes) use ($order) {
            return [
                'order' => $order,
            ];
        });
    }

    public function withCustomProperties(array $properties): static
    {
        return $this->state(function (array $attributes) use ($properties) {
            return [
                'custom_properties' => $properties,
            ];
        });
    }
}





