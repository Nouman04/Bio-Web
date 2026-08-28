{{-- Linked questions on a detail page. Each row shows the question, its kind,
     and the answer — options with the correct one marked for an MCQ. --}}
@php
    $heading = $heading ?? 'Linked Questions';
    $showMarks = $showMarks ?? false;
@endphp

<div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
    <div class="p-6 border-b border-outline-variant/30 dark:border-slate-700 flex items-center justify-between gap-3">
        <h3 class="text-base font-bold text-on-surface dark:text-white">{{ $heading }}</h3>
        <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">
            {{ count($questions) }} {{ Str::plural('question', count($questions)) }}
            @if($showMarks && count($questions))
                · {{ collect($questions)->sum(fn ($q) => (float) $q['marks']) }} marks
            @endif
        </span>
    </div>

    @forelse($questions as $question)
        <div class="px-6 py-5 border-b border-outline-variant/10 dark:border-slate-700/60 last:border-b-0">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-primary">Question {{ $loop->iteration }}</span>
                    <p class="text-sm text-on-surface dark:text-slate-200 mt-1">{{ $question['text'] }}</p>
                </div>
                <div class="flex flex-col items-end gap-1 shrink-0">
                    @if($showMarks)
                        <span class="text-xs font-semibold px-2 py-1 rounded-md bg-surface-container-high dark:bg-slate-900 text-on-surface-variant dark:text-slate-300 whitespace-nowrap">
                            {{ $question['marks'] }} {{ Str::plural('mark', (float) $question['marks']) }}
                        </span>
                    @endif
                    <span class="text-[11px] font-semibold text-on-surface-variant dark:text-slate-400">
                        {{ $question['type'] === 'mcqs' ? 'MCQ' : ($question['type'] === 'theory' ? 'Theory' : $question['type']) }}
                        @if($question['difficulty'] ?? null) · {{ $question['difficulty'] }} @endif
                    </span>
                </div>
            </div>

            @if(! empty($question['options']))
                <ul class="mt-3 flex flex-col gap-1.5">
                    @foreach($question['options'] as $option)
                        <li class="flex items-start gap-2.5 text-sm {{ $option['correct'] ? 'text-tertiary font-semibold' : 'text-on-surface-variant dark:text-slate-400' }}">
                            <span class="w-5 h-5 rounded-full border flex items-center justify-center text-[10px] shrink-0 mt-0.5 {{ $option['correct'] ? 'border-tertiary' : 'border-outline-variant' }}">
                                {{ chr(65 + $loop->index) }}
                            </span>
                            <span>{{ $option['title'] }}</span>
                            @if($option['correct'])
                                <i class="fa-solid fa-circle-check text-tertiary text-xs mt-1"></i>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @elseif(! empty($question['answer']))
                <p class="mt-3 text-sm text-on-surface dark:text-slate-200 bg-primary/5 border border-primary/20 rounded-xl p-3">
                    <span class="text-xs font-bold text-primary">Answer:</span> {{ $question['answer'] }}
                </p>
            @else
                <p class="mt-3 text-xs text-outline">No answer recorded.</p>
            @endif
        </div>
    @empty
        <div class="px-6 py-12 text-center">
            <i class="fa-regular fa-circle-question text-2xl text-outline-variant mb-2"></i>
            <p class="text-sm text-on-surface-variant dark:text-slate-400">No questions are linked yet.</p>
        </div>
    @endforelse
</div>
