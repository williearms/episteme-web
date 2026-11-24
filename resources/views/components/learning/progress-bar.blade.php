@props(['percent'])

<div {{ $attributes->class('flex items-center gap-3') }}>
    <div class="flex-1 h-2 rounded-full bg-surface-muted overflow-hidden" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
        <div class="h-full bg-accent rounded-full" style="width: {{ $percent }}%"></div>
    </div>
    <span class="font-mono text-code-sm text-text-muted w-10 text-right">{{ $percent }}%</span>
</div>
