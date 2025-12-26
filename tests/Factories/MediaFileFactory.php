<?php

namespace Xavier\MediaLibraryPro\Tests\Factories;

use Xavier\MediaLibraryPro\Models\MediaFile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Xavier\MediaLibraryPro\Models\MediaFile>
 */
class MediaFileFactory extends Factory
{
    protected $model = MediaFile::class;

    public function definition(): array
    {
        $extension = $this->faker->randomElement(['jpg', 'png', 'webp', 'pdf', 'mp4', 'mp3']);
        $fileName = $this->faker->word() . '.' . $extension;
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
        ];

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        $isImage = str_starts_with($mimeType, 'image/');

        return [
            'uuid' => (string) Str::uuid(),
            'file_name' => $fileName,
            'stored_name' => Str::random(40) . '.' . $extension,
            'disk' => 'public',
            'path' => 'media/' . date('Y/m') . '/' . Str::random(40) . '.' . $extension,
            'mime_type' => $mimeType,
            'size' => $this->faker->numberBetween(1000, 10000000),
            'width' => $isImage ? $this->faker->numberBetween(100, 2000) : null,
            'height' => $isImage ? $this->faker->numberBetween(100, 2000) : null,
            'duration' => str_starts_with($mimeType, 'video/') || str_starts_with($mimeType, 'audio/') 
                ? $this->faker->numberBetween(10, 3600) 
                : null,
            'metadata' => null,
            'alt_text' => $this->faker->optional()->sentence(),
            'description' => $this->faker->optional()->paragraph(),
            'is_public' => true,
        ];
    }

    public function image(): static
    {
        return $this->state(function (array $attributes) {
            $extension = $this->faker->randomElement(['jpg', 'png', 'webp']);
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
            ];
            
            return [
                'file_name' => $this->faker->word() . '.' . $extension,
                'stored_name' => Str::random(40) . '.' . $extension,
                'mime_type' => $mimeTypes[$extension],
                'width' => $this->faker->numberBetween(100, 2000),
                'height' => $this->faker->numberBetween(100, 2000),
                'duration' => null,
            ];
        });
    }

    public function video(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'file_name' => $this->faker->word() . '.mp4',
                'stored_name' => Str::random(40) . '.mp4',
                'mime_type' => 'video/mp4',
                'width' => $this->faker->numberBetween(640, 1920),
                'height' => $this->faker->numberBetween(480, 1080),
                'duration' => $this->faker->numberBetween(10, 3600),
            ];
        });
    }

    public function document(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'file_name' => $this->faker->word() . '.pdf',
                'stored_name' => Str::random(40) . '.pdf',
                'mime_type' => 'application/pdf',
                'width' => null,
                'height' => null,
                'duration' => null,
            ];
        });
    }

    public function private(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_public' => false,
            ];
        });
    }
}





