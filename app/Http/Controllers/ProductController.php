<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'category', 'type', 'level', 'price']);
        $sort = $request->get('sort');

        $products = Product::query()
            ->published()
            ->with(['category', 'instructor'])
            ->filter($filters)
            ->sort($sort)
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::active()->orderBy('name')->get(),
            'filters' => $filters,
            'sort' => $sort ?: 'featured',
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        $product->load(['category', 'instructor', 'course', 'webinar', 'workshop', 'bootcamp', 'mentoring', 'digitalProduct']);

        $user = $request->user();

        return view('products.show', [
            'product' => $product,
            // What the primary button does depends on the viewer: open it, jump to the cart, or add it.
            'owned' => $user?->ownsProduct($product->id) ?? false,
            'inCart' => $user?->cart?->items()->where('product_id', $product->id)->exists() ?? false,
        ]);
    }
}
