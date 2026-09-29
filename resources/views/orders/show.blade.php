<x-layouts.app :title="'Order '.$order->order_number.' — Episteme'">
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-headline-xl-mobile lg:text-headline-xl text-text tracking-tight">Order {{ $order->order_number }}</h1>
                <p class="text-body-sm text-text-muted mt-1">Placed {{ $order->created_at->translatedFormat('d F Y, H:i') }}</p>
            </div>
            <x-commerce.order-status-badge :status="$order->status" class="text-label-md px-3 py-1.5" />
        </div>

        @if (session('status'))
            <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="error" class="mb-6">{{ session('error') }}</x-ui.alert>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-7 flex flex-col gap-6">
                <div class="bg-surface border border-border/40 rounded-card p-5">
                    <h2 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-4">Payment</h2>
                    @if ($latestPayment)
                        <dl class="space-y-3 text-body-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-text-muted">Status</dt>
                                <dd><x-commerce.payment-status-badge :status="$latestPayment->status" /></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-text-muted">Provider</dt>
                                <dd class="text-text font-medium capitalize">{{ $latestPayment->provider }}</dd>
                            </div>
                            @if ($latestPayment->paid_at)
                                <div class="flex items-center justify-between">
                                    <dt class="text-text-muted">Paid at</dt>
                                    <dd class="text-text font-medium">{{ $latestPayment->paid_at->translatedFormat('d F Y, H:i') }}</dd>
                                </div>
                            @endif
                        </dl>
                    @else
                        <p class="text-body-sm text-text-muted">No payment attempt has been started yet.</p>
                    @endif

                    @if ($order->status === \App\Enums\OrderStatus::Pending && $latestPayment && in_array($latestPayment->status, [\App\Enums\PaymentStatus::Failed, \App\Enums\PaymentStatus::Expired, \App\Enums\PaymentStatus::Cancelled]))
                        <form method="POST" action="{{ route('orders.retry-payment', $order) }}" class="mt-4">
                            @csrf
                            <x-ui.button type="submit">Retry Payment</x-ui.button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5">
                <x-commerce.order-summary :items="$order->items" :subtotal="$order->subtotal" :discount="$order->discount" :total="$order->total" />
            </div>
        </div>
    </section>
</x-layouts.app>
