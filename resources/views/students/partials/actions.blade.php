{{-- Actions column for the roster DataTable --}}
@php $pending = (int) $student->pending_quizzes_count; @endphp

<div class="relative inline-block text-left action-dropdown">
    <button type="button" class="action-dropdown-trigger w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant dark:text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors" title="Actions">
        <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
    </button>
    <div class="action-dropdown-menu hidden absolute right-0 z-20 mt-1 w-52 rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 shadow-lg py-1">
        <a href="{{ route('students.show', $student->uuid) }}" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
            <i class="fa-solid fa-chart-line w-4 text-on-surface-variant"></i>
            View Dashboard
        </a>
        <a href="{{ route('students.pending', $student->uuid) }}" class="w-full flex items-center justify-between gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
            <span class="flex items-center gap-2.5">
                <i class="fa-solid fa-clock-rotate-left w-4 text-on-surface-variant"></i>
                Pending Quizzes
            </span>
            @if($pending > 0)
                <span class="px-1.5 py-0.5 rounded-full bg-error/10 text-error text-[11px] font-bold">{{ $pending }}</span>
            @endif
        </a>
        <div class="my-1 border-t border-outline-variant/20 dark:border-slate-700"></div>
        <a href="mailto:{{ $student->email }}" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
            <i class="fa-solid fa-envelope w-4 text-on-surface-variant"></i>
            Email Student
        </a>
    </div>
</div>
