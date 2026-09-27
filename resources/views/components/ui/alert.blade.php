@props(['variant' => 'success'])

@php
    $styles = [
        'success' => 'bg-primary-soft/40 text-primary border-primary/30',
        'error' => 'bg-error/10 text-error border-error/30',
    ];
@endphp

<div role="status" {{ $attributes->class('rounded-md border px-4 py-3 text-body-sm mb-6 '.($styles[$variant] ?? $styles['success'])) }}>
    {{ $slot }}
</div>
