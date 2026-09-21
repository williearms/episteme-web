@props([
    'image',
    'imageAlt' => '',
    'typeLabel',
    'typeVariant' => 'neutral',
    'meta',
    'rating',
    'learners',
    'title',
    'description',
    'instructor',
    'price',
])

<div class="bg-surface border border-border/40 rounded-card flex flex-col justify-between overflow-hidden group hover:border-accent hover:shadow-md transition-all duration-200">
    <div>
        <div class="h-44 w-full bg-surface-subtle relative overflow-hidden flex items-center justify-center border-b border-border/40">
            <img src="{{ $image }}" alt="{{ $imageAlt }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            <x-ui.badge :variant="$typeVariant" class="absolute top-3 left-3">{{ $typeLabel }}</x-ui.badge>
            <x-ui.badge variant="meta" class="absolute top-3 right-3">{{ $meta }}</x-ui.badge>
        </div>
        <div class="p-5">
            <div class="flex items-center gap-1.5 text-label-sm text-outline mb-2">
                <span class="text-amber-500 font-bold">{{ $rating }} &#9733;</span>
                <span>&middot;</span>
                <span>{{ $learners }}</span>
            </div>
            <h3 class="text-headline-sm text-text group-hover:text-primary transition-colors">
                {{ $title }}
            </h3>
            <p class="text-body-sm text-text-muted mt-2 line-clamp-2">
                {{ $description }}
            </p>
        </div>
    </div>
    <div class="p-5 pt-0">
        <div class="border-t border-border/40 pt-3.5 flex items-center justify-between">
            <div>
                <span class="block text-[11px] font-semibold text-outline uppercase tracking-wider">Instructor</span>
                <span class="text-body-sm font-medium text-text">{{ $instructor }}</span>
            </div>
            <div class="text-right">
                <span class="font-mono text-code-sm font-bold text-text">{{ $price }}</span>
            </div>
        </div>
    </div>
</div>
