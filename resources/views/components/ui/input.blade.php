@props(['placeholder' => 'Search...', 'icon' => 'search'])

<div class="relative">
    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-outline">
        <span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>
    </span>
    <input
        type="text"
        placeholder="{{ $placeholder }}"
        {{ $attributes->class('bg-surface border border-border/40 rounded-full pl-9 pr-8 py-1.5 text-body-sm text-text placeholder:text-outline focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent w-44 transition-all focus:w-56') }}
    >
</div>
