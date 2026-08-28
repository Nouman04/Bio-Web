{{-- Note type column — summary notes also name the summary behind them --}}
@php
    $badges = [
        'exam_notes' => ['Exam Notes', 'fa-solid fa-file-pen', 'bg-primary/8 text-primary dark:bg-primary/20 dark:text-primary-fixed-dim'],
        'summary' => ['Summary Notes', 'fa-solid fa-list-check', 'bg-tertiary-container/20 text-tertiary'],
    ];
    [$label, $icon, $classes] = $badges[$note->type] ?? ['—', 'fa-regular fa-note-sticky', 'bg-surface-container-low text-on-surface-variant'];
@endphp
<div class="flex flex-col gap-1 min-w-0">
    <span class="inline-flex w-fit items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $classes }}">
        <i class="{{ $icon }} text-[10px]"></i>
        {{ $label }}
    </span>
    @if($note->type === 'summary')
        <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
            {{ $note->summary?->title ?? 'No summary linked' }}
        </span>
    @endif
</div>
