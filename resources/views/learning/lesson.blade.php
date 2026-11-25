<x-layouts.app :title="$lesson->title.' — '.$product->title">
    <section class="max-w-[1360px] mx-auto px-6 lg:px-16 py-12">
        <a href="{{ route('learning.show', $product->slug) }}" class="inline-flex items-center gap-1 text-label-md text-text-muted hover:text-primary transition-colors mb-6">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
            {{ $product->title }}
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-8 flex flex-col gap-6">
                @if (session('status'))
                    <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
                @endif

                <div>
                    <p class="text-label-sm text-outline">{{ $lesson->module->title }}</p>
                    <h1 class="text-headline-lg text-text tracking-tight mt-1">{{ $lesson->title }}</h1>
                </div>

                {{-- content_html is sanitized (see App\Support\RichText) — the only unescaped output of author content. --}}
                <div class="lesson-content text-body-md">{!! $lesson->content_html !!}</div>

                <div class="flex flex-wrap items-center justify-between gap-4 pt-6 border-t border-border/40">
                    @if ($completedIds->contains($lesson->id))
                        <form method="POST" action="{{ route('learning.lessons.uncomplete', [$product->slug, $lesson]) }}">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="outline">
                                <span class="material-symbols-outlined text-[18px] text-success-strong" aria-hidden="true">check_circle</span>
                                Completed &mdash; undo
                            </x-ui.button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('learning.lessons.complete', [$product->slug, $lesson]) }}">
                            @csrf
                            <x-ui.button type="submit">Mark as complete</x-ui.button>
                        </form>
                    @endif

                    <div class="flex items-center gap-3">
                        @if ($previous)
                            <x-ui.button href="{{ route('learning.lessons.show', [$product->slug, $previous]) }}" variant="ghost">&larr; Previous</x-ui.button>
                        @endif
                        @if ($next)
                            <x-ui.button href="{{ route('learning.lessons.show', [$product->slug, $next]) }}" variant="outline">Next &rarr;</x-ui.button>
                        @endif
                    </div>
                </div>
            </div>

            <aside class="lg:col-span-4">
                <div class="bg-surface border border-border/40 rounded-card p-5 flex flex-col gap-5 sticky top-24">
                    <x-learning.progress-bar :percent="$enrollment->progressPercent()" />
                    @foreach ($modules as $module)
                        <div>
                            <h2 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-2">{{ $module->title }}</h2>
                            <ul class="space-y-1">
                                @foreach ($module->lessons as $item)
                                    <li>
                                        <a
                                            href="{{ route('learning.lessons.show', [$product->slug, $item]) }}"
                                            @if ($item->id === $lesson->id) aria-current="page" @endif
                                            class="flex items-center gap-2 px-2 py-1.5 rounded-md text-body-sm {{ $item->id === $lesson->id ? 'bg-accent/20 text-text font-medium' : 'text-text-muted hover:bg-surface-subtle' }}"
                                        >
                                            <span class="material-symbols-outlined text-[18px] {{ $completedIds->contains($item->id) ? 'text-success-strong' : 'text-outline' }}" aria-hidden="true">{{ $completedIds->contains($item->id) ? 'check_circle' : 'radio_button_unchecked' }}</span>
                                            {{ $item->title }}
                                        </a>
                                    </li>
                                @endforeach
                                @if ($module->quiz && $module->quiz->questions_count > 0)
                                    <li>
                                        <a href="{{ route('learning.quizzes.show', [$product->slug, $module->quiz]) }}" class="flex items-center gap-2 px-2 py-1.5 rounded-md text-body-sm font-medium text-text hover:bg-surface-subtle">
                                            <span class="material-symbols-outlined text-[18px] {{ $passedQuizIds->contains($module->quiz->id) ? 'text-success-strong' : 'text-primary' }}" aria-hidden="true">{{ $passedQuizIds->contains($module->quiz->id) ? 'check_circle' : 'quiz' }}</span>
                                            Quiz: {{ $module->quiz->title }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endforeach
                </div>
            </aside>
        </div>
    </section>
</x-layouts.app>
