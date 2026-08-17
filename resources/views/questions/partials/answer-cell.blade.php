{{-- Answer column --}}
@php
    $answer = $question->answer->first();
    $text = $answer?->description ?: $answer?->expected_answer;
    $text = html_entity_decode(strip_tags($text ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim(preg_replace('/\s+/u', ' ', $text));
@endphp
@if($text !== '')
    <span class="text-sm text-on-surface-variant dark:text-slate-300 line-clamp-2">{{ $text }}</span>
@else
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-error/10 text-error">
        <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
        No answer
    </span>
@endif
