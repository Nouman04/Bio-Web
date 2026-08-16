{{-- Date added column --}}
<div class="flex flex-col gap-0.5">
    <span class="text-sm text-on-surface dark:text-slate-300">{{ $diagram->created_at?->format('M j, Y') ?? '—' }}</span>
    <span class="text-xs text-on-surface-variant dark:text-slate-400">{{ $diagram->created_at?->diffForHumans() }}</span>
</div>
