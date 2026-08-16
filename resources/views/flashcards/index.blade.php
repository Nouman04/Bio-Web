@extends('layouts.app')

@section('title', 'Flashcards')
@section('meta-description', 'Manage flashcards built from your question bank.')

@section('page-title', 'Flashcards')
@section('page-subtitle', $chapter->title . ' · ' . $course->title)

@push('styles')
<style>
    /* ── Flashcards table ────────────────────────────────────────────────────
       Same treatment as the courses and chapters grids: DataTables' own chrome
       folded into the panel so it reads as one quiet surface. */
    .flashcards-panel { overflow: hidden; }
    #flashcards-table_wrapper { padding: 0.25rem 0 0; font-size: 0.875rem; }

    /* Header */
    #flashcards-table thead th {
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
    .dark #flashcards-table thead th {
        color: rgb(148, 163, 184);
        background: rgba(15, 23, 42, 0.4);
        border-bottom-color: rgb(51, 65, 85);
    }
    #flashcards-table.dataTable thead th.dt-orderable-asc:hover,
    #flashcards-table.dataTable thead th.dt-orderable-desc:hover { color: #4648d4; }

    /* DataTables' own `table.dataTable thead>tr>th` rule outranks utility classes,
       so the centred columns are aligned here to keep header and cell in line. */
    #flashcards-table.dataTable thead > tr > th:nth-child(5),
    #flashcards-table.dataTable tbody > tr > td:nth-child(5) { text-align: center; }

    /* Body */
    #flashcards-table tbody td {
        padding: 0.9375rem 1.5rem;
        vertical-align: middle;
        border-top: none;
        border-bottom: 1px solid rgba(118, 117, 134, 0.08);
    }
    .dark #flashcards-table tbody td { border-bottom-color: rgba(51, 65, 85, 0.6); }
    #flashcards-table tbody tr:last-child td { border-bottom: none; }
    #flashcards-table tbody tr { transition: background-color 0.15s ease; }
    #flashcards-table tbody tr:hover { background: rgba(70, 72, 212, 0.035); }
    .dark #flashcards-table tbody tr:hover { background: rgba(70, 72, 212, 0.12); }
    #flashcards-table.dataTable tbody tr.odd,
    #flashcards-table.dataTable tbody tr.even,
    #flashcards-table.dataTable tbody tr > .sorting_1 { background: transparent; box-shadow: none; }
    #flashcards-table tbody td.dt-empty {
        padding: 3.5rem 1.5rem;
        text-align: center;
        color: rgb(118, 117, 134);
    }

    /* Footer chrome: length menu, info line, pagination */
    #flashcards-table_wrapper .dt-layout-row:last-child {
        padding: 0.875rem 1.5rem;
        border-top: 1px solid rgba(118, 117, 134, 0.12);
        background: rgba(242, 244, 246, 0.35);
    }
    .dark #flashcards-table_wrapper .dt-layout-row:last-child {
        border-top-color: rgb(51, 65, 85);
        background: rgba(15, 23, 42, 0.35);
    }
    #flashcards-table_wrapper .dt-layout-row:first-child { padding: 0.875rem 1.5rem 0.25rem; }
    #flashcards-table_wrapper .dt-length,
    #flashcards-table_wrapper .dt-info {
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(118, 117, 134);
    }
    .dark #flashcards-table_wrapper .dt-length,
    .dark #flashcards-table_wrapper .dt-info { color: rgb(148, 163, 184); }
    #flashcards-table_wrapper select {
        background: #ffffff;
        border: 1px solid rgba(118, 117, 134, 0.3);
        border-radius: 0.625rem;
        padding: 0.25rem 0.5rem;
        margin: 0 0.375rem;
        outline: none;
    }
    .dark #flashcards-table_wrapper select {
        background: rgb(15, 23, 42);
        border-color: rgb(51, 65, 85);
        color: rgb(226, 232, 240);
    }
    #flashcards-table_wrapper .dt-paging .dt-paging-button {
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
    #flashcards-table_wrapper .dt-paging .dt-paging-button:hover:not(.disabled) {
        background: rgba(70, 72, 212, 0.08) !important;
        color: #4648d4 !important;
    }
    #flashcards-table_wrapper .dt-paging .dt-paging-button.current {
        background: #4648d4 !important;
        color: #ffffff !important;
    }
    #flashcards-table_wrapper .dt-paging .dt-paging-button.disabled { opacity: 0.4; }

    /* The shimmer below stands in for DataTables' "Processing..." box */
    #flashcards-table_wrapper .dt-processing { display: none !important; }

    /* Loading shimmer: real <tr>s inside the table body, so the skeleton
       occupies exactly the rows' space and never covers the header or footer. */
    tr.flashcards-shimmer-row td > .shimmer-bar + .shimmer-bar { margin-top: 0.4375rem; }

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
    {{-- Breadcrumbs — course › chapter › flashcards --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6 flex-wrap">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses') }}">Courses</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters', $course->id) }}">{{ $course->title }}</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters.dashboard', [$course->id, $chapter->id]) }}">{{ $chapter->title }}</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Flashcards</span>
    </div>

    @php
        $filtersOpen = request()->hasAny(['title', 'source_type', 'source_id']);
    @endphp

    {{-- Toolbar: Filter toggle + Add button --}}
    <div class="flex justify-end items-center gap-3 mb-4">
        <button type="button" id="filterToggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-800 text-on-surface-variant dark:text-slate-300 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-colors shadow-sm {{ $filtersOpen ? 'is-active' : '' }}"
            title="Toggle Filters">
            <i class="fa-solid fa-filter text-sm"></i>
        </button>
        <button type="button" onclick="openAddFlashcardModal()"
            class="flex items-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            Add New Flashcard
        </button>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div class="filter-card-panel glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <form id="flashcards-filter-form" action="{{ route('flashcards', [$course->id, $chapter->id]) }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Search</label>
                            <div class="relative group">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-sm"></i>
                                <input name="title" value="{{ $filters['title'] ?? '' }}"
                                    class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none"
                                    placeholder="Search by title..." type="text">
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Linked To</label>
                            <select id="filter-source-type" name="source_type" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Types</option>
                                @foreach($sources as $key => $label)
                                    <option value="{{ $key }}" {{ ($filters['source_type'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Record</label>
                            <select id="filter-source-id" name="source_id" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none disabled:opacity-50" disabled>
                                <option value="">Pick a type first</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <button type="button" id="flashcardsClearFilters" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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

    {{-- Flashcard List Data Table (server-side, Yajra DataTables) --}}
    <div class="flashcards-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto w-full">
            <table id="flashcards-table" class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="w-2/5">Title</th>
                        <th>Linked To</th>
                        <th>Questions</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- One skeleton row, cloned into the table body while a draw is in flight --}}
    <template id="flashcards-shimmer-row">
        <tr class="flashcards-shimmer-row" aria-hidden="true">
            <td>
                <span class="shimmer-bar" style="width:55%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:35%"></span>
            </td>
            <td><span class="shimmer-bar shimmer-pill" style="width:70%"></span></td>
            <td><span class="shimmer-bar shimmer-chip"></span></td>
            <td>
                <span class="shimmer-bar" style="width:60%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:40%"></span>
            </td>
            <td><span class="shimmer-bar shimmer-dots"></span></td>
        </tr>
    </template>

    {{-- Add Flashcard Modal — step one: the deck's details --}}
    <div id="add-flashcard-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeAddFlashcardModal()"></div>
        <div class="relative w-full max-w-2xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <div>
                    <h3 class="text-lg font-bold text-on-surface dark:text-white">Add New Flashcard</h3>
                    <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-0.5">Step 1 of 2 — name the deck, then add its questions.</p>
                </div>
                <button type="button" onclick="closeAddFlashcardModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="add-flashcard-form" data-ajax-form action="{{ route('flashcards.store', [$course->id, $chapter->id]) }}" method="POST" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 -mb-2">
                    Adding to <span class="text-primary">{{ $chapter->title }}</span>
                </p>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Title</label>
                    <input name="title" type="text" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="e.g. Cell Structures — Revision Deck">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Linked To <span class="font-normal text-outline">(Optional)</span></label>
                        <select id="add-source-type" name="source_type" class="source-type w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            <option value="">Standalone deck</option>
                            @foreach($sources as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Record</label>
                        <select id="add-source-id" name="source_id" class="source-id w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface disabled:opacity-50" disabled>
                            <option value="">Pick a type first</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAddFlashcardModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Creating…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center gap-2">
                        Continue to Questions
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Flashcard Modal — details only; questions live in the builder --}}
    <div id="edit-flashcard-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeEditFlashcardModal()"></div>
        <div class="relative w-full max-w-2xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Edit Flashcard</h3>
                <button type="button" onclick="closeEditFlashcardModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="edit-flashcard-form" data-ajax-form action="#" method="POST" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                @method('PUT')
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 -mb-2">
                    Editing in <span class="text-primary">{{ $chapter->title }}</span>
                </p>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Title</label>
                    <input id="edit-flashcard-title" name="title" type="text" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="e.g. Cell Structures — Revision Deck">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Linked To <span class="font-normal text-outline">(Optional)</span></label>
                        <select id="edit-source-type" name="source_type" class="source-type w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            <option value="">Standalone deck</option>
                            @foreach($sources as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Record</label>
                        <select id="edit-source-id" name="source_id" class="source-id w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface disabled:opacity-50" disabled>
                            <option value="">Pick a type first</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditFlashcardModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Saving…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let flashcardsTable = null;

        document.addEventListener('DOMContentLoaded', () => {
            const filterToggle = document.getElementById('filterToggle');
            const filterCardWrapper = document.getElementById('filterCardWrapper');

            filterToggle?.addEventListener('click', () => {
                const isOpen = filterCardWrapper?.classList.toggle('is-open');
                filterToggle.classList.toggle('is-active', isOpen);
            });

            const filterForm = document.getElementById('flashcards-filter-form');

            // Server-side table: paging, ordering and filtering all happen in
            // FlashcardController@data, so only the visible page is ever loaded.
            flashcardsTable = App.dataTable('#flashcards-table', {
                order: [[3, 'desc']],
                shimmerTemplate: '#flashcards-shimmer-row',
                language: {
                    emptyTable: 'No flashcards yet.',
                    zeroRecords: 'No flashcards match these filters.',
                },
                ajax: {
                    url: '{{ route('flashcards.data', [$course->id, $chapter->id]) }}',
                    data: (params) => {
                        const filters = new FormData(filterForm);
                        // DataTables reserves `search`, so the filter box travels
                        // as search_term and is mapped back on the server.
                        params.search_term = filters.get('title') ?? '';
                        params.source_type = filters.get('source_type') ?? '';
                        params.source_id = filters.get('source_id') ?? '';
                        return params;
                    },
                },
                columns: [
                    { data: 'title_cell', name: 'title' },
                    { data: 'source_cell', name: 'flashcardable_type', orderable: false },
                    { data: 'questions_cell', name: 'assessments_count', className: 'text-center' },
                    { data: 'date_cell', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center whitespace-nowrap' },
                ],
            });

            filterForm?.addEventListener('submit', (e) => {
                e.preventDefault();
                flashcardsTable.ajax.reload();
            });

            document.getElementById('flashcardsClearFilters')?.addEventListener('click', () => {
                filterForm.reset();
                filterForm.querySelectorAll('select').forEach(select => { select.value = ''; });
                filterForm.querySelector('input[name="title"]').value = '';
                resetSourceRecords(document.getElementById('filter-source-id'));
                flashcardsTable.ajax.reload();
            });

            // ── Dependent source pickers ────────────────────────────────────
            // Choosing a type loads that type's records into the neighbouring
            // select, in the filter card and in both modals.
            document.getElementById('filter-source-type')?.addEventListener('change', (e) => {
                loadSourceRecords(e.target.value, document.getElementById('filter-source-id'));
            });

            document.querySelectorAll('.source-type').forEach(typeSelect => {
                typeSelect.addEventListener('change', (e) => {
                    const recordSelect = e.target.closest('.grid').querySelector('.source-id');
                    loadSourceRecords(e.target.value, recordSelect);
                });
            });

            // ── Add / Edit submit over AJAX ─────────────────────────────────
            const addForm = document.getElementById('add-flashcard-form');
            const editForm = document.getElementById('edit-flashcard-form');

            // Step one hands off to the builder, which is step two.
            addForm?.addEventListener('ajax:success', (e) => {
                const redirect = e.detail?.payload?.redirect;
                if (redirect) {
                    window.location.href = redirect;
                    return;
                }
                closeAddFlashcardModal();
                flashcardsTable.ajax.reload(null, false);
            });

            editForm?.addEventListener('ajax:success', () => {
                closeEditFlashcardModal();
                flashcardsTable.ajax.reload(null, false);
            });
        });

        // Fills a "Record" select with the chosen type's rows.
        function loadSourceRecords(type, recordSelect, selectedId = null, selectedLabel = null) {
            if (!recordSelect) return;

            if (!type) {
                resetSourceRecords(recordSelect);
                return;
            }

            recordSelect.disabled = true;
            recordSelect.innerHTML = '<option value="">Loading…</option>';

            App.request(`{{ url("courses/{$course->id}/chapters/{$chapter->id}/flashcards/sources") }}/${type}`)
                .then(records => {
                    recordSelect.innerHTML = '<option value="">Select a record</option>';
                    records.forEach(record => {
                        const option = document.createElement('option');
                        option.value = record.id;
                        option.textContent = record.text;
                        recordSelect.appendChild(option);
                    });

                    // The linked record may sit outside the 50 most recent.
                    if (selectedId && !records.some(record => String(record.id) === String(selectedId))) {
                        const option = document.createElement('option');
                        option.value = selectedId;
                        option.textContent = selectedLabel || `#${selectedId}`;
                        recordSelect.appendChild(option);
                    }

                    recordSelect.value = selectedId ?? '';
                    recordSelect.disabled = false;
                })
                .catch(() => {
                    recordSelect.innerHTML = '<option value="">Could not load records</option>';
                });
        }

        function resetSourceRecords(recordSelect) {
            if (!recordSelect) return;
            recordSelect.innerHTML = '<option value="">Pick a type first</option>';
            recordSelect.disabled = true;
        }

        function openAddFlashcardModal() {
            document.getElementById('add-flashcard-modal-container').classList.remove('hidden');
        }

        function closeAddFlashcardModal() {
            const form = document.getElementById('add-flashcard-form');
            form.reset();
            App.clearFieldErrors(form);
            resetSourceRecords(document.getElementById('add-source-id'));
            document.getElementById('add-flashcard-modal-container').classList.add('hidden');
        }

        // Fills the edit modal from the row's data-* attributes.
        function openEditFlashcardModal(trigger) {
            const form = document.getElementById('edit-flashcard-form');
            const id = trigger.dataset.id;

            form.action = `{{ url("courses/{$course->id}/chapters/{$chapter->id}/flashcards") }}/${id}`;
            App.clearFieldErrors(form);

            document.getElementById('edit-flashcard-title').value = trigger.dataset.title ?? '';

            const type = trigger.dataset.sourceType ?? '';
            document.getElementById('edit-source-type').value = type;
            loadSourceRecords(
                type,
                document.getElementById('edit-source-id'),
                trigger.dataset.sourceId || null,
                trigger.dataset.sourceLabel || null
            );

            document.getElementById('edit-flashcard-modal-container').classList.remove('hidden');
        }

        function closeEditFlashcardModal() {
            document.getElementById('edit-flashcard-modal-container').classList.add('hidden');
        }

        // Deletes through the flashcards.destroy endpoint, then refreshes the table.
        async function deleteFlashcard(trigger) {
            const title = trigger.dataset.title ?? 'this flashcard';

            const confirmed = await App.confirmDelete({
                title: 'Delete flashcard?',
                text: `“${title}” and its attached questions will be removed.`,
            });
            if (!confirmed) return;

            try {
                const payload = await App.request(`{{ url("courses/{$course->id}/chapters/{$chapter->id}/flashcards") }}/${trigger.dataset.id}`, { method: 'DELETE' });
                App.toast('success', payload.message || 'Flashcard deleted successfully.');
                flashcardsTable?.ajax.reload(null, false);
            } catch (error) {
                App.toast('error', error.message);
            }
        }
    </script>
@endpush
