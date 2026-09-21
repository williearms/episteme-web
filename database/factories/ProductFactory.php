<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);
        $price = fake()->randomElement([0, 99000, 149000, 299000, 499000, 750000, 1200000]);

        return [
            'category_id' => Category::factory(),
            'instructor_id' => Instructor::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'short_description' => fake()->sentence(15),
            'description' => fake()->paragraphs(3, true),
            'product_type' => fake()->randomElement(ProductType::cases()),
            'level' => fake()->randomElement(array_keys(\App\Models\Product::LEVELS)),
            'price' => $price,
            'currency' => 'IDR',
            'thumbnail' => null,
            'status' => ProductStatus::Published,
            'published_at' => now(),
            'featured' => false,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => ProductStatus::Draft, 'published_at' => null]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => ProductStatus::Archived, 'published_at' => null]);
    }
}
