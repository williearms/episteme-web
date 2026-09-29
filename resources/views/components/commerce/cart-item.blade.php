@props(['item'])

@php $product = $item->product; @endphp

<div class="flex flex-col sm:flex-row sm:items-center gap-4 bg-surface border border-border/40 rounded-card p-4">
    <a href="{{ $product ? route('products.show', $product) : '#' }}" class="w-full sm:w-28 aspect-video sm:aspect-square rounded-md overflow-hidden bg-surface-subtle border border-border/40 shrink-0">
        <img src="{{ $product?->thumbnail_url }}" alt="" class="w-full h-full object-cover">
    </a>

    <div class="flex-1 min-w-0">
        @if ($item->isPurchasable())
            <a href="{{ route('products.show', $product) }}" class="text-headline-sm text-text hover:text-primary transition-colors line-clamp-1">{{ $product->title }}</a>
            <p class="text-label-sm text-outline mt-1">{{ $product->type_label }}</p>
        @else
            <span class="text-headline-sm text-text line-clamp-1">{{ $product?->title ?? 'Product no longer available' }}</span>
            <p class="text-label-sm text-error mt-1">No longer available for purchase — please remove it from your cart.</p>
        @endif
    </div>

    <div class="flex items-center gap-4 sm:gap-6">
        <form method="POST" action="{{ route('cart.items.update', $item->product_id) }}" class="flex items-center gap-2">
            @csrf
            @method('PATCH')
            <label for="quantity-{{ $item->product_id }}" class="sr-only">Quantity</label>
            <input
                id="quantity-{{ $item->product_id }}"
                type="number"
                name="quantity"
                min="1"
                value="{{ $item->quantity }}"
                class="w-16 border border-border/60 rounded-md px-2 py-1.5 text-body-sm text-text text-center"
            >
            <button type="submit" class="text-label-sm font-medium text-primary hover:text-primary-hover transition-colors">Update</button>
        </form>

        <span class="font-mono text-body-sm font-bold text-text w-24 text-right">{{ $item->subtotal_label }}</span>

        <form method="POST" action="{{ route('cart.items.destroy', $item->product_id) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-outline hover:text-error transition-colors" aria-label="Remove from cart">
                <span class="material-symbols-outlined text-[20px]" aria-hidden="true">delete</span>
            </button>
        </form>
    </div>
</div>
