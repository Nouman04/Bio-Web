{{-- Actions column for the flashcards DataTable --}}
<div class="relative inline-block text-left action-dropdown">
    <button type="button" class="action-dropdown-trigger w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant dark:text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors" title="Actions">
        <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
    </button>
    <div class="action-dropdown-menu hidden absolute right-0 z-20 mt-1 w-48 rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 shadow-lg py-1">
        <a href="{{ route('flashcards.builder', [$courseId, $chapterId, $flashcard->id]) }}" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
            <i class="fa-solid fa-list-check w-4 text-on-surface-variant"></i>
            Manage Questions
        </a>
        @can('edit flashcard')
            <button type="button"
                onclick="openEditFlashcardModal(this)"
                data-id="{{ $flashcard->id }}"
                data-title="{{ $flashcard->title }}"
                data-chapter-id="{{ $flashcard->chapter_id }}"
                data-source-type="{{ $flashcard->source_key }}"
                data-source-id="{{ $flashcard->flashcardable_id }}"
                data-source-label="{{ $flashcard->source_label }}"
                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
                <i class="fa-solid fa-pen w-4 text-on-surface-variant"></i>
                Edit Details
            </button>
        @endcan
        @can('delete flashcard')
            <div class="my-1 border-t border-outline-variant/20 dark:border-slate-700"></div>
            <button type="button"
                onclick="deleteFlashcard(this)"
                data-id="{{ $flashcard->id }}"
                data-title="{{ $flashcard->title }}"
                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-error hover:bg-error/5 transition-colors">
                <i class="fa-solid fa-trash w-4"></i>
                Delete
            </button>
        @endcan
    </div>
</div>
