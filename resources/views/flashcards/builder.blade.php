@extends('layouts.app')

@section('title', 'Flashcard Questions')
@section('meta-description', 'Attach questions from the bank to a flashcard deck.')

@section('page-title', $flashcard->title)
@section('page-subtitle', 'Step 2 of 2 — add questions from the question bank.')

@push('styles')
<style>
    /* Drag handle affordances for the sortable question list */
    .question-row { transition: box-shadow 0.15s ease, border-color 0.15s ease; }
    .question-row:hover { border-color: rgba(70, 72, 212, 0.35); }
    .question-row.sortable-ghost { opacity: 0.4; }
    .question-row.sortable-chosen { box-shadow: 0 10px 30px rgba(99, 102, 241, 0.12); }
    .question-drag { cursor: grab; }
    .question-drag:active { cursor: grabbing; }
</style>
@endpush

@section('content')
    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6 flex-wrap">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('flashcards', [$course, $chapter]) }}">Flashcards</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">{{ $flashcard->title }}</span>
    </div>

    {{-- Deck summary --}}
    <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-col gap-1 min-w-0">
            <h2 class="text-lg font-bold text-on-surface dark:text-white truncate">{{ $flashcard->title }}</h2>
            <div class="flex items-center gap-2 flex-wrap text-xs text-on-surface-variant dark:text-slate-400">
                @if($flashcard->chapter)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full font-semibold bg-primary/8 text-primary dark:bg-primary/20 dark:text-primary-fixed-dim">
                        {{ $flashcard->chapter->title }}
                    </span>
                @endif
                @if($sourceLabel)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-semibold bg-tertiary-container/20 text-tertiary">
                        <i class="fa-solid fa-link text-[10px]"></i>
                        {{ $sourceLabel }}
                    </span>
                @else
                    <span>Standalone deck</span>
                @endif
            </div>
        </div>
        <a href="{{ route('flashcards', [$course, $chapter]) }}" class="px-5 py-2.5 rounded-full text-sm font-semibold border border-outline-variant/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors">
            Done
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Picker --}}
        <div class="lg:col-span-5">
            <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700 p-6 flex flex-col gap-4">
                <div>
                    <h3 class="text-base font-bold text-on-surface dark:text-white">Add from question bank</h3>
                    <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-1">Search the bank and pick as many as you need.</p>
                </div>

                {{-- The search excludes questions already on this deck --}}
                <div class="question-widget flex flex-col gap-2"
                     data-field-name="question_ids"
                     data-search-url="{{ route('questions.search', ['exclude_type' => 'flashcard', 'exclude_id' => $flashcard]) }}">
                    <select class="question-widget-select" multiple placeholder="Type to search the question bank…" autocomplete="off"></select>
                </div>

                <button type="button" id="add-questions"
                    class="w-full px-5 py-2.5 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center justify-center gap-2"
                    data-loading-text="Adding…">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Add Selected Questions
                </button>
            </div>
        </div>

        {{-- Attached questions --}}
        <div class="lg:col-span-7">
            <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant/20 dark:border-slate-700 bg-surface-container-low/40 dark:bg-slate-900/40 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-on-surface dark:text-white">Questions on this deck</h3>
                        <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-0.5">Drag to reorder — the order is saved automatically.</p>
                    </div>
                    <span id="question-count" class="text-xs font-semibold bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-fixed-dim px-3 py-1 rounded-full">0</span>
                </div>

                <ul id="question-list" class="p-4 flex flex-col gap-2 min-h-[16rem] max-h-[36rem] overflow-y-auto custom-scrollbar"></ul>

                <p id="question-empty" class="hidden px-6 py-12 text-center text-sm text-on-surface-variant dark:text-slate-400">
                    No questions yet — search the bank on the left to add some.
                </p>
            </div>
        </div>
    </div>

    {{-- One skeleton row, cloned into the list while it loads --}}
    <template id="question-shimmer-row">
        <li class="question-row flex items-start gap-3 px-3 py-3 rounded-xl bg-surface-container-low dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700" aria-hidden="true">
            <span class="shimmer-bar shrink-0 mt-0.5" style="width:0.75rem;height:1rem"></span>
            <span class="shimmer-bar shrink-0" style="width:1.5rem;height:1.5rem;border-radius:0.5rem"></span>
            <span class="flex-1 min-w-0 flex flex-col gap-1.5">
                <span class="shimmer-bar" style="width:85%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:40%"></span>
            </span>
            <span class="shimmer-bar shrink-0" style="width:1.25rem;height:1.25rem;border-radius:9999px"></span>
        </li>
    </template>
@endsection

@push('scripts')
    <script src="{{ asset('cdn/sortable.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const list = document.getElementById('question-list');
            const empty = document.getElementById('question-empty');
            const count = document.getElementById('question-count');
            const addButton = document.getElementById('add-questions');
            const widget = document.querySelector('.question-widget');

            const shimmerTemplate = document.getElementById('question-shimmer-row');
            const base = `{{ url("courses/{$course->uuid}/chapters/{$chapter->uuid}/flashcards/{$flashcard->uuid}/questions") }}`;

            // Skeleton rows stand in until the questions land, so the panel does
            // not sit empty (or flash "no questions yet") while loading.
            function showShimmer(rows = 4) {
                empty.classList.add('hidden');
                list.replaceChildren();
                for (let row = 0; row < rows; row++) {
                    list.appendChild(shimmerTemplate.content.cloneNode(true));
                }
            }

            function render(questions) {
                list.replaceChildren();
                count.textContent = questions.length;
                empty.classList.toggle('hidden', questions.length > 0);

                questions.forEach((question, index) => {
                    const item = document.createElement('li');
                    item.className = 'question-row flex items-start gap-3 px-3 py-3 rounded-xl bg-surface-container-low dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700';
                    item.dataset.assessmentId = question.assessment_id;
                    item.innerHTML = `
                        <span class="question-drag shrink-0 mt-0.5 text-outline-variant dark:text-slate-500 hover:text-on-surface-variant transition-colors">
                            <i class="fa-solid fa-grip-vertical"></i>
                        </span>
                        <span class="question-position shrink-0 w-6 h-6 rounded-lg bg-surface-container-high dark:bg-slate-800 text-[11px] font-bold text-on-surface-variant dark:text-slate-300 flex items-center justify-center">${index + 1}</span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm text-on-surface dark:text-slate-200">${escapeHtml(question.text)}</span>
                            ${question.meta ? `<span class="block text-[11px] text-on-surface-variant dark:text-slate-400 mt-0.5">${escapeHtml(question.meta)}</span>` : ''}
                        </span>
                        <button type="button" data-remove="${question.assessment_id}" class="shrink-0 w-7 h-7 flex items-center justify-center rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>`;
                    list.appendChild(item);
                });
            }

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            }

            function refresh() {
                showShimmer();

                return App.request(base)
                    .then(render)
                    .catch(() => {
                        list.replaceChildren();
                        App.toast('error', 'Could not load the deck’s questions.');
                    });
            }

            // Renumber the visible positions after a drag, then persist the order.
            function persistOrder() {
                const ids = Array.from(list.children).map(item => Number(item.dataset.assessmentId));
                Array.from(list.children).forEach((item, index) => {
                    item.querySelector('.question-position').textContent = index + 1;
                });

                App.request(`${base}/order`, { method: 'PUT', body: { assessment_ids: ids } })
                    .catch(() => App.toast('error', 'Could not save the new order.'));
            }

            new Sortable(list, {
                handle: '.question-drag',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: persistOrder,
            });

            addButton.addEventListener('click', async () => {
                const select = widget.querySelector('.question-widget-select');
                const ids = select.tomselect ? select.tomselect.items : [];

                if (!ids.length) {
                    App.toast('warning', 'Pick at least one question first.');
                    return;
                }

                App.setButtonLoading(addButton, true);
                try {
                    const payload = await App.request(base, { method: 'POST', body: { question_ids: ids } });
                    App.toast(payload.added ? 'success' : 'info', payload.added ? payload.message : 'Those questions are already on this deck.');
                    widget.resetQuestions?.();
                    await refresh();
                } catch (error) {
                    App.toast('error', error.message);
                } finally {
                    App.setButtonLoading(addButton, false);
                }
            });

            list.addEventListener('click', async (e) => {
                const button = e.target.closest('button[data-remove]');
                if (!button) return;

                try {
                    const payload = await App.request(`${base}/${button.dataset.remove}`, { method: 'DELETE' });
                    App.toast('success', payload.message || 'Question removed.');
                    // It is eligible for the search again now.
                    widget.refreshQuestionSearch?.();
                    await refresh();
                } catch (error) {
                    App.toast('error', error.message);
                }
            });

            refresh();
        });
    </script>
@endpush
