<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_valid_published_slug_displays_the_correct_product(): void
    {
        $product = Product::factory()->create([
            'title' => 'Deep Learning Masterclass',
            'slug' => 'deep-learning-masterclass',
            'short_description' => 'A short description of the masterclass.',
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertOk();
        $response->assertSee('Deep Learning Masterclass');
        $response->assertSee('A short description of the masterclass.');
    }

    public function test_a_nonexistent_slug_returns_a_404(): void
    {
        $response = $this->get('/products/does-not-exist-at-all');

        $response->assertNotFound();
    }

    public function test_an_unpublished_products_slug_returns_a_404(): void
    {
        Product::factory()->draft()->create([
            'title' => 'Secret Draft Product',
            'slug' => 'secret-draft-product',
        ]);

        $response = $this->get('/products/secret-draft-product');

        $response->assertNotFound();
    }

    public function test_the_detail_page_shows_the_instructor_and_price(): void
    {
        $product = Product::factory()->create([
            'title' => 'Advanced Data Engineering',
            'slug' => 'advanced-data-engineering',
            'price' => 350000,
        ]);
        $product->load('instructor');

        $response = $this->get(route('products.show', $product));

        $response->assertOk();
        $response->assertSee($product->instructor->name);
        $response->assertSee('Rp350.000');
    }

    public function test_a_free_product_displays_free_instead_of_a_price(): void
    {
        $product = Product::factory()->create([
            'title' => 'Free Intro Webinar',
            'slug' => 'free-intro-webinar',
            'price' => 0,
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertOk();
        $response->assertSee('Free');
    }
}
