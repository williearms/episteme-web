@php
    $hasActiveFilters = collect($filters)->filter()->isNotEmpty() || ($sort && $sort !== 'featured');
@endphp

<form method="GET" action="{{ $action }}" role="search" aria-label="Search and filter products" class="bg-surface border border-border/40 rounded-card p-4 sm:p-5 mb-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ $showCategoryFilter ? 6 : 5 }} gap-3 items-end">
        <div class="lg:col-span-2">
            <label for="filter-search" class="block text-label-sm font-semibold text-outline uppercase tracking-wider mb-1.5">Search</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px] pointer-events-none" aria-hidden="true">search</span>
                <input
                    type="search"
                    id="filter-search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search title, description, instructor..."
                    class="w-full bg-surface border border-border/40 rounded-md pl-9 pr-3 py-2 text-body-sm text-text placeholder:text-outline focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent"
                >
            </div>
        </div>

        @if ($showCategoryFilter)
            <div>
                <label for="filter-category" class="block text-label-sm font-semibold text-outline uppercase tracking-wider mb-1.5">Category</label>
                <select id="filter-category" name="category" class="w-full bg-surface border border-border/40 rounded-md px-3 py-2 text-body-sm text-text focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(($filters['category'] ?? null) === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label for="filter-type" class="block text-label-sm font-semibold text-outline uppercase tracking-wider mb-1.5">Type</label>
            <select id="filter-type" name="type" class="w-full bg-surface border border-border/40 rounded-md px-3 py-2 text-body-sm text-text focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent">
                <option value="">All Types</option>
                @foreach (\App\Enums\ProductType::options() as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['type'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter-level" class="block text-label-sm font-semibold text-outline uppercase tracking-wider mb-1.5">Level</label>
            <select id="filter-level" name="level" class="w-full bg-surface border border-border/40 rounded-md px-3 py-2 text-body-sm text-text focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent">
                <option value="">All Levels</option>
                @foreach (\App\Models\Product::LEVELS as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['level'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filter-price" class="block text-label-sm font-semibold text-outline uppercase tracking-wider mb-1.5">Price</label>
            <select id="filter-price" name="price" class="w-full bg-surface border border-border/40 rounded-md px-3 py-2 text-body-sm text-text focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent">
                <option value="">Any Price</option>
                <option value="free" @selected(($filters['price'] ?? null) === 'free')>Free</option>
                <option value="paid" @selected(($filters['price'] ?? null) === 'paid')>Paid</option>
            </select>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 mt-4 pt-4 border-t border-border/40">
        <div>
            <label for="filter-sort" class="sr-only">Sort by</label>
            <select id="filter-sort" name="sort" class="bg-surface border border-border/40 rounded-md px-3 py-2 text-body-sm text-text focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent">
                <option value="featured" @selected($sort === 'featured')>Sort: Featured</option>
                <option value="newest" @selected($sort === 'newest')>Sort: Newest</option>
                <option value="price-low" @selected($sort === 'price-low')>Sort: Price (Low to High)</option>
                <option value="price-high" @selected($sort === 'price-high')>Sort: Price (High to Low)</option>
            </select>
        </div>

        <div class="flex items-center gap-4">
            @if ($hasActiveFilters)
                <a href="{{ $action }}" class="text-label-md font-medium text-text-muted hover:text-primary transition-colors">Clear filters</a>
            @endif
            <x-ui.button type="submit" size="sm">Apply</x-ui.button>
        </div>
    </div>
</form>
