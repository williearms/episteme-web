@php
    use App\Enums\ProductType;
    $details = $product->type_details;
@endphp

<x-layouts.app :title="$product->title.' — My Learning'">
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <a href="{{ route('account.learning.index') }}" class="inline-flex items-center gap-1 text-label-md text-text-muted hover:text-primary transition-colors mb-6">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
            My Learning
        </a>

        @if (session('status'))
            <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
        @endif

        <div class="flex flex-col gap-3 mb-10">
            <x-ui.badge :variant="$product->type_variant" class="w-fit">{{ $product->type_label }}</x-ui.badge>
            <h1 class="text-headline-xl-mobile lg:text-headline-xl text-text tracking-tight">{{ $product->title }}</h1>
            <p class="text-body-sm text-text-muted">Enrolled {{ $enrollment->enrolled_at->translatedFormat('d F Y') }}</p>
        </div>

        @if ($product->product_type === ProductType::Course)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-8 flex flex-col gap-6">
                    @forelse ($product->course?->modules ?? [] as $module)
                        <div class="bg-surface border border-border/40 rounded-card overflow-hidden">
                            <h2 class="px-5 py-4 text-headline-sm text-text bg-surface-subtle border-b border-border/40">
                                <span class="font-mono text-code-sm text-outline mr-2">{{ str_pad($module->position, 2, '0', STR_PAD_LEFT) }}</span>{{ $module->title }}
                            </h2>
                            <ul class="divide-y divide-border/40">
                                @foreach ($module->lessons as $lesson)
                                    <li>
                                        <a href="{{ route('learning.lessons.show', [$product->slug, $lesson]) }}" class="flex items-center gap-3 px-5 py-3 text-body-sm text-text hover:bg-surface-subtle transition-colors">
                                            @if ($completedIds->contains($lesson->id))
                                                <span class="material-symbols-outlined text-[20px] text-success-strong" aria-label="Completed">check_circle</span>
                                            @else
                                                <span class="material-symbols-outlined text-[20px] text-outline" aria-label="Not completed">radio_button_unchecked</span>
                                            @endif
                                            {{ $lesson->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($module->quiz && $module->quiz->questions_count > 0)
                                <a href="{{ route('learning.quizzes.show', [$product->slug, $module->quiz]) }}" class="flex items-center gap-3 px-5 py-3 text-body-sm font-medium text-text bg-surface-subtle border-t border-border/40 hover:bg-surface-muted transition-colors">
                                    @if ($passedQuizIds->contains($module->quiz->id))
                                        <span class="material-symbols-outlined text-[20px] text-success-strong" aria-label="Passed">check_circle</span>
                                    @else
                                        <span class="material-symbols-outlined text-[20px] text-primary" aria-label="Not passed yet">quiz</span>
                                    @endif
                                    Quiz: {{ $module->quiz->title }}
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="border border-dashed border-border/60 rounded-card p-8 text-center text-body-sm text-text-muted">
                            Lessons for this course haven&rsquo;t been published yet.
                        </div>
                    @endforelse
                </div>

                <div class="lg:col-span-4">
                    <div class="bg-surface border border-border/40 rounded-card p-5 flex flex-col gap-4 sticky top-24">
                        <h2 class="text-label-sm font-semibold text-outline uppercase tracking-wider">Your progress</h2>
                        <x-learning.progress-bar :percent="$enrollment->progressPercent()" />
                        @if ($enrollment->completed_at)
                            <p class="text-body-sm text-success-strong font-semibold">You&rsquo;ve completed this course.</p>
                        @endif
                        @if ($nextLesson)
                            <x-ui.button href="{{ route('learning.lessons.show', [$product->slug, $nextLesson]) }}" class="w-full">
                                {{ $completedIds->isEmpty() && $passedQuizIds->isEmpty() ? 'Start Course' : 'Continue Learning' }}
                            </x-ui.button>
                        @elseif ($nextQuiz)
                            <x-ui.button href="{{ route('learning.quizzes.show', [$product->slug, $nextQuiz]) }}" class="w-full">Take the next quiz</x-ui.button>
                        @endif
                    </div>
                </div>
            </div>
        @elseif ($product->product_type === ProductType::DigitalProduct)
            <div class="bg-surface border border-border/40 rounded-card p-6 max-w-xl flex flex-col gap-4">
                <h2 class="text-headline-sm text-text">Your download</h2>
                @if ($product->digitalProduct?->file_path)
                    <p class="text-body-sm text-text-muted">
                        {{ $product->digitalProduct->file_type }}@if ($product->digitalProduct->file_size) &middot; {{ $product->digitalProduct->file_size }}@endif
                    </p>
                    <x-ui.button href="{{ route('learning.download', $product->slug) }}" class="w-fit">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">download</span>
                        Download
                    </x-ui.button>
                @else
                    <p class="text-body-sm text-text-muted">The file for this product isn&rsquo;t available yet. It will show up here once it&rsquo;s uploaded.</p>
                @endif
            </div>
        @else
            <div class="bg-surface border border-border/40 rounded-card p-6 max-w-2xl flex flex-col gap-4">
                <h2 class="text-headline-sm text-text">Access details</h2>

                @if ($details?->event_date ?? null)
                    <p class="text-body-sm text-text-muted">
                        <span class="font-semibold text-text">Date:</span> {{ $details->event_date->translatedFormat('d F Y') }}
                    </p>
                @endif

                @if ($product->access?->join_url)
                    <x-ui.button href="{{ $product->access->join_url }}" target="_blank" rel="noopener noreferrer" class="w-fit">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">open_in_new</span>
                        Join
                    </x-ui.button>
                @endif

                @if ($product->access?->instructions)
                    <div class="text-body-sm text-text-muted leading-relaxed space-y-3">
                        @foreach (explode("\n\n", $product->access->instructions) as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                @endif

                @if (! $product->access?->join_url && ! $product->access?->instructions)
                    <p class="text-body-sm text-text-muted">The organizer hasn&rsquo;t shared access details yet. They&rsquo;ll appear here as soon as they do.</p>
                @endif
            </div>
        @endif
    </section>
</x-layouts.app>
