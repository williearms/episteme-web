@props(['eyebrow' => null, 'as' => 'h2'])

<div {{ $attributes->class('flex flex-col md:flex-row md:items-end justify-between mb-10') }}>
    <div>
        @if ($eyebrow)
            <span class="font-mono text-code-sm text-primary uppercase tracking-wider font-semibold">{{ $eyebrow }}</span>
        @endif
        {{-- `as` lets a page that has no other title (e.g. the catalog) make this its <h1>. --}}
        <{{ $as }} class="text-headline-xl-mobile md:text-headline-xl text-text tracking-tight mt-1">
            {{ $slot }}
        </{{ $as }}>
        @isset($lead)
            <p class="text-body-lead text-text-muted mt-2 max-w-xl">{{ $lead }}</p>
        @endisset
    </div>
    @isset($trailing)
        <div class="mt-6 md:mt-0 flex-shrink-0">{{ $trailing }}</div>
    @endisset
</div>
