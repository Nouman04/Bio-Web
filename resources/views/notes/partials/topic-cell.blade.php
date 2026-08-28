{{-- Topic column --}}
@if($note->topic)
    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-tertiary-container/20 text-tertiary">
        {{ $note->topic->title }}
    </span>
@else
    <span class="text-xs text-outline dark:text-slate-500">No topic</span>
@endif
