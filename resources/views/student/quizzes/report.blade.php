@extends('layouts.student')

@section('title', $quiz->title . ' – Result')

@push('styles')
<style>
    .glass-panel {
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.4);
    }
</style>
@endpush

@php
    $pending = $attempt->status === 'pending_review';
    $expired = $attempt->status === 'expired';
    // This quiz is marked by the student rather than by anyone else: the model
    // answers are shown below and the verdict is theirs, so the header claims
    // no result of its own.
    $selfMarked = $attempt->status === 'self_marked';
    // A paper still with the instructor has no score to show yet, so the
    // header says where it is rather than inventing a result.
    $headline = match (true) {
        $pending => ['Waiting to be marked', 'bg-secondary/10 text-secondary', 'hourglass_top'],
        $selfMarked => ['Mark your own answers', 'bg-primary/10 text-primary', 'fact_check'],
        $expired => ['Time ran out', 'bg-error/10 text-error', 'timer_off'],
        $attempt->passed => ['Passed', 'bg-tertiary/10 text-tertiary', 'check_circle'],
        default => ['Not passed', 'bg-error/10 text-error', 'cancel'],
    };
    $answered = collect($report)->where('answered', true)->count();
@endphp

@section('content')
<div class="max-w-4xl mx-auto pt-4 pb-16">

    <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-4 flex-wrap">
        <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
        @if($chapter)
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $course?->uuid, 'chapterId' => $chapter->uuid]) }}"
                class="hover:text-primary transition-colors">{{ $chapter->title }}</a>
        @endif
        <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
        <span class="text-on-surface font-semibold">Result</span>
    </nav>

    {{-- Summary --}}
    <div class="glass-panel rounded-2xl p-8 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="min-w-0">
                <span class="inline-flex items-center gap-1.5 {{ $headline[1] }} text-xs font-bold px-3 py-1 rounded-full mb-3">
                    <span class="material-symbols-outlined" style="font-size:14px;">{{ $headline[2] }}</span>
                    {{ $headline[0] }}
                </span>
                <h1 class="text-2xl font-bold text-on-surface">{{ $quiz->title }}</h1>
                <p class="text-on-surface-variant text-sm mt-1">
                    Handed in {{ $attempt->submitted_at?->diffForHumans() }}
                    @if($attempt->graded_at)
                        &middot; marked {{ $attempt->graded_at->diffForHumans() }}
                        @if($attempt->grader) by {{ $attempt->grader->name }} @endif
                    @endif
                </p>
            </div>

            @unless($pending || $selfMarked)
                <div class="text-right shrink-0">
                    <div class="text-4xl font-bold text-on-surface tabular-nums">
                        {{ rtrim(rtrim((string) $attempt->earned_marks, '0'), '.') }}<span class="text-on-surface-variant text-2xl">/{{ rtrim(rtrim((string) $attempt->total_marks, '0'), '.') }}</span>
                    </div>
                    <div class="text-on-surface-variant text-sm mt-1">{{ $attempt->percent }}%</div>
                </div>
            @endunless
        </div>

        @unless($pending || $selfMarked)
            <div class="h-2 w-full bg-surface-container-highest rounded-full overflow-hidden mt-6">
                <div class="h-full {{ $attempt->passed ? 'bg-tertiary' : 'bg-error' }} rounded-full transition-all duration-700"
                    style="width: {{ $attempt->percent }}%"></div>
            </div>
        @endunless

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-outline-variant/20">
            <div>
                <div class="text-on-surface-variant text-xs uppercase tracking-wide">Questions</div>
                <div class="text-on-surface font-semibold text-lg">{{ count($report) }}</div>
            </div>
            <div>
                <div class="text-on-surface-variant text-xs uppercase tracking-wide">Answered</div>
                <div class="text-on-surface font-semibold text-lg">{{ $answered }}</div>
            </div>
            <div>
                <div class="text-on-surface-variant text-xs uppercase tracking-wide">Correct</div>
                <div class="text-on-surface font-semibold text-lg">
                    {{ $pending || $selfMarked ? '—' : collect($report)->where('correct', true)->count() }}
                </div>
            </div>
            <div>
                <div class="text-on-surface-variant text-xs uppercase tracking-wide">Pass mark</div>
                <div class="text-on-surface font-semibold text-lg">
                    {{ $selfMarked || $quiz->passing_score === null ? '—' : rtrim(rtrim((string) $quiz->passing_score, '0'), '.') }}
                </div>
            </div>
        </div>

        @if($attempt->feedback)
            <div class="mt-6 p-4 rounded-xl bg-primary/5 border-l-4 border-primary">
                <div class="text-xs font-semibold uppercase tracking-wide text-primary mb-1">Instructor's note</div>
                <p class="text-sm text-on-surface">{{ $attempt->feedback }}</p>
            </div>
        @endif
    </div>

    @if($selfMarked)
        <div class="glass-panel rounded-2xl p-6 mb-6 flex items-start gap-4">
            <span class="material-symbols-outlined text-primary shrink-0">fact_check</span>
            <div>
                <h2 class="text-on-surface font-semibold mb-1">This one is yours to mark</h2>
                <p class="text-on-surface-variant text-sm">
                    Nobody else marks this quiz, so nothing on it is scored and there is no pass mark.
                    Every question is below with what you answered and the answer that was expected —
                    read them side by side and judge your own. Your answers are saved either way, so
                    you can come back to this page whenever you like.
                </p>
            </div>
        </div>
    @endif

    @if($pending)
        <div class="glass-panel rounded-2xl p-6 mb-6 flex items-start gap-4">
            <span class="material-symbols-outlined text-secondary shrink-0">hourglass_top</span>
            <div>
                <h2 class="text-on-surface font-semibold mb-1">Your written answers are with the instructor</h2>
                <p class="text-on-surface-variant text-sm">
                    You will be notified as soon as they are marked, and the full result will appear here.
                    Multiple-choice answers below are already recorded.
                </p>
            </div>
        </div>
    @endif

    {{-- Question by question --}}
    <div class="flex flex-col gap-4">
        @foreach($report as $index => $row)
            @php
                $unmarked = $row['awarded'] === null;
                $tone = $unmarked
                    ? 'border-outline-variant/40'
                    : ($row['correct'] ? 'border-tertiary/40' : 'border-error/30');
            @endphp

            <section class="glass-panel rounded-2xl p-6 border-l-4 {{ $tone }}">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <h2 class="text-on-surface font-semibold text-base flex items-start gap-1.5 min-w-0">
                        <span class="text-primary shrink-0">{{ $index + 1 }}.</span>
                        <span class="min-w-0 [&_p]:mb-2 [&_p:last-child]:mb-0 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_li]:mb-0.5 [&_strong]:font-semibold [&_em]:italic [&_a]:text-primary [&_a]:underline [&_img]:rounded-lg [&_img]:max-w-full">{!! $row['question'] !!}</span>
                    </h2>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full shrink-0 tabular-nums
                        {{ $unmarked ? 'bg-surface-container-high text-on-surface-variant' : ($row['correct'] ? 'bg-tertiary/10 text-tertiary' : 'bg-error/10 text-error') }}">
                        @if($unmarked)
                            {{ $selfMarked ? 'mark yourself' : 'awaiting marking' }}
                        @else
                            {{ rtrim(rtrim((string) $row['awarded'], '0'), '.') }} / {{ rtrim(rtrim((string) $row['marks'], '0'), '.') }}
                        @endif
                    </span>
                </div>

                @if($row['is_mcq'])
                    <div class="flex flex-col gap-2">
                        @foreach($row['options'] as $option)
                            @php
                                $style = match (true) {
                                    $option['correct'] => 'border-tertiary/50 bg-tertiary/5',
                                    $option['picked'] => 'border-error/50 bg-error/5',
                                    default => 'border-outline-variant/30',
                                };
                            @endphp
                            <div class="flex items-center gap-3 p-3 rounded-xl border {{ $style }}">
                                <span class="material-symbols-outlined text-[18px] shrink-0
                                    {{ $option['correct'] ? 'text-tertiary' : ($option['picked'] ? 'text-error' : 'text-outline-variant') }}">
                                    {{ $option['correct'] ? 'check_circle' : ($option['picked'] ? 'cancel' : 'radio_button_unchecked') }}
                                </span>
                                <span class="text-sm text-on-surface">{{ $option['title'] }}</span>
                                @if($option['picked'])
                                    <span class="ml-auto text-xs font-semibold text-on-surface-variant shrink-0">your answer</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-surface-container-lowest border border-outline-variant/20 rounded-xl p-4">
                        <div class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant mb-2">Your answer</div>
                        <p class="text-sm text-on-surface whitespace-pre-line">{{ $row['written'] ?: 'You left this blank.' }}</p>
                    </div>

                    @if($row['feedback'])
                        <div class="mt-3 p-3 rounded-xl bg-primary/5 border-l-4 border-primary">
                            <div class="text-xs font-semibold uppercase tracking-wide text-primary mb-1">Feedback</div>
                            <p class="text-sm text-on-surface">{{ $row['feedback'] }}</p>
                        </div>
                    @endif

                    {{-- The recorded answer, beside what was written, so the
                         two can be read against each other. Held back only while
                         an instructor still has the paper — releasing it then
                         would be handing out the answers before marking. --}}
                    @if(! $unmarked || $selfMarked)
                        @if($row['expected'])
                            <div class="mt-3 p-3 rounded-xl bg-tertiary/5 border-l-4 border-tertiary">
                                <div class="text-xs font-semibold uppercase tracking-wide text-tertiary mb-1">Expected answer</div>
                                <div class="text-sm text-on-surface [&_p]:mb-2 [&_p:last-child]:mb-0 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_li]:mb-0.5 [&_strong]:font-semibold [&_em]:italic [&_a]:text-primary [&_a]:underline [&_img]:rounded-lg [&_img]:max-w-full">{!! $row['expected'] !!}</div>
                            </div>
                        @else
                            <div class="mt-3 p-3 rounded-xl bg-surface-container-high/60 border-l-4 border-outline-variant">
                                <div class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant mb-1">Expected answer</div>
                                <p class="text-sm text-on-surface-variant">
                                    No answer has been recorded for this question yet, so there is nothing to compare against.
                                </p>
                            </div>
                        @endif
                    @endif
                @endif
            </section>
        @endforeach
    </div>

    <div class="flex justify-between items-center mt-8">
        <a href="{{ route('student.quizzes') }}" class="text-primary text-sm font-semibold hover:underline inline-flex items-center gap-1">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            All my attempts
        </a>
        @if($chapter)
            <a href="{{ route('student.chapters.show', ['courseId' => $course?->uuid, 'chapterId' => $chapter->uuid]) }}"
                class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full inline-flex items-center gap-2">
                Back to the chapter
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        @endif
    </div>
</div>
@endsection
