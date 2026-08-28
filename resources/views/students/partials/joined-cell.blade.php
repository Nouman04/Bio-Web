{{-- Joined column for the roster DataTable --}}
<div class="flex flex-col gap-0.5">
    <span class="text-sm text-on-surface dark:text-slate-200">{{ $student->created_at?->format('d M Y') ?? '—' }}</span>
    <span class="text-xs text-on-surface-variant dark:text-slate-400">{{ $student->created_at?->diffForHumans() }}</span>
</div>
