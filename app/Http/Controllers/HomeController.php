<?php

namespace App\Http\Controllers;

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
            ->whereIn('product_type', ['webinar', 'workshop'])
            ->whereNotNull('meta->event_date')
            ->with('instructor')
            ->get()
            ->filter(fn (Product $product) => ($product->meta['event_date'] ?? null) >= now()->toDateString())
            ->sortBy(fn (Product $product) => $product->meta['event_date'])
            ->take(3)
            ->values();

        return view('home', compact('featuredProducts', 'categories', 'instructors', 'upcomingEvents'));
    }
}
