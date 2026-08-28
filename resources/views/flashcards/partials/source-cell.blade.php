{{-- Linked-to column: the content this deck was built from --}}
@php
    $labels = [
        App\Models\Note::class => ['Note', 'fa-regular fa-note-sticky'],
        App\Models\VideoLesson::class => ['Video Lesson', 'fa-solid fa-circle-play'],
        App\Models\Guide::class => ['Guide', 'fa-solid fa-book-open'],
        App\Models\Summary::class => ['Summary', 'fa-solid fa-list-check'],
        App\Models\Diagram::class => ['Diagram', 'fa-regular fa-image'],
        App\Models\Topic::class => ['Topic', 'fa-solid fa-tags'],
    ];
    [$label, $icon] = $labels[$flashcard->flashcardable_type] ?? [null, null];
    $source = $flashcard->flashcardable;
    $name = $source ? strip_tags($source->title ?? $source->content ?? '') : null;
@endphp
@if($label)
    <div class="flex flex-col gap-1 min-w-0">
        <span class="inline-flex w-fit items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-primary/8 text-primary dark:bg-primary/20 dark:text-primary-fixed-dim">
            <i class="{{ $icon }} text-[10px]"></i>
            {{ $label }}
        </span>
        @if($name)
            <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">{{ Str::limit(trim(preg_replace('/\s+/u', ' ', $name)), 40) }}</span>
        @endif
    </div>
@else
    <span class="text-xs text-outline dark:text-slate-500">Standalone</span>
@endif
