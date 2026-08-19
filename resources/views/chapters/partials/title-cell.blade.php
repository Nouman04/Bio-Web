{{-- Title column — mirrors the courses grid: name, then one muted meta line --}}
@php
    // Quill stores HTML, so decode entities (&nbsp; and friends) before
    // trimming — otherwise Blade re-escapes them and they show up literally.
    $excerpt = html_entity_decode(strip_tags($chapter->description ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $excerpt = Str::limit(trim(preg_replace('/\s+/u', ' ', $excerpt)), 70);
@endphp
<div class="flex flex-col gap-0.5 min-w-0">
    <a href="{{ route('courses.chapters.dashboard', [$chapter->course_id, $chapter]) }}"
        class="font-semibold text-on-surface dark:text-white hover:text-primary transition-colors truncate">
        {{ $chapter->title }}
    </a>
    <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
        {{ $excerpt ?: 'No description' }} · updated {{ $chapter->updated_at?->diffForHumans() ?? '—' }}
    </span>
</div>
