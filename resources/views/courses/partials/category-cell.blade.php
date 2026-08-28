{{-- Category column for the courses DataTable --}}
@if($course->category)
    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-primary/8 text-primary dark:bg-primary/20 dark:text-primary-fixed-dim">
        {{ $course->category->title }}
    </span>
@else
    <span class="text-xs text-outline dark:text-slate-500">Uncategorised</span>
@endif
