{{-- Question column — the text, then its chapter --}}
@php
    $text = html_entity_decode(strip_tags($question->question ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim(preg_replace('/\s+/u', ' ', $text));
@endphp
<div class="flex flex-col gap-0.5 min-w-0">
    <span class="font-semibold text-on-surface dark:text-white line-clamp-2">{{ $text ?: '—' }}</span>
    <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
        {{ $question->chapter?->title ?? 'No chapter' }} · added {{ $question->created_at?->diffForHumans() ?? '—' }}
    </span>
</div>
