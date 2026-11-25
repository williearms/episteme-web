<x-layouts.app title="My Learning — Episteme">
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <h1 class="text-headline-xl-mobile lg:text-headline-xl text-text tracking-tight mb-8">My Account</h1>

        @include('account._nav')

        <h2 class="text-headline-md text-text mb-6">My Learning</h2>

        @if ($enrollments->isEmpty())
            <div class="text-center py-20 border border-dashed border-border/60 rounded-card">
                <span class="material-symbols-outlined text-[40px] text-outline" aria-hidden="true">school</span>
                <p class="text-body-lead text-text mt-4">You&rsquo;re not enrolled in anything yet.</p>
                <p class="text-body-sm text-text-muted mt-2">Products you buy will appear here as soon as your payment is confirmed.</p>
                <x-ui.button href="{{ route('products.index') }}" class="mt-6">Explore Catalog</x-ui.button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($enrollments as $enrollment)
                    @php $product = $enrollment->product; @endphp
                    <a href="{{ route('learning.show', $product->slug) }}" class="flex flex-col gap-4 bg-surface border border-border/40 hover:border-accent rounded-card overflow-hidden transition-colors">
                        <img src="{{ $product->thumbnail_url }}" alt="" class="h-40 w-full object-cover border-b border-border/40">
                        <div class="px-5 pb-5 flex flex-col gap-3">
                            <x-ui.badge :variant="$product->type_variant" class="w-fit">{{ $product->type_label }}</x-ui.badge>
                            <h3 class="text-headline-sm text-text">{{ $product->title }}</h3>
                            @if ($product->product_type === \App\Enums\ProductType::Course)
                                <x-learning.progress-bar :percent="$enrollment->progressPercent()" />
                            @endif
                            @if ($enrollment->completed_at)
                                <span class="text-label-sm text-success-strong font-semibold">Completed</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.app>
