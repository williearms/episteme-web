@props(['product'])

<article {{ $attributes->class('relative bg-surface border border-border/40 rounded-card flex flex-col justify-between overflow-hidden group hover:border-accent hover:shadow-md focus-within:border-accent focus-within:shadow-md transition-all duration-200') }}>
    <div>
        <div class="h-44 w-full bg-surface-subtle relative overflow-hidden flex items-center justify-center border-b border-border/40">
            <img
                src="{{ $product->thumbnail_url }}"
                alt=""
                loading="lazy"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            >
            <x-ui.badge :variant="$product->type_variant" class="absolute top-3 left-3">{{ $product->type_label }}</x-ui.badge>
            @if ($product->level_label)
                <x-ui.badge variant="meta" class="absolute top-3 right-3">{{ $product->level_label }}</x-ui.badge>
            @endif
            @if ($product->featured)
                <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 bg-inverse/90 text-inverse-text text-label-sm font-semibold px-2 py-0.5 rounded-md backdrop-blur-sm">
                    <span class="material-symbols-outlined text-[14px] text-accent" aria-hidden="true">star</span>
                    Featured
                </span>
            @endif
        </div>
        <div class="p-5">
            @if ($product->category)
                <span class="text-label-sm text-outline">{{ $product->category->name }}</span>
            @endif
            <h3 class="text-headline-sm text-text group-hover:text-primary transition-colors mt-1">
                <a href="{{ route('products.show', $product) }}" class="rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-accent">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    {{ $product->title }}
                </a>
            </h3>
            <p class="text-body-sm text-text-muted mt-2 line-clamp-2">
                {{ $product->short_description }}
            </p>
        </div>
    </div>
    <div class="p-5 pt-0">
        <div class="border-t border-border/40 pt-3.5 flex items-center justify-between">
            <div>
                <span class="block text-[11px] font-semibold text-outline uppercase tracking-wider">Instructor</span>
                <span class="text-body-sm font-medium text-text">{{ $product->instructor?->name ?? 'Episteme' }}</span>
            </div>
            <div class="text-right">
                <span class="font-mono text-code-sm font-bold text-text">{{ $product->price_label }}</span>
            </div>
        </div>
    </div>
</article>
