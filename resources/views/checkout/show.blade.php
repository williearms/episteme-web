<x-layouts.app title="Checkout — Episteme">
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <h1 class="text-headline-xl-mobile lg:text-headline-xl text-text tracking-tight mb-8">Checkout</h1>

        @if (session('error'))
            <x-ui.alert variant="error" class="mb-6">{{ session('error') }}</x-ui.alert>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-7 flex flex-col gap-4">
                <div class="bg-surface border border-border/40 rounded-card p-5">
                    <h2 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-4">Buyer details</h2>
                    <dl class="space-y-2 text-body-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-text-muted">Name</dt>
                            <dd class="text-text font-medium">{{ auth()->user()->name }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-text-muted">Email</dt>
                            <dd class="text-text font-medium">{{ auth()->user()->email }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="flex flex-col gap-3">
                    @foreach ($items as $item)
                        <div class="flex items-center gap-4 bg-surface border border-border/40 rounded-card p-4">
                            <img src="{{ $item->product->thumbnail_url }}" alt="" class="w-16 h-16 rounded-md object-cover border border-border/40">
                            <div class="flex-1 min-w-0">
                                <p class="text-body-sm font-medium text-text line-clamp-1">{{ $item->product->title }}</p>
                                <p class="text-label-sm text-outline">{{ $item->product->type_label }} &times;{{ $item->quantity }}</p>
                            </div>
                            <span class="font-mono text-body-sm font-bold text-text">{{ $item->product->price_label === 'Free' ? 'Free' : 'Rp'.number_format($item->product->price * $item->quantity, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="flex flex-col gap-4 sticky top-24">
                    <x-commerce.order-summary :items="$items" :subtotal="$subtotal" />

                    <form method="POST" action="{{ route('checkout.store') }}">
                        @csrf
                        <x-ui.button type="submit" class="w-full">
                            {{ $subtotal === 0 ? 'Place Free Order' : 'Place Order & Pay' }}
                        </x-ui.button>
                    </form>
                    <p class="text-label-sm text-outline text-center">
                        @if ($subtotal === 0)
                            No payment is required for this order.
                        @else
                            You'll be redirected to our payment partner to complete payment securely.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
