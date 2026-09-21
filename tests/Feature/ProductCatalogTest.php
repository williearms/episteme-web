<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_products_appear_on_the_catalog(): void
    {
        Product::factory()->create(['title' => 'Published Course']);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('Published Course');
    }

    public function test_unpublished_products_do_not_appear_on_the_catalog(): void
    {
        Product::factory()->draft()->create(['title' => 'Hidden Draft Course']);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertDontSee('Hidden Draft Course');
    }

    public function test_search_returns_only_relevant_products(): void
    {
        Product::factory()->create(['title' => 'Belajar Python untuk Data Science']);
        Product::factory()->create(['title' => 'Fundamentals of Web Development']);

        $response = $this->get(route('products.index', ['search' => 'Python']));

        $response->assertOk();
        $response->assertSee('Belajar Python untuk Data Science');
        $response->assertDontSee('Fundamentals of Web Development');
    }

    public function test_search_preserves_other_active_filters_in_pagination_links(): void
    {
        Product::factory()->create(['title' => 'Python Course', 'product_type' => 'course']);

        $response = $this->get(route('products.index', ['search' => 'Python', 'type' => 'course']));

        $response->assertOk();
        $response->assertSee('name="search"', false);
        $response->assertSee('value="Python"', false);
    }

    public function test_category_filter_scopes_results_to_that_category(): void
    {
        $dataScience = Category::factory()->create(['name' => 'Data Science', 'slug' => 'data-science']);
        $webDev = Category::factory()->create(['name' => 'Web Development', 'slug' => 'web-development']);

        Product::factory()->create(['title' => 'Data Science Deep Dive', 'category_id' => $dataScience->id]);
        Product::factory()->create(['title' => 'Web Dev Bootcamp', 'category_id' => $webDev->id]);

        $response = $this->get(route('products.index', ['category' => 'data-science']));

        $response->assertOk();
        $response->assertSee('Data Science Deep Dive');
        $response->assertDontSee('Web Dev Bootcamp');
    }

    public function test_type_filter_scopes_results_to_that_product_type(): void
    {
        Product::factory()->create(['title' => 'A Live Webinar Session', 'product_type' => 'webinar']);
        Product::factory()->create(['title' => 'A Structured Course', 'product_type' => 'course']);

        $response = $this->get(route('products.index', ['type' => 'webinar']));

        $response->assertOk();
        $response->assertSee('A Live Webinar Session');
        $response->assertDontSee('A Structured Course');
    }

    public function test_level_filter_scopes_results_to_that_level(): void
    {
        Product::factory()->create(['title' => 'Beginner Friendly Class', 'level' => 'beginner']);
        Product::factory()->create(['title' => 'Advanced Deep Dive', 'level' => 'advanced']);

        $response = $this->get(route('products.index', ['level' => 'advanced']));

        $response->assertOk();
        $response->assertSee('Advanced Deep Dive');
        $response->assertDontSee('Beginner Friendly Class');
    }

    public function test_price_filter_scopes_results_to_free_products(): void
    {
        Product::factory()->create(['title' => 'Free Intro Class', 'price' => 0]);
        Product::factory()->create(['title' => 'Premium Paid Class', 'price' => 500000]);

        $response = $this->get(route('products.index', ['price' => 'free']));

        $response->assertOk();
        $response->assertSee('Free Intro Class');
        $response->assertDontSee('Premium Paid Class');
    }

    public function test_sorting_by_price_low_to_high_orders_products_ascending(): void
    {
        Product::factory()->create(['title' => 'Expensive Product', 'price' => 900000]);
        Product::factory()->create(['title' => 'Cheap Product', 'price' => 50000]);

        $response = $this->get(route('products.index', ['sort' => 'price-low']));
        $content = $response->getContent();

        $response->assertOk();
        $this->assertLessThan(
            strpos($content, 'Expensive Product'),
            strpos($content, 'Cheap Product')
        );
    }

    public function test_sorting_by_newest_orders_most_recently_created_first(): void
    {
        Product::factory()->create(['title' => 'Older Product', 'created_at' => now()->subDays(5)]);
        Product::factory()->create(['title' => 'Newer Product', 'created_at' => now()]);

        $response = $this->get(route('products.index', ['sort' => 'newest']));
        $content = $response->getContent();

        $response->assertOk();
        $this->assertLessThan(
            strpos($content, 'Older Product'),
            strpos($content, 'Newer Product')
        );
    }

    public function test_empty_search_results_show_an_empty_state_with_a_clear_filters_link(): void
    {
        Product::factory()->create(['title' => 'Some Unrelated Product']);

        $response = $this->get(route('products.index', ['search' => 'zzz-no-match-zzz']));

        $response->assertOk();
        $response->assertSee('No products match your search');
        $response->assertSee('Clear filters');
    }
}
