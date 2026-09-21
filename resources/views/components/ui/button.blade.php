@props([
    'variant' => 'filled',
    'size' => 'md',
    'href' => null,
])

@php
    $variants = [
        'filled' => 'bg-accent hover:bg-primary-hover text-text hover:text-white shadow-sm hover:shadow-md',
        'outline' => 'bg-surface hover:bg-surface-subtle text-text border border-border/60',
        'ghost' => 'text-text hover:text-primary',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-label-md rounded-md',
        'md' => 'px-6 py-3 text-label-md rounded-md',
        'lg' => 'px-8 py-4 text-label-md rounded-card',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 font-bold transition-all duration-150 active:scale-[0.99] '
        . ($variants[$variant] ?? $variants['filled']) . ' '
        . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->class($classes)->merge(['type' => 'button']) }}>
        {{ $slot }}
    </button>
@endif
