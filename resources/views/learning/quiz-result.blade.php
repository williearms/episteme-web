<x-layouts.app :title="'Result — '.$quiz->title">
    <section class="max-w-3xl mx-auto px-6 py-12">
        <a href="{{ route('learning.show', $product->slug) }}" class="inline-flex items-center gap-1 text-label-md text-text-muted hover:text-primary transition-colors mb-6">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
            {{ $product->title }}
        </a>

        <div class="bg-surface border border-border/40 rounded-card p-6 mb-8 flex flex-col gap-3">
            <p class="text-label-sm text-outline">{{ $quiz->title }}</p>
            <div class="flex flex-wrap items-center gap-4">
                <span class="font-mono text-headline-lg font-bold text-text">{{ $attempt->percentage }}%</span>
                <x-ui.badge :variant="$attempt->passed ? 'success' : 'error'" class="text-label-md px-3 py-1.5">{{ $attempt->passed ? 'Passed' : 'Not passed' }}</x-ui.badge>
            </div>
            <p class="text-body-sm text-text-muted">
                {{ $attempt->correct_count }} of {{ $attempt->total_questions }} correct &middot; pass mark {{ $quiz->pass_percentage }}%
            </p>
            <div class="flex flex-wrap gap-3 pt-2">
                <x-ui.button href="{{ route('learning.quizzes.show', [$product->slug, $quiz]) }}" variant="{{ $attempt->passed ? 'outline' : 'filled' }}">
                    {{ $attempt->passed ? 'Retake' : 'Try again' }}
                </x-ui.button>
                <x-ui.button href="{{ route('learning.show', $product->slug) }}" variant="{{ $attempt->passed ? 'filled' : 'outline' }}">Back to course</x-ui.button>
            </div>
        </div>

        <h2 class="text-headline-sm text-text mb-4">Review</h2>
        <div class="flex flex-col gap-4">
            @foreach ($quiz->questions as $question)
                @php
                    $chosen = collect($attempt->answers[$question->id] ?? []);
                    $correctIds = $question->options->where('is_correct', true)->pluck('id');
                    $isRight = $correctIds->isNotEmpty() && $chosen->sort()->values()->all() === $correctIds->sort()->values()->all();
                @endphp
                <div class="bg-surface border rounded-card p-5 {{ $isRight ? 'border-success/40' : 'border-error/40' }}">
                    <p class="flex items-start gap-2 text-body-md text-text font-medium">
                        <span class="material-symbols-outlined text-[20px] {{ $isRight ? 'text-success-strong' : 'text-error' }}" aria-label="{{ $isRight ? 'Correct' : 'Incorrect' }}">{{ $isRight ? 'check_circle' : 'cancel' }}</span>
                        <span>{{ $question->body }}</span>
                    </p>
                    <ul class="mt-3 flex flex-col gap-1.5">
                        @foreach ($question->options as $option)
                            @php $picked = $chosen->contains($option->id); @endphp
                            <li class="flex items-center gap-2 px-3 py-1.5 rounded-md text-body-sm
                                {{ $option->is_correct ? 'bg-success/10 text-text' : ($picked ? 'bg-error/10 text-text' : 'text-text-muted') }}">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">
                                    {{ $option->is_correct ? 'check' : ($picked ? 'close' : 'remove') }}
                                </span>
                                <span>{{ $option->body }}</span>
                                @if ($picked)<span class="ml-auto text-label-sm text-outline">your answer</span>@endif
                            </li>
                        @endforeach
                    </ul>
                    @if ($question->explanation)
                        <p class="mt-3 text-body-sm text-text-muted"><span class="font-semibold text-text">Why:</span> {{ $question->explanation }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.app>
