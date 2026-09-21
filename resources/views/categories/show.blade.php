<x-layouts.app
    :title="$category->name.' — Episteme'"
    :description="$category->description ?? ('Explore '.$category->name.' courses, workshops, and mentoring on Episteme.')"
>
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <nav aria-label="Breadcrumb" class="text-label-sm text-outline mb-6">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-primary transition-colors">Home</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('products.index') }}" class="hover:text-primary transition-colors">Catalog</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-text font-medium" aria-current="page">{{ $category->name }}</li>
            </ol>
        </nav>

        <div class="mb-8 max-w-2xl">
            <span class="font-mono text-code-sm text-primary uppercase tracking-wider font-semibold">Category</span>
            <h1 class="text-headline-xl-mobile md:text-headline-xl text-text tracking-tight mt-1">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="text-body-lead text-text-muted mt-2">{{ $category->description }}</p>
            @endif
            <p class="text-body-sm text-outline mt-3">{{ $products->total() }} {{ Str::plural('product', $products->total()) }} in this category</p>
        </div>

        @include('products._filters', [
            'action' => route('categories.show', $category),
            'filters' => $filters,
            'sort' => $sort,
            'categories' => $categories,
            'showCategoryFilter' => false,
        ])

        @include('products._grid', [
            'products' => $products,
            'clearUrl' => route('categories.show', $category),
        ])
    </section>
</x-layouts.app>
