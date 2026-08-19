@extends('layouts.app')

@section('title', 'Question Bank')
@section('meta-description', 'Browse, add and maintain the question bank.')

@section('page-title', 'Question Bank')
@section('page-subtitle', $chain
    ? 'Every question in ' . $chain['chapter']->title . ', and where it is used.'
    : 'Every question in the system, and where it is used.')

@push('styles')
<style>
    /* ── Question bank table ─────────────────────────────────────────────────────
       Same treatment as the courses, chapters and summaries grids: DataTables'
       own chrome folded into the panel so it reads as one quiet surface. */
    .questions-panel { overflow: hidden; }
    #questions-table_wrapper { padding: 0.25rem 0 0; font-size: 0.875rem; }

    /* Header */
    #questions-table thead th {
        padding: 0.875rem 1.5rem;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgb(118, 117, 134);
        background: rgba(242, 244, 246, 0.5);
        border-bottom: 1px solid rgba(118, 117, 134, 0.14);
        white-space: nowrap;
    }
    .dark #questions-table thead th {
        color: rgb(148, 163, 184);
        background: rgba(15, 23, 42, 0.4);
        border-bottom-color: rgb(51, 65, 85);
    }
    #questions-table.dataTable thead th.dt-orderable-asc:hover,
    #questions-table.dataTable thead th.dt-orderable-desc:hover { color: #4648d4; }

    /* DataTables' own `table.dataTable thead>tr>th` rule outranks utility classes,
       so the centred columns are aligned here to keep header and cell in line. */
    #questions-table.dataTable thead > tr > th:nth-child(5),
    #questions-table.dataTable tbody > tr > td:nth-child(5) { text-align: center; }

    /* Body */
    #questions-table tbody td {
        padding: 0.9375rem 1.5rem;
        vertical-align: middle;
        border-top: none;
        border-bottom: 1px solid rgba(118, 117, 134, 0.08);
    }
    .dark #questions-table tbody td { border-bottom-color: rgba(51, 65, 85, 0.6); }
    #questions-table tbody tr:last-child td { border-bottom: none; }
    #questions-table tbody tr { transition: background-color 0.15s ease; }
    #questions-table tbody tr:hover { background: rgba(70, 72, 212, 0.035); }
    .dark #questions-table tbody tr:hover { background: rgba(70, 72, 212, 0.12); }
    #questions-table.dataTable tbody tr.odd,
    #questions-table.dataTable tbody tr.even,
    #questions-table.dataTable tbody tr > .sorting_1 { background: transparent; box-shadow: none; }
    #questions-table tbody td.dt-empty {
        padding: 3.5rem 1.5rem;
        text-align: center;
        color: rgb(118, 117, 134);
    }

    /* Footer chrome: length menu, info line, pagination */
    #questions-table_wrapper .dt-layout-row:last-child {
        padding: 0.875rem 1.5rem;
        border-top: 1px solid rgba(118, 117, 134, 0.12);
        background: rgba(242, 244, 246, 0.35);
    }
    .dark #questions-table_wrapper .dt-layout-row:last-child {
        border-top-color: rgb(51, 65, 85);
        background: rgba(15, 23, 42, 0.35);
    }
    #questions-table_wrapper .dt-layout-row:first-child { padding: 0.875rem 1.5rem 0.25rem; }
    #questions-table_wrapper .dt-length,
    #questions-table_wrapper .dt-info {
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(118, 117, 134);
    }
    .dark #questions-table_wrapper .dt-length,
    .dark #questions-table_wrapper .dt-info { color: rgb(148, 163, 184); }
    #questions-table_wrapper select {
        background: #ffffff;
        border: 1px solid rgba(118, 117, 134, 0.3);
        border-radius: 0.625rem;
        padding: 0.25rem 0.5rem;
        margin: 0 0.375rem;
        outline: none;
    }
    .dark #questions-table_wrapper select {
        background: rgb(15, 23, 42);
        border-color: rgb(51, 65, 85);
        color: rgb(226, 232, 240);
    }
    #questions-table_wrapper .dt-paging .dt-paging-button {
        border: none !important;
        background: transparent !important;
        border-radius: 0.625rem;
        min-width: 2rem;
        padding: 0.3125rem 0.625rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: rgb(118, 117, 134) !important;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    #questions-table_wrapper .dt-paging .dt-paging-button:hover:not(.disabled) {
        background: rgba(70, 72, 212, 0.08) !important;
        color: #4648d4 !important;
    }
    #questions-table_wrapper .dt-paging .dt-paging-button.current {
        background: #4648d4 !important;
        color: #ffffff !important;
    }
    #questions-table_wrapper .dt-paging .dt-paging-button.disabled { opacity: 0.4; }

    /* The shimmer below stands in for DataTables' "Processing..." box */
    #questions-table_wrapper .dt-processing { display: none !important; }

    /* Loading shimmer: real <tr>s inside the table body, so the skeleton
       occupies exactly the rows' space and never covers the header or footer. */
    tr.questions-shimmer-row td > .shimmer-bar + .shimmer-bar { margin-top: 0.4375rem; }

    .filter-card-wrapper {
        display: grid;
        grid-template-rows: 0fr;
        margin-bottom: 0;
        opacity: 0;
        transition: grid-template-rows 0.35s ease, margin-bottom 0.35s ease, opacity 0.3s ease;
    }
    .filter-card-wrapper.is-open {
        grid-template-rows: 1fr;
        margin-bottom: 1.5rem;
        opacity: 1;
    }
    .filter-card-inner {
        overflow: hidden;
        min-height: 0;
    }
    .filter-card-panel {
        transform: translateY(-10px) scale(0.98);
        opacity: 0;
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.3s ease;
    }
    .filter-card-wrapper.is-open .filter-card-panel {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    #filterToggle.is-active {
        color: var(--tw-color-primary, #4648d4);
        border-color: rgba(70, 72, 212, 0.35);
        background: rgba(70, 72, 212, 0.08);
    }
</style>
@endpush

@section('content')
    @php
        // Through course › chapter the bank is locked to that chapter: no
        // chapter filter, and new questions inherit it from the URL.
        $chainIds = $chain ? [$chain['course'], $chain['chapter']] : [];
        $bankRoute = $chain ? route('courses.chapters.questions', $chainIds) : route('questions');
        $dataRoute = $chain ? route('courses.chapters.questions.data', $chainIds) : route('questions.data');
        $createRoute = $chain ? route('courses.chapters.questions.create', $chainIds) : route('questions.create');
        $filtersOpen = request()->hasAny(array_filter([
            'question', $chain ? null : 'chapter', 'linked_type', 'linked_id', 'difficulty', 'category', 'assignment',
        ]));
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
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Question Bank</span>
    </div>

    {{-- Toolbar: Filter toggle + Add button --}}
    <div class="flex justify-end items-center gap-3 mb-4">
        <button type="button" id="filterToggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-800 text-on-surface-variant dark:text-slate-300 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-colors shadow-sm {{ $filtersOpen ? 'is-active' : '' }}"
            title="Toggle Filters">
            <i class="fa-solid fa-filter text-sm"></i>
        </button>
        <a href="{{ $createRoute }}"
            class="flex items-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            Add Questions
        </a>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div class="filter-card-panel glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <form id="questions-filter-form" action="{{ $bankRoute }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="flex flex-col gap-1 lg:col-span-2">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Search</label>
                            <div class="relative group">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-sm"></i>
                                <input name="question" value="{{ $filters['question'] ?? '' }}"
                                    class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none"
                                    placeholder="Search question text..." type="text">
                            </div>
                        </div>
                        @unless($chain)
                            {{-- Only offered from the sidenav; through the chain
                                 the chapter is already decided. --}}
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</label>
                                <select name="chapter" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                    <option value="">All Chapters</option>
                                    @forelse($chapters as $chapter)
                                        <option value="{{ $chapter->uuid }}" {{ ($filters['chapter'] ?? '') == $chapter->uuid ? 'selected' : '' }}>{{ $chapter->title }}</option>
                                    @empty
                                        <option value="" disabled>No chapters yet</option>
                                    @endforelse
                                </select>
                            </div>
                        @endunless
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Assignment</label>
                            <select name="assignment" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Questions</option>
                                <option value="assigned" {{ ($filters['assignment'] ?? '') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="unassigned" {{ ($filters['assignment'] ?? '') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Linked To</label>
                            <select id="filter-linked-type" name="linked_type" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">Any Content</option>
                                @foreach($linkables as $key => $label)
                                    <option value="{{ $key }}" {{ ($filters['linked_type'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Record</label>
                            <select id="filter-linked-id" name="linked_id" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none disabled:opacity-50" disabled>
                                <option value="">Pick a type first</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Question Type</label>
                            <select name="category" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Types</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->uuid }}" {{ ($filters['category'] ?? '') == $category->uuid ? 'selected' : '' }}>{{ $category->type === 'mcqs' ? 'MCQ' : 'Theory' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Difficulty</label>
                            <select name="difficulty" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Levels</option>
                                @foreach($difficulties as $level)
                                    <option value="{{ $level }}" {{ ($filters['difficulty'] ?? '') === $level ? 'selected' : '' }}>{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <button type="button" id="questionsClearFilters" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
                            <i class="fa-solid fa-arrow-rotate-left text-xs"></i> Clear Filters
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary/10 text-primary text-sm font-semibold rounded-lg hover:bg-primary/20 transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Question Bank Data Table (server-side, Yajra DataTables) --}}
    <div class="questions-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto w-full">
            <table id="questions-table" class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="w-1/3">Question</th>
                        <th class="w-1/4">Answer</th>
                        <th>Type &amp; Difficulty</th>
                        <th>Usage</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- One skeleton row, cloned into the table body while a draw is in flight --}}
    <template id="questions-shimmer-row">
        <tr class="questions-shimmer-row" aria-hidden="true">
            <td>
                <span class="shimmer-bar" style="width:80%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:40%"></span>
            </td>
            <td><span class="shimmer-bar" style="width:70%"></span></td>
            <td>
                <span class="shimmer-bar shimmer-pill" style="width:60%"></span>
                <span class="shimmer-bar shimmer-pill" style="width:50%"></span>
            </td>
            <td><span class="shimmer-bar shimmer-pill" style="width:75%"></span></td>
            <td><span class="shimmer-bar shimmer-dots"></span></td>
        </tr>
    </template>

    {{-- One option row, cloned into an MCQ's option list --}}
    <template id="option-row-template">
        <li class="option-row flex items-center gap-2">
            <input type="radio" data-role="correct" class="w-4 h-4 text-primary border-outline-variant focus:ring-primary shrink-0" title="Correct answer">
            <input type="text" data-role="option" class="flex-1 bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none" placeholder="Option text…">
            <button type="button" class="option-remove w-7 h-7 flex items-center justify-center rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors shrink-0">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </li>
    </template>

    {{-- Edit Question Modal --}}
    <div id="edit-question-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeEditQuestionModal()"></div>
        <div class="relative w-full max-w-3xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Edit Question</h3>
                <button type="button" onclick="closeEditQuestionModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="edit-question-form" data-ajax-form data-question-form action="#" method="POST" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                @method('PUT')
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Question</label>
                    <textarea id="edit-question-text" name="question" rows="3" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface resize-y" placeholder="Type the question…"></textarea>
                </div>
                {{-- Chapter, type and difficulty come before the answer, since
                     the type decides what the answer looks like --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</label>
                        <select id="edit-question-chapter" name="chapter_id" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            <option value="">No chapter</option>
                            @foreach($chapters as $chapter)
                                <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Question Type</label>
                        <select id="edit-question-category" name="question_categories_id" class="question-type w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" data-type="{{ $category->type }}">{{ $category->type === 'mcqs' ? 'MCQ' : 'Theory' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Difficulty</label>
                        <select id="edit-question-difficulty" name="difficulty_level" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            <option value="">No difficulty</option>
                            @foreach($difficulties as $level)
                                <option value="{{ $level }}">{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Theory: a written answer --}}
                <div class="answer-theory flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Answer <span class="font-normal text-outline">(Optional)</span></label>
                    <textarea id="edit-question-answer" name="answer" rows="3" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface resize-y" placeholder="Attach the answer…"></textarea>
                </div>

                {{-- MCQ: options, with the correct one selected --}}
                <div class="answer-mcq hidden flex-col gap-2">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">
                        Options <span class="font-normal text-outline">— tick the correct answer</span>
                    </label>
                    <ul class="option-list flex flex-col gap-2"></ul>
                    <button type="button" class="option-add self-start text-xs font-semibold text-primary hover:text-primary/80 transition-colors inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-plus text-[10px]"></i> Add option
                    </button>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditQuestionModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Saving…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/question-form.js') }}"></script>
    <script>
        let questionsTable = null;

        document.addEventListener('DOMContentLoaded', () => {
            const filterToggle = document.getElementById('filterToggle');
            const filterCardWrapper = document.getElementById('filterCardWrapper');

            filterToggle?.addEventListener('click', () => {
                const isOpen = filterCardWrapper?.classList.toggle('is-open');
                filterToggle.classList.toggle('is-active', isOpen);
            });

            const filterForm = document.getElementById('questions-filter-form');

            // Server-side table: paging, ordering and filtering all happen in
            // QuestionController@data, so only the visible page is ever loaded.
            questionsTable = App.dataTable('#questions-table', {
                order: [[0, 'asc']],
                shimmerTemplate: '#questions-shimmer-row',
                language: {
                    emptyTable: 'The question bank is empty.',
                    zeroRecords: 'No questions match these filters.',
                },
                ajax: {
                    url: '{{ $dataRoute }}',
                    data: (params) => {
                        const filters = new FormData(filterForm);
                        // DataTables reserves `search`, so the filter box travels
                        // as search_term and is mapped back on the server.
                        params.search_term = filters.get('question') ?? '';
                        params.chapter = filters.get('chapter') ?? '';
                        params.linked_type = filters.get('linked_type') ?? '';
                        params.linked_id = filters.get('linked_id') ?? '';
                        params.difficulty = filters.get('difficulty') ?? '';
                        params.category = filters.get('category') ?? '';
                        params.assignment = filters.get('assignment') ?? '';
                        return params;
                    },
                },
                columns: [
                    { data: 'question_cell', name: 'question' },
                    { data: 'answer_cell', name: 'answer.description', orderable: false },
                    { data: 'meta_cell', name: 'difficulty_level', orderable: false },
                    { data: 'usage_cell', name: 'usage', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center whitespace-nowrap' },
                ],
            });

            filterForm?.addEventListener('submit', (e) => {
                e.preventDefault();
                questionsTable.ajax.reload();
            });

            document.getElementById('questionsClearFilters')?.addEventListener('click', () => {
                filterForm.reset();
                filterForm.querySelectorAll('select').forEach(select => { select.value = ''; });
                filterForm.querySelector('input[name="question"]').value = '';
                resetLinkedRecords();
                questionsTable.ajax.reload();
            });

            // Dependent picker: choosing a content type loads its records.
            document.getElementById('filter-linked-type')?.addEventListener('change', (e) => {
                loadLinkedRecords(e.target.value);
            });

            // ── Edit submit over AJAX ───────────────────────────────────────
            const editForm = document.getElementById('edit-question-form');

            editForm?.addEventListener('ajax:success', () => {
                closeEditQuestionModal();
                questionsTable.ajax.reload(null, false);
            });
        });

        // Fills the "Record" select with the chosen type's rows.
        function loadLinkedRecords(type, selectedId = null) {
            const recordSelect = document.getElementById('filter-linked-id');
            if (!type) {
                resetLinkedRecords();
                return;
            }

            recordSelect.disabled = true;
            recordSelect.innerHTML = '<option value="">Loading…</option>';

            App.request(`{{ url('questions/linked') }}/${type}`)
                .then(records => {
                    recordSelect.innerHTML = '<option value="">Any record</option>';
                    records.forEach(record => {
                        const option = document.createElement('option');
                        option.value = record.id;
                        option.textContent = record.text;
                        recordSelect.appendChild(option);
                    });
                    recordSelect.value = selectedId ?? '';
                    recordSelect.disabled = false;
                })
                .catch(() => {
                    recordSelect.innerHTML = '<option value="">Could not load records</option>';
                });
        }

        function resetLinkedRecords() {
            const recordSelect = document.getElementById('filter-linked-id');
            if (!recordSelect) return;
            recordSelect.innerHTML = '<option value="">Pick a type first</option>';
            recordSelect.disabled = true;
        }

        // Fills the edit modal from the row's data-* attributes before opening it.
        function openEditQuestionModal(trigger) {
            const form = document.getElementById('edit-question-form');

            form.action = `{{ url('questions') }}/${trigger.dataset.id}`;
            App.clearFieldErrors(form);

            document.getElementById('edit-question-text').value = trigger.dataset.question ?? '';
            document.getElementById('edit-question-answer').value = trigger.dataset.answer ?? '';
            document.getElementById('edit-question-chapter').value = trigger.dataset.chapterId ?? '';
            document.getElementById('edit-question-category').value = trigger.dataset.categoryId ?? '';
            document.getElementById('edit-question-difficulty').value = trigger.dataset.difficulty ?? '';

            // Rebuild the option list from the row, then let the type decide
            // which half of the answer area is shown.
            const list = form.querySelector('.option-list');
            list.replaceChildren();
            delete list.dataset.group;

            (JSON.parse(trigger.dataset.options || '[]')).forEach(option => {
                QuestionForm.addOptionRow(list, { title: option.title, correct: option.correct });
            });

            QuestionForm.applyAnswerMode(form);

            document.getElementById('edit-question-modal-container').classList.remove('hidden');
        }

        function closeEditQuestionModal() {
            document.getElementById('edit-question-modal-container').classList.add('hidden');
        }

        // Deletes through the questions.destroy endpoint, then refreshes the table.
        async function deleteQuestion(trigger) {
            const question = trigger.dataset.question ?? 'this question';

            const confirmed = await App.confirmDelete({
                title: 'Delete question?',
                text: `“${question}” will be removed from the bank and detached everywhere it is used.`,
            });
            if (!confirmed) return;

            try {
                const payload = await App.request(`{{ url('questions') }}/${trigger.dataset.id}`, { method: 'DELETE' });
                App.toast('success', payload.message || 'Question deleted successfully.');
                questionsTable?.ajax.reload(null, false);
            } catch (error) {
                App.toast('error', error.message);
            }
        }
    </script>
@endpush
