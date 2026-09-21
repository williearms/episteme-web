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
            'categories' => Category::orderBy('name')->get(),
            'filters' => $filters,
            'sort' => $sort ?: 'featured',
        ]);
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'instructor']);

        return view('products.show', compact('product'));
    }
}
