@props(['items', 'subtotal', 'discount' => 0, 'total' => null])

@php
    $total ??= $subtotal - $discount;
    $money = fn (int $amount) => $amount === 0 ? 'Free' : 'Rp'.number_format($amount, 0, ',', '.');
@endphp

<div {{ $attributes->class('bg-surface border border-border/40 rounded-card p-5') }}>
    <h2 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-4">Order summary</h2>

    <ul class="space-y-3 mb-4">
        @foreach ($items as $item)
            <li class="flex items-start justify-between gap-3 text-body-sm">
                <span class="text-text">
                    {{ $item->product_title ?? $item->product?->title }}
                    @if ($item->quantity > 1)
                        <span class="text-outline">&times;{{ $item->quantity }}</span>
                    @endif
                </span>
                <span class="font-mono text-text whitespace-nowrap">{{ $money($item->subtotal) }}</span>
            </li>
        @endforeach
    </ul>

    <dl class="space-y-2 text-body-sm border-t border-border/40 pt-4">
        <div class="flex items-center justify-between">
            <dt class="text-text-muted">Subtotal</dt>
            <dd class="font-mono text-text">{{ $money($subtotal) }}</dd>
        </div>
        @if ($discount > 0)
            <div class="flex items-center justify-between">
                <dt class="text-text-muted">Discount</dt>
                <dd class="font-mono text-text">-{{ $money($discount) }}</dd>
            </div>
        @endif
        <div class="flex items-center justify-between pt-2 border-t border-border/40">
            <dt class="text-text font-semibold">Total</dt>
            <dd class="font-mono font-bold text-headline-sm text-text">{{ $money($total) }}</dd>
        </div>
    </dl>
</div>
