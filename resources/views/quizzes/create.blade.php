@extends('layouts.app')

@section('title', 'Create New Quiz')
@section('meta-description', 'Create and configure a new quiz or assessment.')

@section('page-title', 'Create New Quiz')
@section('page-subtitle', $chain
    ? 'A new quiz for ' . $chain['chapter']->title . '.'
    : 'Create and configure a new quiz or assessment.')

@push('styles')
<style>
    /* ── Two-step wizard ─────────────────────────────────────────────────────
       Both steps live in one form so the whole quiz is submitted at once; only
       the active one is shown. */
    .wizard-step[hidden] { display: none; }

    .wizard-tab .wizard-bullet {
        background: rgba(118, 117, 134, 0.15);
        color: rgb(118, 117, 134);
    }
    .wizard-tab.is-active .wizard-bullet { background: #4648d4; color: #ffffff; }
    .wizard-tab.is-done .wizard-bullet { background: rgba(70, 72, 212, 0.15); color: #4648d4; }
    .wizard-tab.is-active .wizard-label { color: #4648d4; }
    .wizard-rule { background: rgba(118, 117, 134, 0.2); }
    .wizard-rule.is-done { background: #4648d4; }
</style>
@endpush

@section('content')
    @php
        // Through course › chapter the quiz belongs to that chapter; from the
        // sidenav no chapter is offered at all.
        $chainIds = $chain ? [$chain['course'], $chain['chapter']] : [];
        $listRoute = $chain ? route('courses.chapters.quizzes', $chainIds) : route('quizzes');
        $storeRoute = $chain ? route('courses.chapters.quizzes.store', $chainIds) : route('quizzes.store');
    @endphp

    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        @if($chain)
            <a class="hover:text-primary transition-colors" href="{{ route('courses') }}">Courses</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters', $chain['course']) }}">{{ $chain['course']->title }}</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters.dashboard', $chainIds) }}">{{ $chain['chapter']->title }}</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
        @endif
        <a class="hover:text-primary transition-colors" href="{{ $listRoute }}">Quizzes</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Create</span>
    </div>

    {{-- Step indicator --}}
    <div class="flex items-center gap-3 mb-6 max-w-xl">
        <div class="wizard-tab is-active flex items-center gap-2.5" data-tab="1">
            <span class="wizard-bullet w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center transition-colors">1</span>
            <span class="wizard-label text-sm font-semibold text-on-surface-variant dark:text-slate-400 transition-colors">Details</span>
        </div>
        <span class="wizard-rule flex-1 h-0.5 rounded-full transition-colors"></span>
        <div class="wizard-tab flex items-center gap-2.5" data-tab="2">
            <span class="wizard-bullet w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center transition-colors">2</span>
            <span class="wizard-label text-sm font-semibold text-on-surface-variant dark:text-slate-400 transition-colors">Questions &amp; Setup</span>
        </div>
    </div>

    <form id="create-quiz-form" data-ajax-form action="{{ $storeRoute }}" method="POST" class="flex flex-col gap-6">
        @csrf
        {{-- Set by whichever submit button is used --}}
        <input type="hidden" name="status" value="draft">

        {{-- ── Step 1: what the quiz is ──────────────────────────────────── --}}
        <section class="wizard-step" data-step="1">
            <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm p-6 md:p-8 flex flex-col gap-5">
                <div class="border-b border-outline-variant/30 dark:border-slate-700 pb-3">
                    <h3 class="text-base font-bold text-on-surface dark:text-white">General Information</h3>
                    <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-1">
                        The type decides which questions the next step will offer you.
                    </p>
                </div>

                @if($chain)
                    {{-- Fixed by the chain, so it is shown rather than chosen --}}
                    <div class="flex flex-col gap-1.5 max-w-md">
                        <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</span>
                        <div class="w-full bg-surface-container-low/60 dark:bg-slate-900/60 border border-outline-variant/50 dark:border-slate-700 rounded-xl py-2.5 px-4 text-sm text-on-surface dark:text-slate-200 inline-flex items-center gap-2">
                            <i class="fa-solid fa-lock text-[11px] text-outline"></i>
                            {{ $chain['chapter']->title }}
                        </div>
                    </div>
                @endif

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="title">Quiz Title</label>
                    <input id="title" name="title" type="text" placeholder="e.g., Final Examination"
                        class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface dark:text-slate-200 placeholder:text-outline">
                </div>

                <div class="flex flex-col gap-1.5">
                    <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Quiz Type</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach($types as $value => $label)
                            <label class="quiz-type-option relative flex flex-col gap-1 p-4 rounded-2xl border border-outline-variant/60 dark:border-slate-700 cursor-pointer hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5 transition-colors">
                                <input type="radio" name="type" value="{{ $value }}" class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                                <span class="text-sm font-semibold text-on-surface dark:text-slate-200">
                                    {{ ['mixed' => 'Mixed', 'mcqs' => 'Multiple Choice', 'theory' => 'Theory'][$value] ?? $label }}
                                </span>
                                <span class="text-[11px] text-on-surface-variant dark:text-slate-400">
                                    {{ ['mixed' => 'MCQs and theory questions', 'mcqs' => 'MCQ questions only', 'theory' => 'Theory questions only'][$value] ?? '' }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="description">Description</label>
                    <textarea id="description" name="description" data-quill data-quill-no-attachments data-quill-height="140px"
                        placeholder="Provide a brief description of the quiz..."></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6">
                <a href="{{ $listRoute }}"
                    class="px-6 py-2.5 rounded-full border border-outline-variant/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 text-sm font-semibold hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </a>
                <button type="button" id="wizard-next"
                    class="px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md shadow-primary/20 hover:shadow-lg hover:-translate-y-0.5 transition-all inline-flex items-center gap-2">
                    Next: Questions
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </div>
        </section>

        {{-- ── Step 2: what is in it, and how it runs ────────────────────── --}}
        <section class="wizard-step" data-step="2" hidden>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Questions --}}
                <div class="lg:col-span-8">
                    <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm p-6 flex flex-col gap-4 min-h-[420px]">
                        <div class="flex justify-between items-center border-b border-outline-variant/30 dark:border-slate-700 pb-3">
                            <h3 class="text-base font-bold text-on-surface dark:text-white">Questions</h3>
                            <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">
                                <span id="question-count">0</span> selected ·
                                <span id="question-marks">0</span> marks
                            </span>
                        </div>

                        {{-- Type-ahead against the question bank, narrowed to the
                             chapter from the chain and the type from step 1. --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="question-search">
                                Search the question bank
                                <span class="font-normal text-outline">
                                    @if($chain)— {{ $chain['chapter']->title }},@else —@endif
                                    <span id="search-scope">all question types</span>
                                </span>
                            </label>
                            <select id="question-search" placeholder="Start typing a question…"></select>
                        </div>

                        <ul id="question-list" class="flex flex-col gap-2"></ul>

                        <div id="question-empty" class="flex-1 flex flex-col items-center justify-center py-10 px-4 text-center border-2 border-dashed border-outline-variant/50 dark:border-slate-600 rounded-2xl bg-surface-container-lowest/50 dark:bg-slate-900/40">
                            <div class="w-14 h-14 bg-primary-container/20 text-primary rounded-full flex items-center justify-center mb-3">
                                <i class="fa-solid fa-clipboard-question text-xl"></i>
                            </div>
                            <h4 class="text-base font-semibold text-on-surface dark:text-white mb-1">No questions yet</h4>
                            <p class="text-sm text-on-surface-variant dark:text-slate-400 max-w-sm">
                                Search above to add questions, set the marks for each, and drag them into the order students will answer them.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Configuration --}}
                <div class="lg:col-span-4">
                    <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm p-6 flex flex-col gap-4">
                        <h3 class="text-base font-bold text-on-surface dark:text-white border-b border-outline-variant/30 dark:border-slate-700 pb-3">
                            Configuration
                        </h3>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="duration">
                                Duration <span class="font-normal text-outline">(mins)</span>
                            </label>
                            <input id="duration" name="duration" type="number" min="1" max="1440" value="60"
                                class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface dark:text-slate-200">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="passing_score">
                                Passing Score <span class="font-normal text-outline">(marks out of <span id="total-marks">0</span>)</span>
                            </label>
                            <input id="passing_score" name="passing_score" type="number" min="0" step="0.5" value="0"
                                class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface dark:text-slate-200">
                            <p id="passing-score-error" class="text-xs text-error hidden">
                                The passing score cannot be more than the total marks.
                            </p>
                        </div>

                        <div class="flex items-center justify-between py-1">
                            <div>
                                <p class="text-sm font-semibold text-on-surface dark:text-slate-200">Shuffle Questions</p>
                                <p class="text-xs text-on-surface-variant dark:text-slate-400">Randomize question order</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="shuffle_questions" value="1" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-surface-container-high dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 mt-6">
                <button type="button" id="wizard-back"
                    class="px-6 py-2.5 rounded-full border border-outline-variant/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 text-sm font-semibold hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors inline-flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    Back
                </button>
                <div class="flex items-center gap-3">
                    <button type="submit" data-status="draft" data-loading-text="Saving…"
                        class="quiz-submit px-6 py-2.5 rounded-full bg-surface-variant text-on-surface-variant text-sm font-semibold hover:bg-outline-variant transition-all inline-flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        Save Draft
                    </button>
                    <button type="submit" data-status="published" data-loading-text="Publishing…"
                        class="quiz-submit px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md shadow-primary/20 hover:shadow-lg hover:-translate-y-0.5 transition-all inline-flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        Publish Quiz
                    </button>
                </div>
            </div>
        </section>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('cdn/sortable.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('create-quiz-form');
            const status = form.querySelector('input[name="status"]');
            const searchUrl = '{{ route('questions.search') }}';
            @if($chain)
                // The search endpoint matches the chapter by uuid, not by key.
                const chapterId = @json($chain['chapter']->uuid);
            @else
                const chapterId = null;
            @endif

            /* ── Wizard ───────────────────────────────────────────────────
               One form, two panels. Step 1 settles what the quiz is; step 2
               fills it, because the type decides which questions are on offer. */
            const steps = form.querySelectorAll('.wizard-step');
            const tabs = document.querySelectorAll('.wizard-tab');
            const rule = document.querySelector('.wizard-rule');

            function showStep(number) {
                steps.forEach(step => { step.hidden = Number(step.dataset.step) !== number; });
                tabs.forEach(tab => {
                    const index = Number(tab.dataset.tab);
                    tab.classList.toggle('is-active', index === number);
                    tab.classList.toggle('is-done', index < number);
                });
                rule.classList.toggle('is-done', number > 1);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            document.getElementById('wizard-next').addEventListener('click', () => {
                const title = form.querySelector('#title');

                if (!title.value.trim()) {
                    App.showFieldErrors(form, { title: ['Give the quiz a title first.'] });
                    title.focus();
                    return;
                }

                App.clearFieldErrors(form);
                applyType();
                showStep(2);
            });

            document.getElementById('wizard-back').addEventListener('click', () => showStep(1));

            /* ── Question picker ──────────────────────────────────────────
               The quiz does not exist yet, so the picked questions live in the
               page until the form is submitted with them. */
            const list = document.getElementById('question-list');
            const empty = document.getElementById('question-empty');
            const countLabel = document.getElementById('question-count');
            const marksLabel = document.getElementById('question-marks');
            const totalLabel = document.getElementById('total-marks');
            const scopeLabel = document.getElementById('search-scope');
            const passingScore = document.getElementById('passing_score');
            const passingError = document.getElementById('passing-score-error');

            /** The type chosen in step 1: 'mixed', 'mcqs' or 'theory'. */
            function quizType() {
                return form.querySelector('input[name="type"]:checked')?.value ?? 'mixed';
            }

            /** The ids already on the list — never offered by the search again. */
            function chosenIds() {
                return Array.from(list.children).map(row => Number(row.dataset.questionId));
            }

            const picker = new TomSelect('#question-search', {
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                maxOptions: 20,
                loadThrottle: 300,
                // Nothing stays selected: a pick becomes a row below instead.
                maxItems: 1,
                closeAfterSelect: true,
                dropdownParent: 'body',
                load(query, callback) {
                    const url = new URL(searchUrl, window.location.origin);
                    url.searchParams.set('q', query);
                    if (chapterId) url.searchParams.set('chapter', chapterId);

                    // A theory or MCQ quiz only offers questions of that kind.
                    const type = quizType();
                    if (type !== 'mixed') url.searchParams.set('type', type);

                    const chosen = chosenIds();
                    if (chosen.length) url.searchParams.set('exclude', chosen.join(','));

                    fetch(url, {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    })
                        .then(response => response.json())
                        .then(callback)
                        .catch(() => callback());
                },
                render: {
                    option: (data, escape) => `<div class="py-2 px-3">
                            <div class="text-sm text-on-surface dark:text-slate-200">${escape(data.text)}</div>
                            ${data.meta ? `<div class="text-[11px] text-on-surface-variant mt-0.5">${escape(data.meta)}</div>` : ''}
                        </div>`,
                    no_results: () => '<div class="py-2 px-3 text-sm text-on-surface-variant">No questions found.</div>',
                },
                onItemAdd(value) {
                    const data = this.options[value];
                    addQuestion(Number(value), data?.text ?? '', data?.meta ?? '', data?.type ?? '');

                    // Leave the box empty and ready for the next search.
                    this.clear(true);
                    this.clearOptions();
                    this.blur();
                },
            });

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            }

            function addQuestion(id, text, meta, type) {
                if (chosenIds().includes(id)) return;

                const row = document.createElement('li');
                row.className = 'question-row flex items-start gap-3 px-3 py-3 rounded-xl bg-surface-container-low dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700';
                row.dataset.questionId = id;
                row.dataset.questionType = type ?? '';
                row.innerHTML = `
                    <span class="question-drag shrink-0 mt-1.5 cursor-grab text-outline-variant dark:text-slate-500 hover:text-on-surface-variant transition-colors">
                        <i class="fa-solid fa-grip-vertical"></i>
                    </span>
                    <span class="question-position shrink-0 mt-0.5 w-6 h-6 rounded-lg bg-surface-container-high dark:bg-slate-800 text-[11px] font-bold text-on-surface-variant dark:text-slate-300 flex items-center justify-center"></span>
                    <span class="flex-1 min-w-0">
                        <span class="block text-sm text-on-surface dark:text-slate-200">${escapeHtml(text)}</span>
                        ${meta ? `<span class="block text-[11px] text-on-surface-variant dark:text-slate-400 mt-0.5">${escapeHtml(meta)}</span>` : ''}
                    </span>
                    <label class="shrink-0 flex items-center gap-1.5">
                        <span class="text-[11px] font-semibold text-on-surface-variant dark:text-slate-400">Marks</span>
                        <input type="number" class="question-marks w-20 bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-1.5 px-2 text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none" min="0" step="0.5" value="1">
                    </label>
                    <button type="button" class="question-remove shrink-0 mt-1 w-7 h-7 flex items-center justify-center rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>`;

                list.appendChild(row);
                renumber();
            }

            /**
             * Rewrites the positions and the field names. The names carry the
             * row's index, so this must run after every add, remove and drag —
             * the shared submit handler reads the form as it stands.
             */
            function renumber() {
                const rows = Array.from(list.children);
                let total = 0;

                rows.forEach((row, index) => {
                    row.querySelector('.question-position').textContent = index + 1;

                    const marks = row.querySelector('.question-marks');
                    marks.name = `questions[${index}][marks]`;
                    total += Number(marks.value) || 0;

                    let id = row.querySelector('input[type="hidden"]');
                    if (!id) {
                        id = document.createElement('input');
                        id.type = 'hidden';
                        row.appendChild(id);
                    }
                    id.name = `questions[${index}][question_bank_id]`;
                    id.value = row.dataset.questionId;
                });

                const label = Number.isInteger(total) ? total : total.toFixed(1);
                countLabel.textContent = rows.length;
                marksLabel.textContent = label;
                totalLabel.textContent = label;
                empty.classList.toggle('hidden', rows.length > 0);

                // The pass mark has to stay reachable as the total moves.
                passingScore.max = total;
                checkPassingScore();
            }

            function checkPassingScore() {
                const total = Number(totalLabel.textContent) || 0;
                const tooHigh = Number(passingScore.value) > total;

                passingError.classList.toggle('hidden', !tooHigh);
                passingScore.classList.toggle('border-error', tooHigh);

                return !tooHigh;
            }

            /**
             * Applies the type chosen in step 1: the search is narrowed to it,
             * and any question already picked that no longer fits is dropped.
             */
            function applyType() {
                const type = quizType();
                scopeLabel.textContent = {
                    mixed: 'all question types',
                    mcqs: 'MCQ questions',
                    theory: 'theory questions',
                }[type];

                picker.clearOptions();

                if (type === 'mixed') return;

                const dropped = Array.from(list.children)
                    .filter(row => row.dataset.questionType && row.dataset.questionType !== type);

                dropped.forEach(row => row.remove());

                if (dropped.length) {
                    renumber();
                    App.toast('warning', dropped.length === 1
                        ? 'One question was removed — it does not suit this quiz type.'
                        : `${dropped.length} questions were removed — they do not suit this quiz type.`);
                }
            }

            form.querySelectorAll('input[name="type"]').forEach(radio => {
                radio.addEventListener('change', applyType);
            });

            list.addEventListener('click', (e) => {
                if (!e.target.closest('.question-remove')) return;
                e.target.closest('.question-row').remove();
                renumber();
            });

            list.addEventListener('input', (e) => {
                if (e.target.classList.contains('question-marks')) renumber();
            });

            passingScore.addEventListener('input', checkPassingScore);

            new Sortable(list, {
                handle: '.question-drag',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: renumber,
            });

            renumber();

            // Two submit buttons, one form: whichever was pressed decides the
            // status. Set on click so the value is in place before the shared
            // handler builds the FormData.
            form.querySelectorAll('.quiz-submit').forEach(button => {
                button.addEventListener('click', (e) => {
                    if (!checkPassingScore()) {
                        e.preventDefault();
                        passingScore.focus();
                        App.toast('warning', 'The passing score cannot be more than the total marks.');
                        return;
                    }

                    status.value = button.dataset.status;
                });
            });

            form.addEventListener('ajax:success', (event) => {
                const redirect = event.detail?.payload?.redirect;
                if (redirect) window.location.href = redirect;
            });

            // A server-side rejection belongs to step 2's fields, so make sure
            // the step showing them is the one on screen.
            form.addEventListener('ajax:error', () => showStep(2));
        });
    </script>
@endpush
