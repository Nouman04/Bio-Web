{{-- Chapter column --}}
@if($summary->chapter)
    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-primary/8 text-primary dark:bg-primary/20 dark:text-primary-fixed-dim">
        {{ $summary->chapter->title }}
    </span>
@else
    <span class="text-xs text-outline dark:text-slate-500">—</span>
@endif
