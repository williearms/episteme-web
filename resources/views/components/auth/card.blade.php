@props(['title', 'subtitle' => null])

<section class="max-w-md mx-auto px-6 py-16 lg:py-24">
    <div class="bg-surface border border-border/40 rounded-card p-8 shadow-sm">
        <h1 class="text-headline-md text-text mb-1">{{ $title }}</h1>

        @if ($subtitle)
            <p class="text-body-sm text-text-muted mb-6">{{ $subtitle }}</p>
        @else
            <div class="mb-6"></div>
        @endif

        {{ $slot }}
    </div>
</section>
