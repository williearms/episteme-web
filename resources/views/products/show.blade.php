@php
    $meta = $product->meta ?? [];
@endphp

<x-layouts.app
    :title="$product->title.' — Episteme'"
    :description="$product->short_description"
    :og-image="$product->thumbnail_url"
>
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 pt-8">
        <nav aria-label="Breadcrumb" class="text-label-sm text-outline mb-6">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('home') }}" class="hover:text-primary transition-colors">Home</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('products.index') }}" class="hover:text-primary transition-colors">Catalog</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('categories.show', $product->category) }}" class="hover:text-primary transition-colors">{{ $product->category->name }}</a></li>
                <li aria-hidden="true">/</li>
                <li class="text-text font-medium" aria-current="page">{{ $product->title }}</li>
            </ol>
        </nav>
    </section>

    {{-- Header --}}
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 pb-12 border-b border-border/40">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            <div class="lg:col-span-7 flex flex-col gap-5">
                <x-ui.badge :variant="$product->type_variant" class="w-fit">{{ $product->type_label }}</x-ui.badge>

                <h1 class="text-headline-xl-mobile lg:text-headline-xl text-text tracking-tight">
                    {{ $product->title }}
                </h1>

                <p class="text-body-lead text-text-muted max-w-2xl">
                    {{ $product->short_description }}
                </p>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-3 pt-2 text-body-sm text-text-muted">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px] text-outline" aria-hidden="true">person</span>
                        {{ $product->instructor?->name ?? 'Episteme' }}
                    </span>
                    @if ($product->level_label)
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px] text-outline" aria-hidden="true">signal_cellular_alt</span>
                            {{ $product->level_label }}
                        </span>
                    @endif
                    @if ($product->duration)
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px] text-outline" aria-hidden="true">schedule</span>
                            {{ $product->duration }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-4 pt-4">
                    <span class="font-mono text-headline-sm font-bold text-text">{{ $product->price_label }}</span>

                    <button
                        type="button"
                        disabled
                        aria-disabled="true"
                        class="inline-flex items-center gap-2 bg-accent/50 text-text/60 font-bold px-6 py-3 rounded-md cursor-not-allowed"
                    >
                        {{ $product->cta_label }}
                    </button>
                </div>
                <p class="text-label-sm text-outline -mt-2">Enrollment and checkout are not available yet — coming in a future phase.</p>
            </div>

            <div class="lg:col-span-5">
                <div class="aspect-video w-full rounded-card overflow-hidden border border-border/40 bg-surface-subtle">
                    <img src="{{ $product->thumbnail_url }}" alt="" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            {{-- Main content --}}
            <div class="lg:col-span-8 flex flex-col gap-12">
                <div>
                    <h2 class="text-headline-md text-text mb-4">Overview</h2>
                    <div class="text-body-md text-text-muted leading-relaxed space-y-4">
                        @foreach (explode("\n\n", $product->description) as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </div>

                @if (! empty($meta['outcomes']))
                    <div>
                        <h2 class="text-headline-md text-text mb-4">What you&rsquo;ll learn</h2>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                            @foreach ($meta['outcomes'] as $outcome)
                                <li class="flex items-start gap-2 text-body-sm text-text-muted">
                                    <span class="material-symbols-outlined text-[18px] text-primary mt-0.5" aria-hidden="true">check_circle</span>
                                    <span>{{ $outcome }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (! empty($meta['audience']))
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Who this is for</h2>
                        <p class="text-body-md text-text-muted">{{ $meta['audience'] }}</p>
                    </div>
                @endif

                @if (! empty($meta['requirements']))
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Requirements</h2>
                        <ul class="space-y-2">
                            @foreach ($meta['requirements'] as $requirement)
                                <li class="flex items-start gap-2 text-body-sm text-text-muted">
                                    <span class="material-symbols-outlined text-[18px] text-outline mt-0.5" aria-hidden="true">arrow_right</span>
                                    <span>{{ $requirement }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (! empty($meta['modules']) || ! empty($meta['lessons']))
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Curriculum preview</h2>
                        <div class="bg-surface border border-border/40 rounded-card p-5 flex items-center gap-8">
                            @if (! empty($meta['modules']))
                                <div>
                                    <div class="text-headline-sm font-bold text-text">{{ $meta['modules'] }}</div>
                                    <div class="text-label-sm text-outline">Modules</div>
                                </div>
                            @endif
                            @if (! empty($meta['lessons']))
                                <div>
                                    <div class="text-headline-sm font-bold text-text">{{ $meta['lessons'] }}</div>
                                    <div class="text-label-sm text-outline">Lessons</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if (in_array($product->product_type, ['webinar', 'workshop']) && (! empty($meta['event_date']) || ! empty($meta['location'])))
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Event details</h2>
                        <dl class="bg-surface border border-border/40 rounded-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if (! empty($meta['event_date']))
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Date</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ \Illuminate\Support\Carbon::parse($meta['event_date'])->translatedFormat('d F Y') }}</dd>
                                </div>
                            @endif
                            @if (! empty($meta['event_time']))
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Time</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $meta['event_time'] }}</dd>
                                </div>
                            @endif
                            @if (! empty($meta['location']))
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Location</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $meta['location'] }}</dd>
                                </div>
                            @endif
                            @if (! empty($meta['speaker']))
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Speaker</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $meta['speaker'] }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                @if ($product->product_type === 'mentoring' && (! empty($meta['format']) || ! empty($meta['session_duration'])))
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Mentoring format</h2>
                        <dl class="bg-surface border border-border/40 rounded-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if (! empty($meta['format']))
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Format</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $meta['format'] }}</dd>
                                </div>
                            @endif
                            @if (! empty($meta['session_duration']))
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Session duration</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $meta['session_duration'] }}</dd>
                                </div>
                            @endif
                            @if (! empty($meta['availability']))
                                <div class="sm:col-span-2">
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Availability</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $meta['availability'] }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                @if ($product->product_type === 'digital-product' && (! empty($meta['file_type']) || ! empty($meta['access_type'])))
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Product format</h2>
                        <dl class="bg-surface border border-border/40 rounded-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if (! empty($meta['file_type']))
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">File type</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $meta['file_type'] }}</dd>
                                </div>
                            @endif
                            @if (! empty($meta['access_type']))
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Access</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $meta['access_type'] }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-4 flex flex-col gap-6">
                <div class="bg-surface border border-border/40 rounded-card p-5">
                    <h2 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-4">Product information</h2>
                    <dl class="space-y-3 text-body-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-text-muted">Type</dt>
                            <dd class="text-text font-medium">{{ $product->type_label }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-text-muted">Category</dt>
                            <dd class="text-text font-medium">
                                <a href="{{ route('categories.show', $product->category) }}" class="hover:text-primary transition-colors">{{ $product->category->name }}</a>
                            </dd>
                        </div>
                        @if ($product->level_label)
                            <div class="flex items-center justify-between">
                                <dt class="text-text-muted">Level</dt>
                                <dd class="text-text font-medium">{{ $product->level_label }}</dd>
                            </div>
                        @endif
                        @if ($product->duration)
                            <div class="flex items-center justify-between">
                                <dt class="text-text-muted">Duration</dt>
                                <dd class="text-text font-medium">{{ $product->duration }}</dd>
                            </div>
                        @endif
                        <div class="flex items-center justify-between pt-3 border-t border-border/40">
                            <dt class="text-text-muted">Price</dt>
                            <dd class="font-mono font-bold text-text">{{ $product->price_label }}</dd>
                        </div>
                    </dl>
                </div>

                @if ($product->instructor)
                    <div>
                        <h2 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-3">Instructor</h2>
                        <x-instructor.card
                            :avatar="$product->instructor->avatar"
                            :name="$product->instructor->name"
                            :role="$product->instructor->headline"
                            :bio="$product->instructor->bio"
                        />
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layouts.app>
