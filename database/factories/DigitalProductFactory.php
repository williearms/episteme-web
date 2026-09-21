<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DigitalProduct>
 */
class DigitalProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'file_type' => fake()->randomElement(['PDF', 'ZIP', 'Template', 'Dataset', 'E-book']),
            'file_size' => fake()->numberBetween(1, 50).' MB',
            'access_type' => 'Lifetime download',
        ];
    }
}
