@props([
    'avatar' => null,
    'name',
    'role' => null,
    'bio' => null,
    'tags' => [],
    'footerLabel' => null,
])

<div class="bg-surface border border-border/40 rounded-card p-6 group hover:border-accent transition-colors">
    <div class="flex items-center gap-4 mb-5">
        <div class="w-16 h-16 rounded-full overflow-hidden border border-border/40 bg-surface-muted flex-shrink-0">
            @if ($avatar)
                <img src="{{ $avatar }}" alt="{{ $name }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-headline-sm font-semibold text-primary bg-primary-soft" aria-hidden="true">
                    {{ collect(explode(' ', $name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}
                </div>
            @endif
        </div>
        <div>
            <h3 class="text-headline-sm text-text">{{ $name }}</h3>
            @if ($role)
                <p class="text-body-sm text-outline">{{ $role }}</p>
            @endif
        </div>
    </div>

    @if ($bio)
        <p class="text-body-sm text-text-muted mb-4">
            {{ $bio }}
        </p>
    @endif

    @if (count($tags))
        <div class="flex flex-wrap gap-1.5 mb-5">
            @foreach ($tags as $tag)
                <span class="px-2 py-0.5 bg-surface-subtle text-code-sm font-mono text-text-muted rounded">{{ $tag }}</span>
            @endforeach
        </div>
    @endif

    @if ($footerLabel)
        <div class="pt-4 border-t border-border/40 text-label-sm text-outline">
            {{ $footerLabel }}
        </div>
    @endif
</div>
