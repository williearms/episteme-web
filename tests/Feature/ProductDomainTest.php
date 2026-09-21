<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Bootcamp;
use App\Models\Category;
use App\Models\Course;
use App\Models\DigitalProduct;
use App\Models\Instructor;
use App\Models\Mentoring;
use App\Models\Product;
use App\Models\Webinar;
use App\Models\Workshop;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_product_can_be_created_with_valid_attributes(): void
    {
        $product = Product::factory()->create([
            'title' => 'Test Product',
            'product_type' => ProductType::Course,
            'price' => 150000,
        ]);

        $this->assertDatabaseHas('products', ['title' => 'Test Product']);
        $this->assertSame(150000, $product->fresh()->price);
        $this->assertSame('IDR', $product->fresh()->currency);
    }

    public function test_product_slug_must_be_unique(): void
    {
        Product::factory()->create(['slug' => 'duplicate-slug']);

        $this->expectException(QueryException::class);

        Product::factory()->create(['slug' => 'duplicate-slug']);
    }

    public function test_product_type_is_cast_to_a_backed_enum(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::Webinar]);

        $this->assertInstanceOf(ProductType::class, $product->fresh()->product_type);
        $this->assertSame(ProductType::Webinar, $product->fresh()->product_type);
    }

    public function test_price_is_stored_and_retrieved_as_an_integer(): void
    {
        $product = Product::factory()->create(['price' => 299000]);

        $this->assertIsInt($product->fresh()->price);
        $this->assertSame(299000, $product->fresh()->price);
    }

    public function test_product_belongs_to_a_category_and_an_instructor(): void
    {
        $category = Category::factory()->create();
        $instructor = Instructor::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'instructor_id' => $instructor->id]);

        $this->assertTrue($product->category->is($category));
        $this->assertTrue($product->instructor->is($instructor));
        $this->assertTrue($category->products->contains($product));
        $this->assertTrue($instructor->products->contains($product));
    }

    public function test_published_products_are_publicly_visible(): void
    {
        Product::factory()->create(['title' => 'A Published Product', 'status' => ProductStatus::Published]);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('A Published Product');
    }

    public function test_draft_products_are_not_publicly_visible(): void
    {
        Product::factory()->draft()->create(['title' => 'A Draft Product', 'slug' => 'a-draft-product']);

        $this->get(route('products.index'))->assertDontSee('A Draft Product');
        $this->get('/products/a-draft-product')->assertNotFound();
    }

    public function test_archived_products_are_not_publicly_visible(): void
    {
        Product::factory()->archived()->create(['title' => 'An Archived Product', 'slug' => 'an-archived-product']);

        $this->get(route('products.index'))->assertDontSee('An Archived Product');
        $this->get('/products/an-archived-product')->assertNotFound();
    }

    public function test_a_course_product_has_its_course_subtype_attached(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::Course]);
        Course::factory()->create(['product_id' => $product->id, 'total_lessons' => 20]);

        $this->assertInstanceOf(Course::class, $product->fresh()->course);
        $this->assertSame(20, $product->fresh()->course->total_lessons);
        $this->assertInstanceOf(Course::class, $product->fresh()->type_details);
    }

    public function test_a_webinar_product_has_its_webinar_subtype_attached(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::Webinar]);
        Webinar::factory()->create(['product_id' => $product->id, 'platform' => 'Zoom']);

        $this->assertInstanceOf(Webinar::class, $product->fresh()->webinar);
        $this->assertSame('Zoom', $product->fresh()->webinar->platform);
    }

    public function test_a_workshop_product_has_its_workshop_subtype_attached(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::Workshop]);
        Workshop::factory()->create(['product_id' => $product->id, 'mode' => 'offline']);

        $this->assertInstanceOf(Workshop::class, $product->fresh()->workshop);
        $this->assertSame('offline', $product->fresh()->workshop->mode);
    }

    public function test_a_bootcamp_product_has_its_bootcamp_subtype_attached(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::Bootcamp]);
        Bootcamp::factory()->create(['product_id' => $product->id, 'cohort' => 'Batch 9']);

        $this->assertInstanceOf(Bootcamp::class, $product->fresh()->bootcamp);
        $this->assertSame('Batch 9', $product->fresh()->bootcamp->cohort);
    }

    public function test_a_mentoring_product_has_its_mentoring_subtype_attached(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::Mentoring]);
        Mentoring::factory()->create(['product_id' => $product->id, 'number_of_sessions' => 3]);

        $this->assertInstanceOf(Mentoring::class, $product->fresh()->mentoring);
        $this->assertSame(3, $product->fresh()->mentoring->number_of_sessions);
    }

    public function test_a_digital_product_has_its_digital_product_subtype_attached(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::DigitalProduct]);
        DigitalProduct::factory()->create(['product_id' => $product->id, 'file_type' => 'PDF']);

        $this->assertInstanceOf(DigitalProduct::class, $product->fresh()->digitalProduct);
        $this->assertSame('PDF', $product->fresh()->digitalProduct->file_type);
    }

    public function test_a_subtype_row_cannot_exist_without_its_product(): void
    {
        $this->expectException(QueryException::class);

        Course::factory()->create(['product_id' => 999999]);
    }

    public function test_a_subtype_row_is_deleted_when_its_product_is_deleted(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::Course]);
        $course = Course::factory()->create(['product_id' => $product->id]);

        $product->delete();

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_the_product_detail_page_shows_type_specific_metadata_for_a_course(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::Course, 'slug' => 'course-with-details']);
        Course::factory()->create(['product_id' => $product->id, 'total_modules' => 9, 'total_lessons' => 42]);

        $response = $this->get('/products/course-with-details');

        $response->assertOk();
        $response->assertSee('9');
        $response->assertSee('42');
        $response->assertSee('Modules');
        $response->assertSee('Lessons');
    }

    public function test_the_product_detail_page_shows_type_specific_metadata_for_a_webinar(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::Webinar, 'slug' => 'webinar-with-details']);
        Webinar::factory()->create(['product_id' => $product->id, 'platform' => 'Google Meet']);

        $response = $this->get('/products/webinar-with-details');

        $response->assertOk();
        $response->assertSee('Google Meet');
    }
}
