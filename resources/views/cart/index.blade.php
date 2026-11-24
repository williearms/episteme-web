<x-layouts.app title="Your Cart — Episteme">
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <h1 class="text-headline-xl-mobile lg:text-headline-xl text-text tracking-tight mb-8">Your Cart</h1>

        @if (session('status'))
            <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="error" class="mb-6">{{ session('error') }}</x-ui.alert>
        @endif

        @if ($cart->items->isEmpty())
            <div class="text-center py-20 border border-dashed border-border/60 rounded-card">
                <span class="material-symbols-outlined text-[40px] text-outline" aria-hidden="true">shopping_cart</span>
                <p class="text-body-lead text-text mt-4">Your cart is empty.</p>
                <p class="text-body-sm text-text-muted mt-2">Browse the catalog to find your next course, workshop, or mentoring session.</p>
                <x-ui.button href="{{ route('products.index') }}" class="mt-6">Explore Catalog</x-ui.button>
            </div>
        @else
            @php $purchasable = $cart->items->filter(fn ($item) => $item->isPurchasable() && ! $ownedProductIds->contains($item->product_id)); @endphp

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-8 flex flex-col gap-4">
                    @foreach ($cart->items as $item)
                        <x-commerce.cart-item :item="$item" :owned="$ownedProductIds->contains($item->product_id)" />
                    @endforeach
                </div>

                <div class="lg:col-span-4">
                    <div class="bg-surface border border-border/40 rounded-card p-5 sticky top-24">
                        <h2 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-4">Summary</h2>
                        <div class="flex items-center justify-between pb-4 border-b border-border/40">
                            <span class="text-text-muted text-body-sm">Subtotal ({{ $purchasable->sum('quantity') }} item{{ $purchasable->sum('quantity') === 1 ? '' : 's' }})</span>
                            <span class="font-mono font-bold text-text">{{ $purchasable->sum(fn ($i) => $i->subtotal) === 0 ? 'Free' : 'Rp'.number_format($purchasable->sum(fn ($i) => $i->subtotal), 0, ',', '.') }}</span>
                        </div>

                        @if ($purchasable->isEmpty())
                            <p class="text-label-sm text-outline mt-4">Remove unavailable items to continue.</p>
                        @else
                            <x-ui.button href="{{ route('checkout.show') }}" class="w-full mt-4">Proceed to Checkout</x-ui.button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </section>
</x-layouts.app>
