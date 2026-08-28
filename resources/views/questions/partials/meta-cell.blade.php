{{-- Type + difficulty column --}}
@php
    $type = $question->category?->type;
    $difficulty = $question->difficulty_level;
    $difficultyClasses = [
        'Easy' => 'bg-tertiary-container/20 text-tertiary',
        'Medium' => 'bg-secondary-container/20 text-secondary',
        'Hard' => 'bg-error/10 text-error',
    ][$difficulty] ?? 'bg-surface-container-low text-on-surface-variant dark:bg-slate-900 dark:text-slate-300';
@endphp
<div class="flex flex-col gap-1 min-w-0">
    <span class="inline-flex w-fit items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-primary/8 text-primary dark:bg-primary/20 dark:text-primary-fixed-dim">
        {{ $type === 'mcqs' ? 'MCQ' : 'Theory' }}
    </span>
    <span class="inline-flex w-fit items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $difficultyClasses }}">
        {{ $difficulty ?: 'Unrated' }}
    </span>
</div>
