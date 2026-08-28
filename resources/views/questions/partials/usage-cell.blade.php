{{-- Where the question is used --}}
@php
    $links = ($question->questionables_count ?? 0) + ($question->assessments_count ?? 0);
@endphp
@if($links > 0)
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-tertiary-container/20 text-tertiary">
        <i class="fa-solid fa-link text-[10px]"></i>
        Assigned · {{ $links }}
    </span>
@else
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-surface-container-low text-on-surface-variant dark:bg-slate-900 dark:text-slate-300">
        <span class="w-1.5 h-1.5 rounded-full bg-outline"></span>
        Unassigned
    </span>
@endif
