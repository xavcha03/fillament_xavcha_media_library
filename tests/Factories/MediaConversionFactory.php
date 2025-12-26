<?php

namespace Xavier\MediaLibraryPro\Tests\Factories;

use Xavier\MediaLibraryPro\Models\MediaConversion;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Xavier\MediaLibraryPro\Models\MediaConversion>
 */
class MediaConversionFactory extends Factory
{
    protected $model = MediaConversion::class;

    public function definition(): array
    {
        $format = $this->faker->randomElement(['jpg', 'png', 'webp']);
        
        return [
            'media_file_id' => MediaFileFactory::new()->image(),
            'conversion_name' => $this->faker->randomElement(['thumb', 'small', 'medium', 'large']),
            'file_name' => Str::random(20) . '.' . $format,
            'disk' => 'public',
            'path' => 'media/conversions/' . date('Y/m') . '/' . Str::random(20) . '.' . $format,
            'width' => $this->faker->numberBetween(100, 1920),
            'height' => $this->faker->numberBetween(100, 1080),
            'size' => $this->faker->numberBetween(1000, 1000000),
            'quality' => $this->faker->numberBetween(60, 100),
            'format' => $format,
            'generated_at' => now(),
        ];
    }

    public function forMediaFile(MediaFile $mediaFile): static
    {
        return $this->state(function (array $attributes) use ($mediaFile) {
            return [
                'media_file_id' => $mediaFile->id,
            ];
        });
    }

    public function conversion(string $conversionName): static
    {
        return $this->state(function (array $attributes) use ($conversionName) {
            return [
                'conversion_name' => $conversionName,
            ];
        });
    }

    public function thumb(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'conversion_name' => 'thumb',
                'width' => 150,
                'height' => 150,
            ];
        });
    }

    public function small(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'conversion_name' => 'small',
                'width' => 300,
                'height' => null,
            ];
        });
    }

    public function medium(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'conversion_name' => 'medium',
                'width' => 800,
                'height' => null,
            ];
        });
    }
}





