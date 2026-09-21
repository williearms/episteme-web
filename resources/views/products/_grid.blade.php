<div class="mb-6">
    <p class="text-body-sm text-text-muted" role="status">
        @if ($products->total() === 0)
            No products found.
        @else
            Showing {{ $products->firstItem() }}&ndash;{{ $products->lastItem() }} of {{ $products->total() }} {{ Str::plural('product', $products->total()) }}
        @endif
    </p>
</div>

@if ($products->isEmpty())
    <div class="text-center py-20 border border-dashed border-border/60 rounded-card">
        <span class="material-symbols-outlined text-4xl text-outline" aria-hidden="true">search_off</span>
        <h2 class="text-headline-sm text-text mt-4">No products match your search</h2>
        <p class="text-body-sm text-text-muted mt-2 max-w-md mx-auto">
            Try a different keyword or clear your filters to see everything Episteme has to offer.
        </p>
        <a href="{{ $clearUrl }}" class="inline-block mt-6 text-label-md font-semibold text-primary hover:underline">
            Clear filters &rarr;
        </a>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($products as $product)
            <x-product.card :product="$product" />
        @endforeach
    </div>

    <nav class="mt-10" aria-label="Pagination">
        {{ $products->links() }}
    </nav>
@endif
