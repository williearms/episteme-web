@props(['code', 'title', 'message'])

<x-layouts.app :title="$title.' — Episteme'">
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-24 text-center">
        <p class="font-mono text-code-sm text-primary uppercase tracking-wider font-semibold">Error {{ $code }}</p>
        <h1 class="text-headline-xl-mobile lg:text-headline-xl text-text tracking-tight mt-3">{{ $title }}</h1>
        <p class="text-body-lead text-text-muted max-w-xl mx-auto mt-4">{{ $message }}</p>
        <div class="flex flex-wrap items-center justify-center gap-3 mt-8">
            <x-ui.button href="{{ route('home') }}">Back to home</x-ui.button>
            <x-ui.button href="{{ route('products.index') }}" variant="outline">Browse the catalog</x-ui.button>
        </div>
    </section>
</x-layouts.app>
