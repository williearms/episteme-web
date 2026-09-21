@props([
    'avatar',
    'name',
    'role',
    'bio',
    'tags' => [],
    'footerLabel',
])

<div class="bg-surface border border-border/40 rounded-card p-6 group hover:border-accent transition-colors">
    <div class="flex items-center gap-4 mb-5">
        <div class="w-16 h-16 rounded-full overflow-hidden border border-border/40 bg-surface-muted flex-shrink-0">
            <img src="{{ $avatar }}" alt="{{ $name }}" class="w-full h-full object-cover">
        </div>
        <div>
            <h3 class="text-headline-sm text-text">{{ $name }}</h3>
            <p class="text-body-sm text-outline">{{ $role }}</p>
        </div>
    </div>
    <p class="text-body-sm text-text-muted mb-4">
        {{ $bio }}
    </p>
    <div class="flex flex-wrap gap-1.5 mb-5">
        @foreach ($tags as $tag)
            <span class="px-2 py-0.5 bg-surface-subtle text-code-sm font-mono text-text-muted rounded">{{ $tag }}</span>
        @endforeach
    </div>
    <div class="pt-4 border-t border-border/40 flex items-center justify-between text-label-sm">
        <span class="text-outline">{{ $footerLabel }}</span>
        <a href="#" class="text-primary font-semibold hover:underline">View Faculty Profile &rarr;</a>
    </div>
</div>
