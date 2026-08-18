{{-- Created column — date above, relative age below --}}
<div class="flex flex-col gap-0.5">
    <span class="text-sm text-on-surface dark:text-slate-200">{{ $quiz->created_at?->format('M j, Y') ?? '—' }}</span>
    <span class="text-xs text-on-surface-variant dark:text-slate-400">{{ $quiz->created_at?->diffForHumans() ?? '' }}</span>
</div>
