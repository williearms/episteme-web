<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Data Science', 'Artificial Intelligence', 'Web Development', 'Programming',
            'Data Analytics', 'Digital Literacy', 'Research Methodology', 'Cloud Computing',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(12),
            'icon' => fake()->randomElement(['analytics', 'psychology', 'code', 'layers', 'science', 'public']),
            'status' => 'active',
        ];
    }
}
