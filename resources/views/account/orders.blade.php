<x-layouts.app title="My Orders — Episteme">
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <h1 class="text-headline-xl-mobile lg:text-headline-xl text-text tracking-tight mb-8">My Account</h1>

        @include('account._nav')

        <h2 class="text-headline-md text-text mb-6">My Orders</h2>

        @if ($orders->isEmpty())
            <div class="text-center py-20 border border-dashed border-border/60 rounded-card">
                <span class="material-symbols-outlined text-[40px] text-outline" aria-hidden="true">receipt_long</span>
                <p class="text-body-lead text-text mt-4">You haven&rsquo;t placed any orders yet.</p>
                <x-ui.button href="{{ route('products.index') }}" class="mt-6">Explore Catalog</x-ui.button>
            </div>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-3 bg-surface border border-border/40 hover:border-accent rounded-card p-5 transition-colors">
                        <div>
                            <p class="text-body-sm font-mono text-text font-medium">{{ $order->order_number }}</p>
                            <p class="text-label-sm text-outline mt-1">{{ $order->created_at->translatedFormat('d F Y') }} &middot; {{ $order->items_count }} item{{ $order->items_count === 1 ? '' : 's' }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="font-mono font-bold text-text">{{ $order->total_label }}</span>
                            <x-commerce.order-status-badge :status="$order->status" />
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @endif
    </section>
</x-layouts.app>
