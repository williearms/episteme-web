<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Request $request, Category $category): View
    {
        $filters = $request->only(['search', 'type', 'level', 'price']);
        $sort = $request->get('sort');

        $products = Product::query()
            ->published()
            ->with(['category', 'instructor'])
            ->filter([...$filters, 'category' => $category->slug])
            ->sort($sort)
            ->paginate(12)
            ->withQueryString();

        return view('categories.show', [
            'category' => $category,
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'filters' => $filters,
            'sort' => $sort ?: 'featured',
        ]);
    }
}
