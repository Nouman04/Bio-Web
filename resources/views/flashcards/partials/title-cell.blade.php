{{-- Title column — deck name, then one muted meta line --}}
<div class="flex flex-col gap-0.5 min-w-0">
    <a href="{{ route('flashcards.builder', [$courseId, $chapterId, $flashcard]) }}"
        class="font-semibold text-on-surface dark:text-white hover:text-primary transition-colors truncate">
        {{ $flashcard->title }}
    </a>
    <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
        updated {{ $flashcard->updated_at?->diffForHumans() ?? '—' }}
    </span>
</div>
