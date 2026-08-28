{{-- Pending-quizzes column for the roster DataTable.

     A count of nothing is left plain: a badge on every row would make the
     rows that actually need marking harder to pick out. --}}
@php $pending = (int) $student->pending_quizzes_count; @endphp

@if($pending > 0)
    <a href="{{ route('students.pending', $student->uuid) }}"
        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-error/10 text-error text-xs font-bold hover:bg-error/20 transition-colors"
        title="{{ $pending }} {{ Str::plural('paper', $pending) }} waiting to be marked">
        <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
        {{ $pending }}
    </a>
@else
    <span class="text-xs text-on-surface-variant dark:text-slate-500">—</span>
@endif
