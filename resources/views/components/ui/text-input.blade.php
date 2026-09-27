@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'required' => false,
])

@php
    $hasError = $errors->has($name);
@endphp

<div>
    <label for="{{ $name }}" class="block text-label-sm font-semibold text-outline uppercase tracking-wider mb-1.5">
        {{ $label }}
        @if ($required)
            <span class="text-error" aria-hidden="true">*</span>
        @endif
    </label>

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if ($required) required aria-required="true" @endif
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{ $attributes->class(
            'w-full bg-surface border rounded-md px-3 py-2.5 text-body-sm text-text placeholder:text-outline focus:outline-none focus:ring-1 transition-colors '
            . ($hasError ? 'border-error focus:border-error focus:ring-error' : 'border-border/40 focus:border-accent focus:ring-accent')
        ) }}
    >

    @error($name)
        <p id="{{ $name }}-error" class="mt-1.5 text-label-sm text-error">{{ $message }}</p>
    @enderror
</div>
