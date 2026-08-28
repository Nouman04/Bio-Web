{{-- Actions column for the question bank DataTable --}}
@php
    $answer = $question->answer->first();
    // Options travel with the row so the edit modal can rebuild an MCQ.
    $options = $question->options->map(fn ($option) => [
        'id' => $option->id,
        'title' => $option->title,
        'correct' => $answer && $answer->question_option_id === $option->id,
    ])->values();
@endphp
<div class="relative inline-block text-left action-dropdown">
    <button type="button" class="action-dropdown-trigger w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant dark:text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors" title="Actions">
        <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
    </button>
    <div class="action-dropdown-menu hidden absolute right-0 z-20 mt-1 w-52 rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 shadow-lg py-1">
        @can('edit question')
            <button type="button"
                onclick="openEditQuestionModal(this)"
                data-id="{{ $question->uuid }}"
                data-chapter-id="{{ $question->chapter_id }}"
                data-category-id="{{ $question->question_categories_id }}"
                data-difficulty="{{ $question->difficulty_level }}"
                data-question="{{ $question->question }}"
                data-answer="{{ $answer?->description }}"
                data-options="{{ $options->toJson() }}"
                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
                <i class="fa-solid fa-pen w-4 text-on-surface-variant"></i>
                Edit
            </button>
        @endcan
        @can('delete question')
            <div class="my-1 border-t border-outline-variant/20 dark:border-slate-700"></div>
            <button type="button"
                onclick="deleteQuestion(this)"
                data-id="{{ $question->uuid }}"
                data-question="{{ Str::limit(strip_tags($question->question), 40) }}"
                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-error hover:bg-error/5 transition-colors">
                <i class="fa-solid fa-trash w-4"></i>
                Delete
            </button>
        @endcan
    </div>
</div>
