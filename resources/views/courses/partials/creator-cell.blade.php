{{-- Created-by column for the courses DataTable --}}
@if($course->creator)
    <div class="flex items-center gap-2.5 min-w-0">
        <span class="w-7 h-7 rounded-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700 flex items-center justify-center text-[11px] font-bold text-on-surface-variant dark:text-slate-300 shrink-0">
            {{ Str::of($course->creator->name)->trim()->substr(0, 1)->upper() }}
        </span>
        <span class="truncate text-on-surface dark:text-slate-300">{{ $course->creator->name }}</span>
    </div>
@else
    <span class="text-xs text-outline dark:text-slate-500">—</span>
@endif
