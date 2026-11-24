<x-layouts.app :title="$quiz->title.' — '.$product->title">
    <section class="max-w-3xl mx-auto px-6 py-12">
        <a href="{{ route('learning.show', $product->slug) }}" class="inline-flex items-center gap-1 text-label-md text-text-muted hover:text-primary transition-colors mb-6">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_back</span>
            {{ $product->title }}
        </a>

        <div class="mb-8">
            <p class="text-label-sm text-outline">{{ $quiz->module->title }}</p>
            <h1 class="text-headline-lg text-text tracking-tight mt-1">{{ $quiz->title }}</h1>
            @if ($quiz->description)
                <p class="text-body-md text-text-muted mt-3">{{ $quiz->description }}</p>
            @endif
            <p class="text-label-md text-outline mt-3">
                {{ $quiz->questions->count() }} question{{ $quiz->questions->count() === 1 ? '' : 's' }}
                &middot; pass mark {{ $quiz->pass_percentage }}% &middot; you can retake it as many times as you like
            </p>
        </div>

        @if ($attempts->isNotEmpty())
            <div class="bg-surface border border-border/40 rounded-card p-5 mb-8">
                <h2 class="text-label-sm font-semibold text-outline uppercase tracking-wider mb-3">Previous attempts</h2>
                <ul class="divide-y divide-border/40">
                    @foreach ($attempts as $previous)
                        <li class="flex items-center justify-between gap-3 py-2 text-body-sm">
                            <a href="{{ route('learning.quizzes.attempts.show', [$product->slug, $quiz, $previous]) }}" class="text-primary hover:underline">
                                {{ $previous->created_at->translatedFormat('d F Y, H:i') }}
                            </a>
                            <span class="flex items-center gap-3">
                                <span class="font-mono">{{ $previous->percentage }}%</span>
                                <x-ui.badge :variant="$previous->passed ? 'success' : 'error'">{{ $previous->passed ? 'Passed' : 'Not passed' }}</x-ui.badge>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('learning.quizzes.attempts.store', [$product->slug, $quiz]) }}" class="flex flex-col gap-6">
            @csrf

            @foreach ($quiz->questions as $question)
                <fieldset class="bg-surface border border-border/40 rounded-card p-5">
                    <legend class="sr-only">Question {{ $loop->iteration }}</legend>
                    <p class="text-body-md text-text font-medium"><span class="font-mono text-outline mr-1">{{ $loop->iteration }}.</span>{{ $question->body }}</p>
                    <p class="text-label-sm text-outline mt-1 mb-3">{{ $question->allowsMultiple() ? 'Select all that apply' : 'Select one' }}</p>

                    <div class="flex flex-col gap-2">
                        @foreach ($question->options as $option)
                            <label class="flex items-start gap-3 px-3 py-2 rounded-md border border-border/40 hover:bg-surface-subtle cursor-pointer text-body-sm text-text">
                                <input
                                    type="{{ $question->allowsMultiple() ? 'checkbox' : 'radio' }}"
                                    name="answers[{{ $question->id }}][]"
                                    value="{{ $option->id }}"
                                    class="mt-1"
                                >
                                <span>{{ $option->body }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach

            <div>
                <x-ui.button type="submit">Submit answers</x-ui.button>
            </div>
        </form>
    </section>
</x-layouts.app>
