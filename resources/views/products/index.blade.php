<x-layouts.app
    title="Catalog — Episteme"
    description="Browse all courses, workshops, webinars, bootcamps, and mentoring on Episteme. Search, filter by category, type, and level."
>
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <x-ui.section-heading eyebrow="Catalog" as="h1">
            <x-slot:lead>Discover courses, workshops, webinars, bootcamps, and mentoring designed to build real capability.</x-slot:lead>
            Explore all products
        </x-ui.section-heading>

        @include('products._filters', [
            'action' => route('products.index'),
            'filters' => $filters,
            'sort' => $sort,
            'categories' => $categories,
            'showCategoryFilter' => true,
        ])

        @include('products._grid', [
            'products' => $products,
            'clearUrl' => route('products.index'),
        ])
    </section>
</x-layouts.app>
