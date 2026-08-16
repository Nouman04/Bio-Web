{{-- Linked question count column --}}
@if($summary->questionables_count > 0)
    <span class="inline-flex items-center justify-center min-w-[2rem] h-7 px-2 rounded-lg bg-surface-container-low dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700 text-xs font-bold text-on-surface dark:text-slate-200">
        {{ $summary->questionables_count }}
    </span>
@else
    <span class="text-xs text-outline dark:text-slate-500">0</span>
@endif
