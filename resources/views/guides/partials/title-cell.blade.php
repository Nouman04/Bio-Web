{{-- Title column — name, then one muted meta line --}}
@php
    // Quill stores HTML, so decode entities before trimming.
    $excerpt = html_entity_decode(strip_tags($guide->content ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $excerpt = Str::limit(trim(preg_replace('/\s+/u', ' ', $excerpt)), 70);
@endphp
<div class="flex flex-col gap-0.5 min-w-0">
    <span class="font-semibold text-on-surface dark:text-white truncate">{{ $guide->title }}</span>
    <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
        {{ $excerpt ?: 'No content' }} · updated {{ $guide->updated_at?->diffForHumans() ?? '—' }}
    </span>
</div>
