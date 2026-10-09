{{-- Question column — the text, then its chapter --}}
<div class="flex flex-col gap-0.5 min-w-0">
    <span class="font-semibold text-on-surface dark:text-white line-clamp-2 break-words">{{ $question->plain_text ?: '—' }}</span>
    <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
        {{ $question->chapter?->title ?? 'No chapter' }} · added {{ $question->created_at?->diffForHumans() ?? '—' }}
    </span>
</div>
