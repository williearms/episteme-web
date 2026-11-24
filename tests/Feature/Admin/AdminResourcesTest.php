<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Filament\Resources\CategoryResource\Pages\ManageCategories;
use App\Filament\Resources\CourseModuleResource\Pages\EditCourseModule;
use App\Filament\Resources\CourseModuleResource\RelationManagers\LessonsRelationManager;
use App\Filament\Resources\EnrollmentResource\Pages\ManageEnrollments;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Widgets\StatsOverview;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Actions\CreateAction as TableCreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminResourcesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function baseProductData(array $overrides = []): array
    {
        return array_merge([
            'category_id' => Category::factory()->create()->id,
            'title' => 'Admin Made Product',
            'slug' => 'admin-made-product',
            'short_description' => 'Short text',
            'description' => 'Long text',
            'price' => 150000,
            'status' => ProductStatus::Draft->value,
        ], $overrides);
    }

    public function test_the_product_list_shows_products_with_their_status(): void
    {
        $published = Product::factory()->create(['title' => 'Listed Course']);
        $draft = Product::factory()->draft()->create();

        Livewire::test(ListProducts::class)
            ->assertCanSeeTableRecords([$published, $draft])
            ->filterTable('status', ProductStatus::Draft->value)
            ->assertCanSeeTableRecords([$draft])
            ->assertCanNotSeeTableRecords([$published]);
    }

    public function test_an_admin_can_create_a_course_with_its_subtype_details(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm($this->baseProductData([
                'product_type' => ProductType::Course->value,
                'course' => ['duration_minutes' => 90, 'total_modules' => 3, 'total_lessons' => 9, 'delivery_type' => 'self_paced'],
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('slug', 'admin-made-product')->firstOrFail();
        $this->assertDatabaseHas('courses', ['product_id' => $product->id, 'duration_minutes' => 90, 'total_lessons' => 9]);
        $this->assertDatabaseCount('webinars', 0);
        $this->assertDatabaseCount('digital_products', 0);
    }

    public function test_a_webinar_gets_member_access_details_and_no_stray_course_row(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm($this->baseProductData([
                'product_type' => ProductType::Webinar->value,
                'webinar' => ['timezone' => 'WIB', 'platform' => 'Zoom'],
                'access' => ['join_url' => 'https://meet.example.com/room', 'instructions' => 'Join early'],
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('slug', 'admin-made-product')->firstOrFail();
        $this->assertDatabaseHas('webinars', ['product_id' => $product->id, 'platform' => 'Zoom']);
        $this->assertDatabaseHas('product_accesses', ['product_id' => $product->id, 'join_url' => 'https://meet.example.com/room']);
        $this->assertDatabaseCount('courses', 0);
    }

    public function test_publishing_stamps_published_at_once(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm($this->baseProductData([
                'product_type' => ProductType::DigitalProduct->value,
                'status' => ProductStatus::Published->value,
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertNotNull(Product::where('slug', 'admin-made-product')->firstOrFail()->published_at);
    }

    public function test_the_slug_must_be_unique(): void
    {
        Product::factory()->create(['slug' => 'taken-slug']);

        Livewire::test(CreateProduct::class)
            ->fillForm($this->baseProductData(['slug' => 'taken-slug', 'product_type' => ProductType::Course->value]))
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_the_product_type_is_locked_once_created(): void
    {
        $product = Product::factory()->create();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->assertFormFieldIsDisabled('product_type');
    }

    public function test_a_product_that_has_been_bought_cannot_be_deleted(): void
    {
        $bought = Product::factory()->create();
        $unused = Product::factory()->create();
        Enrollment::create(['user_id' => User::factory()->create()->id, 'product_id' => $bought->id, 'enrolled_at' => now()]);

        Livewire::test(ListProducts::class)
            ->assertTableActionHidden('delete', $bought)
            ->assertTableActionVisible('delete', $unused);
    }

    public function test_a_category_in_use_cannot_be_deleted_but_an_empty_one_can(): void
    {
        $inUse = Category::factory()->create();
        Product::factory()->create(['category_id' => $inUse->id]);
        $empty = Category::factory()->create();

        Livewire::test(ManageCategories::class)
            ->assertTableActionHidden('delete', $inUse)
            ->assertTableActionVisible('delete', $empty);
    }

    public function test_orders_are_read_only_in_the_panel(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $this->assertFalse(OrderResource::canCreate());
        $this->assertFalse(OrderResource::canEdit($order));
        $this->assertFalse(OrderResource::canDelete($order));
        $this->get('/admin/orders/create')->assertNotFound();
        $this->get('/admin/orders/'.$order->id.'/edit')->assertNotFound();
    }

    public function test_an_admin_can_grant_access_manually(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Livewire::test(ManageEnrollments::class)
            ->callAction('create', ['user_id' => $user->id, 'product_id' => $product->id, 'enrolled_at' => now()])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('enrollments', ['user_id' => $user->id, 'product_id' => $product->id, 'order_id' => null]);
    }

    public function test_granting_access_twice_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Enrollment::create(['user_id' => $user->id, 'product_id' => $product->id, 'enrolled_at' => now()]);

        Livewire::test(ManageEnrollments::class)
            ->callAction('create', ['user_id' => $user->id, 'product_id' => $product->id, 'enrolled_at' => now()])
            ->assertHasActionErrors(['product_id']);

        $this->assertDatabaseCount('enrollments', 1);
    }

    public function test_an_admin_can_revoke_access(): void
    {
        $enrollment = Enrollment::create(['user_id' => User::factory()->create()->id, 'product_id' => Product::factory()->create()->id, 'enrolled_at' => now()]);

        Livewire::test(ManageEnrollments::class)->callTableAction('delete', $enrollment);

        $this->assertModelMissing($enrollment);
    }

    public function test_an_admin_can_promote_and_demote_another_user(): void
    {
        $user = User::factory()->create();

        Livewire::test(ListUsers::class)->callTableAction('makeAdmin', $user);
        $this->assertTrue($user->fresh()->is_admin);

        // fresh(): the action's visibility check reads is_admin off the record we pass in.
        Livewire::test(ListUsers::class)->callTableAction('removeAdmin', $user->fresh());
        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_an_admin_cannot_demote_themselves(): void
    {
        Livewire::test(ListUsers::class)->assertTableActionHidden('removeAdmin', $this->admin);
    }

    public function test_users_cannot_be_created_edited_or_deleted_from_the_panel(): void
    {
        $user = User::factory()->create();

        $this->get('/admin/users/create')->assertNotFound();
        $this->get('/admin/users/'.$user->id.'/edit')->assertNotFound();
    }

    public function test_an_admin_can_add_a_lesson_to_a_module(): void
    {
        $product = Product::factory()->create(['product_type' => ProductType::Course]);
        $course = Course::factory()->create(['product_id' => $product->id]);
        $module = CourseModule::create(['course_id' => $course->id, 'title' => 'Modul A', 'position' => 1]);

        Livewire::test(LessonsRelationManager::class, ['ownerRecord' => $module, 'pageClass' => EditCourseModule::class])
            ->callTableAction(TableCreateAction::class, data: ['title' => 'Lesson Baru', 'position' => 1, 'content' => 'Isi'])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('lessons', ['course_module_id' => $module->id, 'title' => 'Lesson Baru']);
    }

    public function test_the_dashboard_totals_only_count_paid_revenue(): void
    {
        Order::factory()->create(['status' => OrderStatus::Paid, 'subtotal' => 200000, 'total' => 200000]);
        Order::factory()->create(['status' => OrderStatus::Pending, 'subtotal' => 999000, 'total' => 999000]);

        Livewire::test(StatsOverview::class)
            ->assertSee('Rp200.000')
            ->assertDontSee('999.000');
    }
}
