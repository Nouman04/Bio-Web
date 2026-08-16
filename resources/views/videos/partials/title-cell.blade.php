{{-- Title column — name, then one muted meta line --}}
@php
    // Quill stores HTML, so decode entities before trimming.
    $excerpt = html_entity_decode(strip_tags($video->description ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $excerpt = Str::limit(trim(preg_replace('/\s+/u', ' ', $excerpt)), 60);
    $questions = $video->questionables_count ?? 0;
@endphp
<div class="flex items-center gap-3 min-w-0">
    <span class="w-10 h-10 shrink-0 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
        <i class="fa-solid fa-circle-play"></i>
    </span>
    <div class="flex flex-col gap-0.5 min-w-0">
        @if($video->video_url)
            <a href="{{ $video->video_url }}" target="_blank" rel="noopener"
                class="font-semibold text-on-surface dark:text-white hover:text-primary transition-colors truncate">
                {{ $video->title }}
            </a>
        @else
            <span class="font-semibold text-on-surface dark:text-white truncate">{{ $video->title }}</span>
        @endif
        <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
            {{ $excerpt ?: 'No description' }} · {{ $questions }} {{ Str::plural('question', $questions) }}
        </span>
    </div>
</div>
