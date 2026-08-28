{{-- Chapter column — a quiz can span chapters, so they are listed --}}
@php $titles = $quiz->chapters->pluck('title')->filter(); @endphp
@if($titles->isEmpty())
    <span class="text-sm text-outline">No chapter</span>
@else
    <span class="text-sm text-on-surface-variant dark:text-slate-400 truncate">{{ $titles->implode(', ') }}</span>
@endif
