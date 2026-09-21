<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Instructor;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredProducts = Product::query()
            ->published()
            ->featured()
            ->with(['category', 'instructor'])
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        $categories = Category::query()
            ->active()
            ->withCount(['products' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get();

        $instructors = Instructor::query()
            ->withCount(['products' => fn ($query) => $query->published()])
            ->having('products_count', '>', 0)
            ->orderByDesc('products_count')
            ->take(3)
            ->get();

        $upcomingEvents = Product::query()
            ->published()
            ->whereIn('product_type', [ProductType::Webinar, ProductType::Workshop])
            ->with(['instructor', 'webinar', 'workshop'])
            ->get()
            ->map(fn (Product $product) => [
                'product' => $product,
                'event_date' => $product->type_details?->event_date,
            ])
            ->filter(fn (array $row) => $row['event_date'] && $row['event_date']->isFuture())
            ->sortBy(fn (array $row) => $row['event_date'])
            ->take(3)
            ->values();

        return view('home', compact('featuredProducts', 'categories', 'instructors', 'upcomingEvents'));
    }
}
