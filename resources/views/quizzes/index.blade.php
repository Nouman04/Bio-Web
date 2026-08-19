@extends('layouts.app')

@section('title', 'Quiz Management')
@section('meta-description', 'Manage and organize quizzes and assessments.')

@section('page-title', 'Quiz Management')
@section('page-subtitle', $chain
    ? 'Quizzes for ' . $chain['chapter']->title . '.'
    : 'Manage and organize quizzes and assessments.')

@push('styles')
<style>
    /* ── Quizzes table ──────────────────────────────────────────────────────
       Same treatment as the courses and chapters grids: DataTables' own chrome
       folded into the panel so it reads as one quiet surface. */
    .quizzes-panel { overflow: hidden; }
    #quizzes-table_wrapper { padding: 0.25rem 0 0; font-size: 0.875rem; }

    /* Header */
    #quizzes-table thead th {
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
    .dark #quizzes-table thead th {
        color: rgb(148, 163, 184);
        background: rgba(15, 23, 42, 0.4);
        border-bottom-color: rgb(51, 65, 85);
    }
    #quizzes-table.dataTable thead th.dt-orderable-asc:hover,
    #quizzes-table.dataTable thead th.dt-orderable-desc:hover { color: #4648d4; }

    /* DataTables' own `table.dataTable thead>tr>th` rule outranks utility classes,
       so the centred columns are aligned here to keep header and cell in line. */
    #quizzes-table.dataTable thead > tr > th:nth-child(4),
    #quizzes-table.dataTable tbody > tr > td:nth-child(4),
    #quizzes-table.dataTable thead > tr > th:nth-child(5),
    #quizzes-table.dataTable tbody > tr > td:nth-child(5),
    #quizzes-table.dataTable thead > tr > th:nth-child(6),
    #quizzes-table.dataTable tbody > tr > td:nth-child(6) { text-align: center; }

    /* Keep the sort arrows tight against the centred header labels */
    #quizzes-table.dataTable thead > tr > th:nth-child(4) span.dt-column-order,
    #quizzes-table.dataTable thead > tr > th:nth-child(5) span.dt-column-order { position: static; }

    /* Body */
    #quizzes-table tbody td {
        padding: 0.9375rem 1.5rem;
        vertical-align: middle;
        border-top: none;
        border-bottom: 1px solid rgba(118, 117, 134, 0.08);
    }
    .dark #quizzes-table tbody td { border-bottom-color: rgba(51, 65, 85, 0.6); }
    #quizzes-table tbody tr:last-child td { border-bottom: none; }
    #quizzes-table tbody tr { transition: background-color 0.15s ease; }
    #quizzes-table tbody tr:hover { background: rgba(70, 72, 212, 0.035); }
    .dark #quizzes-table tbody tr:hover { background: rgba(70, 72, 212, 0.12); }
    #quizzes-table.dataTable tbody tr.odd,
    #quizzes-table.dataTable tbody tr.even,
    #quizzes-table.dataTable tbody tr > .sorting_1 { background: transparent; box-shadow: none; }
    #quizzes-table tbody td.dt-empty {
        padding: 3.5rem 1.5rem;
        text-align: center;
        color: rgb(118, 117, 134);
    }

    /* Footer chrome: length menu, info line, pagination */
    #quizzes-table_wrapper .dt-layout-row:last-child {
        padding: 0.875rem 1.5rem;
        border-top: 1px solid rgba(118, 117, 134, 0.12);
        background: rgba(242, 244, 246, 0.35);
    }
    .dark #quizzes-table_wrapper .dt-layout-row:last-child {
        border-top-color: rgb(51, 65, 85);
        background: rgba(15, 23, 42, 0.35);
    }
    #quizzes-table_wrapper .dt-layout-row:first-child { padding: 0.875rem 1.5rem 0.25rem; }
    #quizzes-table_wrapper .dt-length,
    #quizzes-table_wrapper .dt-info {
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(118, 117, 134);
    }
    .dark #quizzes-table_wrapper .dt-length,
    .dark #quizzes-table_wrapper .dt-info { color: rgb(148, 163, 184); }
    #quizzes-table_wrapper select {
        background: #ffffff;
        border: 1px solid rgba(118, 117, 134, 0.3);
        border-radius: 0.625rem;
        padding: 0.25rem 0.5rem;
        margin: 0 0.375rem;
        outline: none;
    }
    .dark #quizzes-table_wrapper select {
        background: rgb(15, 23, 42);
        border-color: rgb(51, 65, 85);
        color: rgb(226, 232, 240);
    }
    #quizzes-table_wrapper .dt-paging .dt-paging-button {
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
    #quizzes-table_wrapper .dt-paging .dt-paging-button:hover:not(.disabled) {
        background: rgba(70, 72, 212, 0.08) !important;
        color: #4648d4 !important;
    }
    #quizzes-table_wrapper .dt-paging .dt-paging-button.current {
        background: #4648d4 !important;
        color: #ffffff !important;
    }
    #quizzes-table_wrapper .dt-paging .dt-paging-button.disabled { opacity: 0.4; }

    /* The shimmer below stands in for DataTables' "Processing..." box */
    #quizzes-table_wrapper .dt-processing { display: none !important; }

    /* Loading shimmer: real <tr>s inside the table body, so the skeleton
       occupies exactly the rows' space and never covers the header or footer. */
    tr.quizzes-shimmer-row td > .shimmer-bar + .shimmer-bar { margin-top: 0.4375rem; }

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
        // Through course › chapter the listing is limited to that chapter, and
        // a new quiz inherits it from the URL.
        $chainIds = $chain ? [$chain['course'], $chain['chapter']] : [];
        $listRoute = $chain ? route('courses.chapters.quizzes', $chainIds) : route('quizzes');
        $dataRoute = $chain ? route('courses.chapters.quizzes.data', $chainIds) : route('quizzes.data');
        $createRoute = $chain ? route('courses.chapters.quizzes.create', $chainIds) : route('quizzes.create');
        $filtersOpen = request()->hasAny(array_filter([
            'search', $chain ? null : 'chapter', 'status', 'date_from', 'date_to',
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
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Quiz Management</span>
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
            Create Quiz
        </a>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div class="filter-card-panel glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <form id="quizzes-filter-form" action="{{ $listRoute }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="flex flex-col gap-1 {{ $chain ? 'lg:col-span-2' : '' }}">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Search</label>
                            <div class="relative group">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-sm"></i>
                                <input name="search" value="{{ $filters['search'] ?? '' }}"
                                    class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none"
                                    placeholder="Search by title..." type="text">
                            </div>
                        </div>
                        @unless($chain)
                            {{-- Only offered from the sidenav; through the chain
                                 the chapter is already decided. --}}
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</label>
                                <select name="chapter" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                    <option value="">All Chapters</option>
                                    @forelse($chapters as $chapterOption)
                                        <option value="{{ $chapterOption->uuid }}" {{ ($filters['chapter'] ?? '') == $chapterOption->uuid ? 'selected' : '' }}>{{ $chapterOption->title }}</option>
                                    @empty
                                        <option value="" disabled>No chapters yet</option>
                                    @endforelse
                                </select>
                            </div>
                        @endunless
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Status</label>
                            <select name="status" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Status</option>
                                <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="closed" {{ ($filters['status'] ?? '') === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Created Between</label>
                            <div class="flex items-center gap-2">
                                <input name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}"
                                    class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <input name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}"
                                    class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <button type="button" id="quizzesClearFilters" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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

    {{-- Quiz List Data Table (server-side, Yajra DataTables) --}}
    <div class="quizzes-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto w-full">
            <table id="quizzes-table" class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="w-1/3">Quiz</th>
                        <th>Chapter</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th>Responses</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- One skeleton row, cloned into the table body while a draw is in flight --}}
    <template id="quizzes-shimmer-row">
        <tr class="quizzes-shimmer-row" aria-hidden="true">
            <td>
                <span class="shimmer-bar" style="width:55%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:35%"></span>
            </td>
            <td><span class="shimmer-bar" style="width:60%"></span></td>
            <td>
                <span class="shimmer-bar" style="width:50%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:35%"></span>
            </td>
            <td><span class="shimmer-bar shimmer-pill" style="width:70%"></span></td>
            <td><span class="shimmer-bar shimmer-chip"></span></td>
            <td><span class="shimmer-bar shimmer-dots"></span></td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script>
        let quizzesTable = null;

        document.addEventListener('DOMContentLoaded', () => {
            const filterToggle = document.getElementById('filterToggle');
            const filterCardWrapper = document.getElementById('filterCardWrapper');

            filterToggle?.addEventListener('click', () => {
                const isOpen = filterCardWrapper?.classList.toggle('is-open');
                filterToggle.classList.toggle('is-active', isOpen);
            });

            const filterForm = document.getElementById('quizzes-filter-form');

            // Server-side table: paging, ordering and filtering all happen in
            // QuizController@data, so only the visible page is ever loaded.
            quizzesTable = App.dataTable('#quizzes-table', {
                order: [[2, 'desc']],
                shimmerTemplate: '#quizzes-shimmer-row',
                language: {
                    emptyTable: 'No quizzes yet.',
                    zeroRecords: 'No quizzes match these filters.',
                },
                ajax: {
                    url: '{{ $dataRoute }}',
                    data: (params) => {
                        const filters = new FormData(filterForm);
                        // DataTables reserves `search`, so the filter box travels
                        // as search_term and is mapped back on the server.
                        params.search_term = filters.get('search') ?? '';
                        params.chapter = filters.get('chapter') ?? '';
                        params.status = filters.get('status') ?? '';
                        params.date_from = filters.get('date_from') ?? '';
                        params.date_to = filters.get('date_to') ?? '';
                        return params;
                    },
                },
                columns: [
                    { data: 'title_cell', name: 'title' },
                    { data: 'chapter_cell', name: 'chapter', orderable: false, searchable: false },
                    { data: 'date_cell', name: 'created_at' },
                    { data: 'status_cell', name: 'status', className: 'text-center' },
                    { data: 'responses_cell', name: 'responses', searchable: false, className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center whitespace-nowrap' },
                ],
            });

            filterForm?.addEventListener('submit', (e) => {
                e.preventDefault();
                quizzesTable.ajax.reload();
            });

            document.getElementById('quizzesClearFilters')?.addEventListener('click', () => {
                filterForm.reset();
                filterForm.querySelectorAll('select, input').forEach(field => {
                    if (field.type !== 'submit' && field.type !== 'button') field.value = '';
                });
                quizzesTable.ajax.reload();
            });
        });

        // Deletes through the quizzes.destroy endpoint, then refreshes the table.
        async function deleteQuiz(trigger) {
            const title = trigger.dataset.title ?? 'this quiz';

            const confirmed = await App.confirmDelete({
                title: 'Delete quiz?',
                text: `“${title}” and its questions will be removed.`,
            });
            if (!confirmed) return;

            try {
                const payload = await App.request(`{{ url('quizzes') }}/${trigger.dataset.id}`, { method: 'DELETE' });
                App.toast('success', payload.message || 'Quiz deleted successfully.');
                quizzesTable?.ajax.reload(null, false);
            } catch (error) {
                App.toast('error', error.message);
            }
        }
    </script>
@endpush
