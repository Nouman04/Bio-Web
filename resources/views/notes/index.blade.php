@extends('layouts.app')

@section('title', 'Study Notes')
@section('meta-description', 'Manage study notes for this chapter.')

@section('page-title', 'Study Notes')
@section('page-subtitle', $chapter->title . ' · ' . $course->title)

@push('styles')
<style>
    /* ── Notes table ─────────────────────────────────────────────────────
       Same treatment as the courses, chapters and summaries grids: DataTables'
       own chrome folded into the panel so it reads as one quiet surface. */
    .notes-panel { overflow: hidden; }
    #notes-table_wrapper { padding: 0.25rem 0 0; font-size: 0.875rem; }

    /* Header */
    #notes-table thead th {
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
    .dark #notes-table thead th {
        color: rgb(148, 163, 184);
        background: rgba(15, 23, 42, 0.4);
        border-bottom-color: rgb(51, 65, 85);
    }
    #notes-table.dataTable thead th.dt-orderable-asc:hover,
    #notes-table.dataTable thead th.dt-orderable-desc:hover { color: #4648d4; }

    /* DataTables' own `table.dataTable thead>tr>th` rule outranks utility classes,
       so the centred columns are aligned here to keep header and cell in line. */
    #notes-table.dataTable thead > tr > th:nth-child(5),
    #notes-table.dataTable tbody > tr > td:nth-child(5) { text-align: center; }

    /* Body */
    #notes-table tbody td {
        padding: 0.9375rem 1.5rem;
        vertical-align: middle;
        border-top: none;
        border-bottom: 1px solid rgba(118, 117, 134, 0.08);
    }
    .dark #notes-table tbody td { border-bottom-color: rgba(51, 65, 85, 0.6); }
    #notes-table tbody tr:last-child td { border-bottom: none; }
    #notes-table tbody tr { transition: background-color 0.15s ease; }
    #notes-table tbody tr:hover { background: rgba(70, 72, 212, 0.035); }
    .dark #notes-table tbody tr:hover { background: rgba(70, 72, 212, 0.12); }
    #notes-table.dataTable tbody tr.odd,
    #notes-table.dataTable tbody tr.even,
    #notes-table.dataTable tbody tr > .sorting_1 { background: transparent; box-shadow: none; }
    #notes-table tbody td.dt-empty {
        padding: 3.5rem 1.5rem;
        text-align: center;
        color: rgb(118, 117, 134);
    }

    /* Footer chrome: length menu, info line, pagination */
    #notes-table_wrapper .dt-layout-row:last-child {
        padding: 0.875rem 1.5rem;
        border-top: 1px solid rgba(118, 117, 134, 0.12);
        background: rgba(242, 244, 246, 0.35);
    }
    .dark #notes-table_wrapper .dt-layout-row:last-child {
        border-top-color: rgb(51, 65, 85);
        background: rgba(15, 23, 42, 0.35);
    }
    #notes-table_wrapper .dt-layout-row:first-child { padding: 0.875rem 1.5rem 0.25rem; }
    #notes-table_wrapper .dt-length,
    #notes-table_wrapper .dt-info {
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(118, 117, 134);
    }
    .dark #notes-table_wrapper .dt-length,
    .dark #notes-table_wrapper .dt-info { color: rgb(148, 163, 184); }
    #notes-table_wrapper select {
        background: #ffffff;
        border: 1px solid rgba(118, 117, 134, 0.3);
        border-radius: 0.625rem;
        padding: 0.25rem 0.5rem;
        margin: 0 0.375rem;
        outline: none;
    }
    .dark #notes-table_wrapper select {
        background: rgb(15, 23, 42);
        border-color: rgb(51, 65, 85);
        color: rgb(226, 232, 240);
    }
    #notes-table_wrapper .dt-paging .dt-paging-button {
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
    #notes-table_wrapper .dt-paging .dt-paging-button:hover:not(.disabled) {
        background: rgba(70, 72, 212, 0.08) !important;
        color: #4648d4 !important;
    }
    #notes-table_wrapper .dt-paging .dt-paging-button.current {
        background: #4648d4 !important;
        color: #ffffff !important;
    }
    #notes-table_wrapper .dt-paging .dt-paging-button.disabled { opacity: 0.4; }

    /* The shimmer below stands in for DataTables' "Processing..." box */
    #notes-table_wrapper .dt-processing { display: none !important; }

    /* Loading shimmer: real <tr>s inside the table body, so the skeleton
       occupies exactly the rows' space and never covers the header or footer. */
    tr.notes-shimmer-row td > .shimmer-bar + .shimmer-bar { margin-top: 0.4375rem; }

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
    {{-- Breadcrumbs — course › chapter › notes --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6 flex-wrap">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses') }}">Courses</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters', $course) }}">{{ $course->title }}</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters.dashboard', [$course, $chapter]) }}">{{ $chapter->title }}</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Study Notes</span>
    </div>

    @php
        $filtersOpen = request()->hasAny(['title', 'topic', 'type']);
    @endphp

    {{-- Toolbar: Filter toggle + Add button --}}
    <div class="flex justify-end items-center gap-3 mb-4">
        <button type="button" id="filterToggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-800 text-on-surface-variant dark:text-slate-300 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-colors shadow-sm {{ $filtersOpen ? 'is-active' : '' }}"
            title="Toggle Filters">
            <i class="fa-solid fa-filter text-sm"></i>
        </button>
        <button type="button" onclick="openAddNoteModal()"
            class="flex items-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            Add New Note
        </button>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div class="filter-card-panel glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <form id="notes-filter-form" action="{{ route('notes', [$course, $chapter]) }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Search</label>
                            <div class="relative group">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-sm"></i>
                                <input name="title" value="{{ $filters['title'] ?? '' }}"
                                    class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none"
                                    placeholder="Search title or content..." type="text">
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic</label>
                            <select name="topic" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Topics</option>
                                @forelse($topics as $topic)
                                    <option value="{{ $topic->uuid }}" {{ ($filters['topic'] ?? '') == $topic->uuid ? 'selected' : '' }}>{{ $topic->title }}</option>
                                @empty
                                    <option value="" disabled>No topics in this chapter</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Type</label>
                            <select name="type" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Types</option>
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}" {{ ($filters['type'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <button type="button" id="notesClearFilters" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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

    {{-- Note List Data Table (server-side, Yajra DataTables) --}}
    <div class="notes-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto w-full">
            <table id="notes-table" class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="w-2/5">Note</th>
                        <th>Topic</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- One skeleton row, cloned into the table body while a draw is in flight --}}
    <template id="notes-shimmer-row">
        <tr class="notes-shimmer-row" aria-hidden="true">
            <td>
                <span class="shimmer-bar" style="width:55%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:35%"></span>
            </td>
            <td><span class="shimmer-bar shimmer-pill" style="width:60%"></span></td>
            <td><span class="shimmer-bar shimmer-pill" style="width:75%"></span></td>
            <td>
                <span class="shimmer-bar" style="width:60%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:40%"></span>
            </td>
            <td><span class="shimmer-bar shimmer-dots"></span></td>
        </tr>
    </template>

    {{-- Add Note Modal --}}
    <div id="add-note-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeAddNoteModal()"></div>
        <div class="relative w-full max-w-2xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Add New Note</h3>
                <button type="button" onclick="closeAddNoteModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="add-note-form" data-ajax-form action="{{ route('notes.store', [$course, $chapter]) }}" method="POST" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 -mb-2">
                    Adding to <span class="text-primary">{{ $chapter->title }}</span>
                </p>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Note Title</label>
                    <input name="title" type="text" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="e.g. Week 1 Summary">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic <span class="font-normal text-outline">(Optional)</span></label>
                        <select name="topic_id" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            <option value="">No topic</option>
                            @forelse($topics as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->title }}</option>
                            @empty
                                <option value="" disabled>No topics in this chapter yet</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Note Type</label>
                        <select id="add-note-type" name="type" class="note-type w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Only shown for summary notes --}}
                <div class="note-summary-field hidden flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter Summary <span class="font-normal text-outline">(Optional)</span></label>
                    <select id="add-note-summary" name="summary_id" class="note-summary w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        <option value="">Select the summary this note covers</option>
                        @forelse($summaries as $summary)
                            <option value="{{ $summary->id }}">{{ $summary->title }}</option>
                        @empty
                            <option value="" disabled>No summaries in this chapter yet</option>
                        @endforelse
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Content</label>
                    <textarea name="content" data-quill data-quill-height="200px" placeholder="Write your note content here..."></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAddNoteModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Creating…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Create Note</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Note Modal --}}
    <div id="edit-note-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeEditNoteModal()"></div>
        <div class="relative w-full max-w-2xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Edit Note</h3>
                <button type="button" onclick="closeEditNoteModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="edit-note-form" data-ajax-form action="#" method="POST" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                @method('PUT')
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 -mb-2">
                    Editing in <span class="text-primary">{{ $chapter->title }}</span>
                </p>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Note Title</label>
                    <input id="edit-note-title" name="title" type="text" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="e.g. Week 1 Summary">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic <span class="font-normal text-outline">(Optional)</span></label>
                        <select id="edit-note-topic" name="topic_id" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            <option value="">No topic</option>
                            @forelse($topics as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->title }}</option>
                            @empty
                                <option value="" disabled>No topics in this chapter yet</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Note Type</label>
                        <select id="edit-note-type" name="type" class="note-type w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Only shown for summary notes --}}
                <div class="note-summary-field hidden flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter Summary <span class="font-normal text-outline">(Optional)</span></label>
                    <select id="edit-note-summary" name="summary_id" class="note-summary w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        <option value="">Select the summary this note covers</option>
                        @forelse($summaries as $summary)
                            <option value="{{ $summary->id }}">{{ $summary->title }}</option>
                        @empty
                            <option value="" disabled>No summaries in this chapter yet</option>
                        @endforelse
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Content</label>
                    <textarea id="edit-note-content" name="content" data-quill data-quill-height="200px" placeholder="Write your note content here..."></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditNoteModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Saving…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let notesTable = null;

        document.addEventListener('DOMContentLoaded', () => {
            const filterToggle = document.getElementById('filterToggle');
            const filterCardWrapper = document.getElementById('filterCardWrapper');

            filterToggle?.addEventListener('click', () => {
                const isOpen = filterCardWrapper?.classList.toggle('is-open');
                filterToggle.classList.toggle('is-active', isOpen);
            });

            const filterForm = document.getElementById('notes-filter-form');

            // Server-side table: paging, ordering and filtering all happen in
            // NoteController@data, so only the visible page is ever loaded.
            notesTable = App.dataTable('#notes-table', {
                order: [[3, 'desc']],
                shimmerTemplate: '#notes-shimmer-row',
                language: {
                    emptyTable: 'No notes in this chapter yet.',
                    zeroRecords: 'No notes match these filters.',
                },
                ajax: {
                    url: '{{ route('notes.data', [$course, $chapter]) }}',
                    data: (params) => {
                        const filters = new FormData(filterForm);
                        // DataTables reserves `search`, so the filter box travels
                        // as search_term and is mapped back on the server. The
                        // chapter comes from the URL, not a filter.
                        params.search_term = filters.get('title') ?? '';
                        params.topic = filters.get('topic') ?? '';
                        params.type = filters.get('type') ?? '';
                        return params;
                    },
                },
                columns: [
                    { data: 'title_cell', name: 'title' },
                    { data: 'topic_cell', name: 'topic.title', orderable: false },
                    { data: 'type_cell', name: 'type' },
                    { data: 'date_cell', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center whitespace-nowrap' },
                ],
            });

            filterForm?.addEventListener('submit', (e) => {
                e.preventDefault();
                notesTable.ajax.reload();
            });

            document.getElementById('notesClearFilters')?.addEventListener('click', () => {
                filterForm.reset();
                filterForm.querySelectorAll('select').forEach(select => { select.value = ''; });
                filterForm.querySelector('input[name="title"]').value = '';
                notesTable.ajax.reload();
            });

            // The summary picker only applies to summary notes.
            document.querySelectorAll('.note-type').forEach(typeSelect => {
                typeSelect.addEventListener('change', (e) => toggleSummaryField(e.target.closest('form')));
            });

            // ── Add / Edit submit over AJAX ─────────────────────────────────
            const addForm = document.getElementById('add-note-form');
            const editForm = document.getElementById('edit-note-form');

            addForm?.addEventListener('ajax:success', () => {
                closeAddNoteModal();
                resetNoteForm(addForm);
                notesTable.ajax.reload(null, false);
            });

            editForm?.addEventListener('ajax:success', () => {
                closeEditNoteModal();
                notesTable.ajax.reload(null, false);
            });
        });

        // Shows the chapter-summary picker for summary notes, hides and clears
        // it otherwise, so an exam note never carries a stale summary_id.
        function toggleSummaryField(form) {
            if (!form) return;

            const isSummary = form.querySelector('.note-type')?.value === 'summary';
            const field = form.querySelector('.note-summary-field');
            const select = form.querySelector('.note-summary');

            field?.classList.toggle('hidden', !isSummary);
            field?.classList.toggle('flex', isSummary);
            if (!isSummary && select) select.value = '';
        }

        function openAddNoteModal() {
            const form = document.getElementById('add-note-form');
            toggleSummaryField(form);
            document.getElementById('add-note-modal-container').classList.remove('hidden');
        }

        function closeAddNoteModal() {
            document.getElementById('add-note-modal-container').classList.add('hidden');
        }

        // Fills the edit modal from the row's data-* attributes before opening it.
        function openEditNoteModal(trigger) {
            const form = document.getElementById('edit-note-form');
            const id = trigger.dataset.id;

            form.action = `{{ url("courses/{$course->uuid}/chapters/{$chapter->uuid}/notes") }}/${id}`;
            App.clearFieldErrors(form);

            document.getElementById('edit-note-title').value = trigger.dataset.title ?? '';
            document.getElementById('edit-note-topic').value = trigger.dataset.topicId ?? '';
            document.getElementById('edit-note-type').value = trigger.dataset.type ?? 'exam_notes';
            document.getElementById('edit-note-summary').value = trigger.dataset.summaryId ?? '';
            toggleSummaryField(form);

            const content = document.getElementById('edit-note-content');
            if (content.setQuillContent) {
                content.setQuillContent(trigger.dataset.content ?? '');
            } else {
                content.value = trigger.dataset.content ?? '';
            }

            document.getElementById('edit-note-modal-container').classList.remove('hidden');
        }

        function closeEditNoteModal() {
            document.getElementById('edit-note-modal-container').classList.add('hidden');
        }

        // Clears inputs and the Quill editor, which a native form.reset() misses.
        function resetNoteForm(form) {
            form.reset();
            App.clearFieldErrors(form);
            form.querySelectorAll('textarea[data-quill]').forEach(textarea => {
                textarea.setQuillContent?.('');
            });
            toggleSummaryField(form);
        }

        // Deletes through the notes.destroy endpoint, then refreshes the table.
        async function deleteNote(trigger) {
            const title = trigger.dataset.title || 'this note';

            const confirmed = await App.confirmDelete({
                title: 'Delete note?',
                text: `“${title}” will be removed from the listing.`,
            });
            if (!confirmed) return;

            try {
                const payload = await App.request(
                    `{{ url("courses/{$course->uuid}/chapters/{$chapter->uuid}/notes") }}/${trigger.dataset.id}`,
                    { method: 'DELETE' }
                );
                App.toast('success', payload.message || 'Note deleted successfully.');
                notesTable?.ajax.reload(null, false);
            } catch (error) {
                App.toast('error', error.message);
            }
        }
    </script>
@endpush
