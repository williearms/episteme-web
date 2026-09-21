<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_shows_only_products_from_that_category(): void
    {
        $dataScience = Category::factory()->create(['name' => 'Data Science', 'slug' => 'data-science']);
        $webDev = Category::factory()->create(['name' => 'Web Development', 'slug' => 'web-development']);

        Product::factory()->create(['title' => 'Data Science 101', 'category_id' => $dataScience->id]);
        Product::factory()->create(['title' => 'Web Dev Basics', 'category_id' => $webDev->id]);

        $response = $this->get(route('categories.show', $dataScience));

        $response->assertOk();
        $response->assertSee('Data Science 101');
        $response->assertDontSee('Web Dev Basics');
    }

    public function test_category_page_hides_unpublished_products(): void
    {
        $category = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);

        Product::factory()->draft()->create(['title' => 'Draft In Category', 'category_id' => $category->id]);

        $response = $this->get(route('categories.show', $category));

        $response->assertOk();
        $response->assertDontSee('Draft In Category');
    }

    public function test_an_empty_category_shows_a_useful_empty_state(): void
    {
        $emptyCategory = Category::factory()->create(['name' => 'Empty Category', 'slug' => 'empty-category']);

        $response = $this->get(route('categories.show', $emptyCategory));

        $response->assertOk();
        $response->assertSee('No products match your search');
    }

    public function test_an_invalid_category_slug_returns_a_404(): void
    {
        $response = $this->get('/categories/does-not-exist');

        $response->assertNotFound();
    }
}
