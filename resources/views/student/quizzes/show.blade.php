@extends('layouts.student')

@section('title', $quiz->title . ' – Quiz')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .glass-panel {
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.4);
    }
    /* The clock turns amber under two minutes and red under thirty seconds. */
    #quiz-timer.is-warning { color: #b45309; background: rgba(180,83,9,0.08); }
    #quiz-timer.is-critical { color: #b91c1c; background: rgba(185,28,28,0.1); }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto pt-4 pb-16">

    <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-4 flex-wrap">
        <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
        <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
        <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
            class="hover:text-primary transition-colors">{{ $chapter->title }}</a>
        <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
        <span class="text-on-surface font-semibold">Quiz</span>
    </nav>

    {{-- Header: what this is, and how long is left. Sticks to the top of the
         scroll area so the clock and the hand-in button stay reachable. --}}
    <div class="glass-panel rounded-2xl p-6 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 sticky top-0 z-30 shadow-sm">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold text-on-surface">{{ $quiz->title }}</h1>
            <p class="text-on-surface-variant text-sm mt-1">
                {{ $questions->count() }} {{ Str::plural('question', $questions->count()) }}
                &middot; {{ rtrim(rtrim((string) $questions->sum('marks'), '0'), '.') }} marks
                @if($quiz->passing_score !== null)
                    &middot; pass at {{ rtrim(rtrim((string) $quiz->passing_score, '0'), '.') }}
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            @if($secondsRemaining !== null)
                <div id="quiz-timer" class="font-mono text-lg font-bold px-4 py-2 rounded-xl bg-primary/10 text-primary tabular-nums"
                    data-seconds="{{ $secondsRemaining }}">--:--</div>
            @else
                <span class="text-on-surface-variant text-sm">Untimed</span>
            @endif
            <button type="submit" form="quiz-form" id="quiz-submit"
                class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full shadow-sm hover:bg-primary/90 transition-colors inline-flex items-center gap-2">
                Hand in
                <span class="material-symbols-outlined text-[18px]">check</span>
            </button>
        </div>
    </div>

    <form id="quiz-form" data-submit-url="{{ route('student.chapters.quizzes.submit', ['courseId' => $courseId, 'chapterId' => $chapterId, 'quizId' => $quiz->uuid]) }}">
        @csrf
        <div class="flex flex-col gap-5">
            @foreach($questions as $index => $link)
                @php
                    $question = $link->questionBank;
                    $isMcq = $question?->category?->type === 'mcqs';
                @endphp

                <section class="glass-panel rounded-2xl p-6">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <h2 class="text-on-surface font-semibold text-base">
                            <span class="text-primary">{{ $index + 1 }}.</span>
                            {{ $question?->question }}
                        </h2>
                        <span class="text-xs font-semibold text-on-surface-variant bg-surface-container-high px-2 py-1 rounded-full shrink-0">
                            {{ rtrim(rtrim((string) $link->marks, '0'), '.') }} {{ (float) $link->marks === 1.0 ? 'mark' : 'marks' }}
                        </span>
                    </div>

                    @if($isMcq)
                        <div class="flex flex-col gap-2">
                            @foreach($question->options as $option)
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-outline-variant/40 hover:border-primary/40 hover:bg-primary/5 transition-colors cursor-pointer">
                                    <input type="radio" name="answers[{{ $link->id }}]" value="{{ $option->id }}"
                                        class="w-4 h-4 text-primary border-outline-variant focus:ring-primary/40">
                                    <span class="text-sm text-on-surface">{{ $option->title }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        {{-- Written: marked by the instructor after handing in. --}}
                        <textarea name="answers[{{ $link->id }}]" rows="5"
                            placeholder="Write your answer…"
                            class="w-full bg-surface-container-lowest border border-outline-variant/50 rounded-xl p-4 text-sm text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none"></textarea>
                        <p class="text-on-surface-variant text-xs mt-2 flex items-center gap-1.5">
                            <span class="material-symbols-outlined" style="font-size:14px;">person_edit</span>
                            Marked by your instructor after you hand in.
                        </p>
                    @endif
                </section>
            @endforeach
        </div>

        {{-- Handing in early. The header button does the same thing, but this
             is where someone who has just answered the last question looks. --}}
        <div class="glass-panel rounded-2xl p-6 mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-on-surface font-semibold">Finished?</h2>
                <p class="text-on-surface-variant text-sm mt-1">
                    <span id="answered-count">0</span> of {{ $questions->count() }} answered.
                    @if($secondsRemaining !== null)
                        You do not have to use the whole time.
                    @endif
                </p>
            </div>

            <button type="button" id="quiz-submit-footer"
                class="bg-primary text-on-primary text-sm font-semibold py-3 px-8 rounded-full shadow-sm hover:bg-primary/90 transition-colors inline-flex items-center justify-center gap-2 shrink-0">
                Hand in now
                <span class="material-symbols-outlined text-[18px]">check</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const form = document.getElementById('quiz-form');
    const button = document.getElementById('quiz-submit');
    const footerButton = document.getElementById('quiz-submit-footer');
    const timer = document.getElementById('quiz-timer');
    const answeredCount = document.getElementById('answered-count');
    const total = {{ $questions->count() }};

    let handedIn = false;

    /** How many questions have something in them. */
    function answered() {
        const done = new Set();

        form.querySelectorAll('input[type="radio"]:checked').forEach((input) => {
            done.add(input.name);
        });

        form.querySelectorAll('textarea').forEach((box) => {
            if (box.value.trim() !== '') {
                done.add(box.name);
            }
        });

        return done.size;
    }

    function refreshCount() {
        if (answeredCount) {
            answeredCount.textContent = answered();
        }
    }

    form.addEventListener('input', refreshCount);
    form.addEventListener('change', refreshCount);
    refreshCount();

    function submit(auto) {
        if (handedIn) {
            return;
        }

        // Handing in early with blanks is allowed, but not by accident. The
        // timer's own submit never asks — there is nobody left to ask.
        if (! auto) {
            const left = total - answered();

            if (left > 0 && ! window.confirm(
                left === 1
                    ? 'One question is still blank. Hand in anyway?'
                    : left + ' questions are still blank. Hand in anyway?'
            )) {
                return;
            }
        }

        handedIn = true;

        const data = new FormData(form);
        if (auto) {
            data.append('auto', '1');
        }

        [button, footerButton].forEach((b) => b && App.setButtonLoading(b, true));

        fetch(form.dataset.submitUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            body: data,
        })
            .then(response => response.json())
            .then(body => {
                App.toast('success', body.message || 'Handed in.');
                window.location = body.url;
            })
            .catch(() => {
                handedIn = false;
                [button, footerButton].forEach((b) => b && App.setButtonLoading(b, false));
                App.toast('error', 'Could not hand that in. Try again.');
            });
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        submit(false);
    });

    footerButton?.addEventListener('click', () => submit(false));

    // The clock is authoritative on the server; this only shows it, and hands
    // the paper in when it reaches zero.
    if (timer) {
        let left = parseInt(timer.dataset.seconds, 10) || 0;

        const paint = () => {
            const minutes = Math.floor(left / 60);
            const seconds = left % 60;
            timer.textContent = minutes + ':' + String(seconds).padStart(2, '0');
            timer.classList.toggle('is-warning', left <= 120 && left > 30);
            timer.classList.toggle('is-critical', left <= 30);
        };

        paint();

        const tick = setInterval(() => {
            left -= 1;

            if (left <= 0) {
                clearInterval(tick);
                timer.textContent = '0:00';
                App.toast('warning', 'Time is up — handing your paper in.');
                submit(true);
                return;
            }

            paint();
        }, 1000);
    }
})();
</script>
@endpush
