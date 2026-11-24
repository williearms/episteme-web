@php
    use App\Enums\ProductType;
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
                    @if ($product->duration_label)
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px] text-outline" aria-hidden="true">schedule</span>
                            {{ $product->duration_label }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-4 pt-4">
                    <span class="font-mono text-headline-sm font-bold text-text">{{ $product->price_label }}</span>

                    @auth
                        @if ($owned)
                            <x-ui.button href="{{ route('learning.show', $product->slug) }}">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">school</span>
                                Go to My Learning
                            </x-ui.button>
                        @elseif ($inCart)
                            <x-ui.button href="{{ route('cart.index') }}" variant="outline">
                                <span class="material-symbols-outlined text-[18px] text-success" aria-hidden="true">check_circle</span>
                                In your cart &mdash; view cart
                            </x-ui.button>
                        @else
                            <form method="POST" action="{{ route('cart.items.store') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <x-ui.button type="submit">{{ $product->cta_label }}</x-ui.button>
                            </form>
                        @endif
                    @else
                        <x-ui.button href="{{ route('login') }}">Log In to {{ $product->cta_label }}</x-ui.button>
                    @endauth
                </div>
                @if (session('status'))
                    <p class="text-label-sm text-success -mt-2">{{ session('status') }}</p>
                @endif
                @if (session('error'))
                    <p class="text-label-sm text-error -mt-2">{{ session('error') }}</p>
                @endif
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

                @if (! empty($product->outcomes))
                    <div>
                        <h2 class="text-headline-md text-text mb-4">What you&rsquo;ll learn</h2>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                            @foreach ($product->outcomes as $outcome)
                                <li class="flex items-start gap-2 text-body-sm text-text-muted">
                                    <span class="material-symbols-outlined text-[18px] text-primary mt-0.5" aria-hidden="true">check_circle</span>
                                    <span>{{ $outcome }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($product->audience)
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Who this is for</h2>
                        <p class="text-body-md text-text-muted">{{ $product->audience }}</p>
                    </div>
                @endif

                @if (! empty($product->requirements))
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Requirements</h2>
                        <ul class="space-y-2">
                            @foreach ($product->requirements as $requirement)
                                <li class="flex items-start gap-2 text-body-sm text-text-muted">
                                    <span class="material-symbols-outlined text-[18px] text-outline mt-0.5" aria-hidden="true">arrow_right</span>
                                    <span>{{ $requirement }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($product->product_type === ProductType::Course && $product->course)
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Curriculum preview</h2>
                        <div class="bg-surface border border-border/40 rounded-card p-5 flex flex-wrap items-center gap-8">
                            @if ($product->course->total_modules)
                                <div>
                                    <div class="text-headline-sm font-bold text-text">{{ $product->course->total_modules }}</div>
                                    <div class="text-label-sm text-outline">Modules</div>
                                </div>
                            @endif
                            @if ($product->course->total_lessons)
                                <div>
                                    <div class="text-headline-sm font-bold text-text">{{ $product->course->total_lessons }}</div>
                                    <div class="text-label-sm text-outline">Lessons</div>
                                </div>
                            @endif
                            @if ($product->course->delivery_type)
                                <div>
                                    <div class="text-headline-sm font-bold text-text">{{ \Illuminate\Support\Str::headline($product->course->delivery_type) }}</div>
                                    <div class="text-label-sm text-outline">Delivery</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($product->product_type === ProductType::Webinar && $product->webinar)
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Event details</h2>
                        <dl class="bg-surface border border-border/40 rounded-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if ($product->webinar->event_date)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Date</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $product->webinar->event_date->translatedFormat('d F Y') }}</dd>
                                </div>
                            @endif
                            @if ($product->webinar->start_time)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Time</dt>
                                    <dd class="text-body-sm text-text mt-1">
                                        {{ substr($product->webinar->start_time, 0, 5) }}@if ($product->webinar->end_time)&ndash;{{ substr($product->webinar->end_time, 0, 5) }}@endif
                                        {{ $product->webinar->timezone }}
                                    </dd>
                                </div>
                            @endif
                            @if ($product->webinar->platform)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Platform</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $product->webinar->platform }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                @if ($product->product_type === ProductType::Workshop && $product->workshop)
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Event details</h2>
                        <dl class="bg-surface border border-border/40 rounded-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if ($product->workshop->event_date)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Date</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $product->workshop->event_date->translatedFormat('d F Y') }}</dd>
                                </div>
                            @endif
                            @if ($product->workshop->duration_minutes)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Duration</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $product->workshop->duration_minutes }} minutes</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-label-sm text-outline uppercase tracking-wider">Mode</dt>
                                <dd class="text-body-sm text-text mt-1">{{ \Illuminate\Support\Str::headline($product->workshop->mode) }}</dd>
                            </div>
                            @if ($product->workshop->location)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Location</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $product->workshop->location }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                @if ($product->product_type === ProductType::Bootcamp && $product->bootcamp)
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Program details</h2>
                        <dl class="bg-surface border border-border/40 rounded-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if ($product->bootcamp->duration)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Duration</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $product->bootcamp->duration }}</dd>
                                </div>
                            @endif
                            @if ($product->bootcamp->schedule)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Schedule</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $product->bootcamp->schedule }}</dd>
                                </div>
                            @endif
                            @if ($product->bootcamp->cohort)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Cohort</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $product->bootcamp->cohort }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-label-sm text-outline uppercase tracking-wider">Delivery mode</dt>
                                <dd class="text-body-sm text-text mt-1">{{ \Illuminate\Support\Str::headline($product->bootcamp->delivery_mode) }}</dd>
                            </div>
                        </dl>
                    </div>
                @endif

                @if ($product->product_type === ProductType::Mentoring && $product->mentoring)
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Mentoring format</h2>
                        <dl class="bg-surface border border-border/40 rounded-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if ($product->mentoring->session_duration_minutes)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Session duration</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $product->mentoring->session_duration_minutes }} minutes</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-label-sm text-outline uppercase tracking-wider">Number of sessions</dt>
                                <dd class="text-body-sm text-text mt-1">{{ $product->mentoring->number_of_sessions }}</dd>
                            </div>
                            <div>
                                <dt class="text-label-sm text-outline uppercase tracking-wider">Session type</dt>
                                <dd class="text-body-sm text-text mt-1">{{ \Illuminate\Support\Str::headline($product->mentoring->session_type) }}</dd>
                            </div>
                            <div>
                                <dt class="text-label-sm text-outline uppercase tracking-wider">Delivery mode</dt>
                                <dd class="text-body-sm text-text mt-1">{{ \Illuminate\Support\Str::headline($product->mentoring->delivery_mode) }}</dd>
                            </div>
                        </dl>
                    </div>
                @endif

                @if ($product->product_type === ProductType::DigitalProduct && $product->digitalProduct)
                    <div>
                        <h2 class="text-headline-md text-text mb-4">Product format</h2>
                        <dl class="bg-surface border border-border/40 rounded-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if ($product->digitalProduct->file_type)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">File type</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $product->digitalProduct->file_type }}</dd>
                                </div>
                            @endif
                            @if ($product->digitalProduct->file_size)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">File size</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $product->digitalProduct->file_size }}</dd>
                                </div>
                            @endif
                            @if ($product->digitalProduct->access_type)
                                <div>
                                    <dt class="text-label-sm text-outline uppercase tracking-wider">Access</dt>
                                    <dd class="text-body-sm text-text mt-1">{{ $product->digitalProduct->access_type }}</dd>
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
                        @if ($product->duration_label)
                            <div class="flex items-center justify-between">
                                <dt class="text-text-muted">Duration</dt>
                                <dd class="text-text font-medium">{{ $product->duration_label }}</dd>
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
