@extends('public.layouts.app')

@section('title', $quiz->title . ' — MCQ Quiz | Lumina LMS')

@include('public.catalog._styles')

@push('styles')
<style>
    /* ── Answering ───────────────────────────────────────────────────────── */
    .mcq-option { transition: border-color .2s, background-color .2s, color .2s; }
    .mcq-option:not(.is-locked):hover { border-color: rgba(70, 72, 212, .4); background-color: rgba(70, 72, 212, .03); }
    .mcq-option.is-picked { border-color: #4648d4; background-color: rgba(70, 72, 212, .06); }
    .mcq-option.is-picked .mcq-bullet { border-color: #4648d4; color: #4648d4; }

    /* ── After marking ───────────────────────────────────────────────────────
       The options themselves stay neutral; the card says whether it was right,
       and the answer waits behind "See answer". */
    .mcq-option.is-locked { cursor: default; }

    .mcq-verdict { display: none; }
    .is-marked .mcq-verdict { display: inline-flex; }
    .mcq-answer[hidden] { display: none; }

    /* The reveal only exists once the paper has been marked */
    .mcq-reveal { display: none; }
    .is-marked .mcq-reveal { display: inline-flex; }
</style>
@endpush

@section('content')
<main class="max-w-container-max mx-auto px-md md:px-lg py-lg md:py-xl min-h-[calc(100vh-160px)]">
<header class="mb-lg relative z-10">
@include('public.catalog._breadcrumb', ['crumbs' => [
    'Courses' => route('public.courses'),
    $course->title => route('public.course.chapters', $course),
    'Chapter ' . $chapter->chapter_number => route('public.course.chapter.show', [$course, $chapter]),
    'MCQ Quizzes' => route('public.course.chapter.mcqs', [$course, $chapter]),
    $quiz->title => '#',
]])
<h1 class="font-display-lg text-display-lg text-on-surface mt-2">{{ $quiz->title }}</h1>
<div class="flex flex-wrap items-center gap-md mt-3 font-label-md text-label-md text-on-surface-variant">
<span class="flex items-center gap-xs"><span class="material-symbols-outlined text-[18px] text-primary">quiz</span>{{ $questions->count() }} {{ Str::plural('question', $questions->count()) }}</span>
@if($quiz->duration)
<span class="flex items-center gap-xs"><span class="material-symbols-outlined text-[18px] text-primary">timer</span>{{ $quiz->duration }} minutes</span>
@endif
@if($quiz->passing_score !== null)
<span class="flex items-center gap-xs"><span class="material-symbols-outlined text-[18px] text-primary">flag</span>Pass mark {{ rtrim(rtrim(number_format((float) $quiz->passing_score, 2, '.', ''), '0'), '.') }}</span>
@endif
</div>
</header>

@if($questions->isEmpty())
<div class="glass-panel rounded-xl p-lg text-center relative z-10">
<span class="material-symbols-outlined text-primary text-[40px] mb-2">quiz</span>
<h2 class="font-headline-md text-headline-md text-on-surface mb-2">This quiz has no questions yet</h2>
<a href="{{ route('public.course.chapter.mcqs', [$course, $chapter]) }}" class="font-label-md text-primary hover:text-primary-container">Back to all quizzes</a>
</div>
@else

<!-- Score panel, filled in once the paper is marked -->
<section id="score-panel" class="glass-panel rounded-xl p-md md:p-lg mb-lg relative z-10 hidden">
<div class="flex flex-col md:flex-row md:items-center gap-lg">
<div class="flex items-center gap-md">
<div id="score-badge" class="w-20 h-20 rounded-full flex flex-col items-center justify-center shrink-0 bg-primary/10 text-primary">
<span id="score-percent" class="font-headline-md text-headline-md leading-none">0%</span>
</div>
<div>
<h2 id="score-heading" class="font-headline-md text-headline-md text-on-surface">Your score</h2>
<p id="score-detail" class="font-body-md text-body-md text-on-surface-variant mt-xs"></p>
</div>
</div>
<div class="md:ml-auto flex items-center gap-sm">
<button id="quiz-retry" type="button"
    class="flex items-center gap-xs py-sm px-md rounded-full font-label-md text-label-md border border-outline-variant text-on-surface-variant hover:text-primary hover:border-primary/40 transition-all active:scale-95">
<span class="material-symbols-outlined text-[18px]">restart_alt</span>
                    Try again
                </button>
</div>
</div>
</section>

<form id="mcq-form" class="flex flex-col gap-lg relative z-10">
@foreach($questions as $question)
<section class="mcq-question glass-panel rounded-xl p-md md:p-lg" data-question="{{ $question['id'] }}">
<div class="flex items-start justify-between gap-md">
<div>
<span class="font-label-md text-label-md text-primary tracking-widest uppercase mb-2 block">Question {{ $loop->iteration }} of {{ $questions->count() }}</span>
<h2 class="font-headline-lg-mobile md:text-headline-lg text-on-surface max-w-3xl">{{ $question['text'] }}</h2>
</div>
<div class="flex flex-col items-end gap-2 shrink-0">
<span class="font-label-sm text-label-sm text-on-surface-variant bg-surface-container-high px-2 py-1 rounded-md whitespace-nowrap">{{ $question['marks'] }} {{ Str::plural('mark', (float) $question['marks']) }}</span>
<span class="mcq-verdict items-center gap-xs px-3 py-1.5 rounded-full font-label-md text-label-md whitespace-nowrap"></span>
</div>
</div>

<!-- Options -->
<div class="grid grid-cols-1 gap-md mt-md">
@forelse($question['options'] as $option)
<button type="button" data-option="{{ $option['id'] }}"
    class="mcq-option text-left w-full p-md rounded-lg border-2 border-outline-variant bg-surface-container-lowest flex items-start gap-md group">
<span class="mcq-bullet flex-shrink-0 w-8 h-8 rounded-full border-2 border-outline-variant flex items-center justify-center font-label-md text-label-md text-secondary transition-colors">{{ chr(65 + $loop->index) }}</span>
<span class="font-body-lg text-body-lg text-on-surface">{{ $option['title'] }}</span>
</button>
@empty
<p class="font-body-md text-body-md text-on-surface-variant">No options were recorded for this question.</p>
@endforelse
</div>

<!-- Only offered once the paper has been marked -->
<div class="mt-md pt-md border-t border-glass-stroke mcq-reveal-wrap">
<button type="button" class="mcq-reveal font-label-md text-label-md text-primary hover:text-primary-container items-center gap-xs transition-colors">
<span class="material-symbols-outlined text-[18px]">visibility</span>
                    See answer
                </button>
<p class="mcq-answer mt-sm font-body-md text-body-md text-on-surface bg-primary/5 border border-primary/20 rounded-lg p-sm" hidden></p>
</div>
</section>
@endforeach

<!-- Submit -->
<div id="submit-bar" class="glass-panel rounded-xl p-md flex flex-col sm:flex-row items-center justify-between gap-md">
<p id="answered-count" class="font-label-md text-label-md text-on-surface-variant">0 of {{ $questions->count() }} answered</p>
<button id="quiz-submit" type="submit"
    class="w-full sm:w-auto flex items-center justify-center gap-sm py-sm px-lg rounded-full font-label-md text-label-md bg-primary text-on-primary hover:bg-surface-tint shadow-[0_4px_14px_0_rgba(70,72,212,0.39)] transition-all active:scale-95 disabled:opacity-50 disabled:pointer-events-none">
<span class="material-symbols-outlined">assignment_turned_in</span>
                Submit answers
            </button>
</div>
</form>

<div class="mt-lg text-center relative z-10">
<a href="{{ route('public.course.chapter.mcqs', [$course, $chapter]) }}"
    class="font-label-md text-label-md text-secondary hover:text-on-surface transition-colors">Back to all quizzes</a>
</div>
@endif
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('mcq-form');
        if (!form) return;

        const questions = Array.from(form.querySelectorAll('.mcq-question'));
        const submitButton = document.getElementById('quiz-submit');
        const answeredCount = document.getElementById('answered-count');
        const scorePanel = document.getElementById('score-panel');
        const submitUrl = '{{ route('public.course.chapter.mcqs.submit', [$course, $chapter, $quiz]) }}';
        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        // questionId -> chosen optionId
        const picked = {};
        let marked = false;

        function refreshCount() {
            const answered = Object.keys(picked).length;
            answeredCount.textContent = `${answered} of ${questions.length} answered`;
            submitButton.disabled = answered === 0;
        }

        // ── Choosing an option ──────────────────────────────────────────────
        form.addEventListener('click', (e) => {
            const option = e.target.closest('.mcq-option');
            if (!option || marked) return;

            const question = option.closest('.mcq-question');
            question.querySelectorAll('.mcq-option').forEach(o => o.classList.remove('is-picked'));
            option.classList.add('is-picked');
            picked[question.dataset.question] = Number(option.dataset.option);
            refreshCount();
        });

        // ── Revealing an answer, after marking ──────────────────────────────
        form.addEventListener('click', (e) => {
            const reveal = e.target.closest('.mcq-reveal');
            if (!reveal) return;

            const answer = reveal.parentElement.querySelector('.mcq-answer');
            answer.hidden = !answer.hidden;
            reveal.querySelector('span').textContent = answer.hidden ? 'visibility' : 'visibility_off';
            reveal.lastChild.textContent = answer.hidden ? ' See answer ' : ' Hide answer ';
        });

        // ── Submitting ──────────────────────────────────────────────────────
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (marked) return;

            submitButton.disabled = true;
            submitButton.querySelector('span').textContent = 'hourglass_top';

            try {
                const response = await fetch(submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ answers: picked }),
                });

                if (!response.ok) throw new Error('Could not mark this quiz.');

                applyResults(await response.json());
            } catch (error) {
                submitButton.disabled = false;
                submitButton.querySelector('span').textContent = 'assignment_turned_in';
                alert(error.message);
            }
        });

        function applyResults(payload) {
            marked = true;

            questions.forEach(question => {
                const result = payload.results[question.dataset.question];
                if (!result) return;

                question.classList.add('is-marked');

                // Options stay as they were — only locked, with the reader's
                // own pick still marked so they can see what they chose.
                question.querySelectorAll('.mcq-option').forEach(option => option.classList.add('is-locked'));

                // The card itself says right or wrong.
                const verdict = question.querySelector('.mcq-verdict');
                if (result.correct) {
                    verdict.innerHTML = '<span class="material-symbols-outlined text-[18px]">check_circle</span> Right · +'
                        + result.awarded;
                    verdict.style.background = 'rgba(22, 121, 74, .12)';
                    verdict.style.color = '#16794a';
                } else {
                    verdict.innerHTML = '<span class="material-symbols-outlined text-[18px]">cancel</span> '
                        + (result.answered ? 'Wrong · 0 of ' + result.marks : 'Not answered · 0 of ' + result.marks);
                    verdict.style.background = 'rgba(186, 26, 26, .12)';
                    verdict.style.color = '#ba1a1a';
                }

                const answer = question.querySelector('.mcq-answer');
                if (answer) answer.textContent = result.answer || 'No answer was recorded for this question.';
            });

            // Score panel
            document.getElementById('score-percent').textContent = payload.percent + '%';
            document.getElementById('score-heading').textContent =
                payload.passed === null ? 'Your score'
                    : (payload.passed ? 'Passed' : 'Not passed yet');
            document.getElementById('score-detail').textContent =
                `${payload.earned} of ${payload.total} marks · ${payload.correct_count} of ${payload.question_count} correct`
                + (payload.pass_mark !== null ? ` · pass mark ${payload.pass_mark}` : '');

            const badge = document.getElementById('score-badge');
            badge.className = 'w-20 h-20 rounded-full flex flex-col items-center justify-center shrink-0 '
                + (payload.passed === false ? 'bg-error/10 text-error' : 'bg-primary/10 text-primary');

            scorePanel.classList.remove('hidden');
            document.getElementById('submit-bar').classList.add('hidden');
            scorePanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        document.getElementById('quiz-retry').addEventListener('click', () => {
            marked = false;
            Object.keys(picked).forEach(key => delete picked[key]);

            questions.forEach(question => {
                question.classList.remove('is-marked');
                question.querySelectorAll('.mcq-option').forEach(option => {
                    option.classList.remove('is-locked', 'is-picked');
                });
                const verdict = question.querySelector('.mcq-verdict');
                if (verdict) { verdict.innerHTML = ''; verdict.style.background = ''; verdict.style.color = ''; }

                const answer = question.querySelector('.mcq-answer');
                if (answer) { answer.hidden = true; answer.textContent = ''; }

                // The reveal toggle goes back to its unopened label.
                const reveal = question.querySelector('.mcq-reveal');
                if (reveal) {
                    reveal.querySelector('span').textContent = 'visibility';
                    reveal.lastChild.textContent = ' See answer ';
                }
            });

            scorePanel.classList.add('hidden');
            document.getElementById('submit-bar').classList.remove('hidden');
            submitButton.querySelector('span').textContent = 'assignment_turned_in';
            refreshCount();
            questions[0]?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

        refreshCount();
    });
</script>
@endpush
