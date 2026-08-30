{{-- Title column — mirrors the courses grid: name, then one muted meta line --}}
@php
    // Quill stores HTML, so decode entities (&nbsp; and friends) before
    // trimming — otherwise Blade re-escapes them and they show up literally.
    $excerpt = html_entity_decode(strip_tags($chapter->description ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $excerpt = Str::limit(trim(preg_replace('/\s+/u', ' ', $excerpt)), 70);
@endphp
<div class="flex items-center gap-3 min-w-0">
    {{-- The chapter's image, or its number where there is none yet. --}}
    @if($chapter->image)
        <img src="{{ $chapter->image->url }}" alt=""
            class="w-10 h-10 shrink-0 rounded-lg object-cover border border-outline-variant/30 dark:border-slate-700">
    @else
        <span class="w-10 h-10 shrink-0 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">
            {{ $chapter->chapter_number }}
        </span>
    @endif

    <div class="flex flex-col gap-0.5 min-w-0">
    <a href="{{ route('courses.chapters.dashboard', [$courseId, $chapter]) }}"
        class="font-semibold text-on-surface dark:text-white hover:text-primary transition-colors truncate">
        {{ $chapter->title }}
    </a>
    <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
        {{ $excerpt ?: 'No description' }} · updated {{ $chapter->updated_at?->diffForHumans() ?? '—' }}
    </span>
    </div>
</div>
