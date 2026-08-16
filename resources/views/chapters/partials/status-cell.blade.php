{{-- Status column — same pill shape as the courses grid's category chip --}}
@if($chapter->status === 'Published')
    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-tertiary-container/20 text-tertiary">
        Published
    </span>
@else
    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-surface-container-low text-on-surface-variant dark:bg-slate-900 dark:text-slate-300">
        Draft
    </span>
@endif
