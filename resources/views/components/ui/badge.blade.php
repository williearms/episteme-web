@props(['variant' => 'neutral'])

@php
    $styles = [
        'neutral' => 'bg-surface/90 backdrop-blur-sm text-text border border-border/40 text-label-sm font-semibold px-2.5 py-1 rounded-md',
        'accent' => 'bg-accent text-primary-hover text-label-sm font-semibold px-2.5 py-1 rounded-md',
        'lavender' => 'bg-lavender-soft text-lavender text-label-sm font-semibold px-2.5 py-1 rounded-md',
        'meta' => 'bg-surface/90 backdrop-blur-sm text-outline border border-border/40 font-mono text-code-sm px-2 py-0.5 rounded',
        'success' => 'bg-success/10 text-success border border-success/20 text-label-sm font-semibold px-2.5 py-1 rounded-md',
        'error' => 'bg-error/10 text-error border border-error/20 text-label-sm font-semibold px-2.5 py-1 rounded-md',
    ];
@endphp

<span {{ $attributes->class($styles[$variant] ?? $styles['neutral']) }}>{{ $slot }}</span>
