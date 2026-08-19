@extends('layouts.app')

@section('title', 'Chapters — ' . $courseTitle)
@section('meta-description', 'Manage course chapters and curriculum structure in EduAdmin LMS.')

@section('page-title', 'Chapters')
@section('page-subtitle', $courseTitle)

@push('styles')
<style>
    /* ── Chapters table ─────────────────────────────────────────────────────
       Same treatment as the courses grid: DataTables' own chrome folded into
       the panel so it reads as one quiet surface. */
    .chapters-panel { overflow: hidden; }
    #chapters-table_wrapper { padding: 0.25rem 0 0; font-size: 0.875rem; }

    /* Header */
    #chapters-table thead th {
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
    .dark #chapters-table thead th {
        color: rgb(148, 163, 184);
        background: rgba(15, 23, 42, 0.4);
        border-bottom-color: rgb(51, 65, 85);
    }
    #chapters-table.dataTable thead th.dt-orderable-asc:hover,
    #chapters-table.dataTable thead th.dt-orderable-desc:hover { color: #4648d4; }

    /* DataTables' own `table.dataTable thead>tr>th` rule outranks utility classes,
       so the centred columns are aligned here to keep header and cell in line. */
    #chapters-table.dataTable thead > tr > th:nth-child(1),
    #chapters-table.dataTable tbody > tr > td:nth-child(1),
    #chapters-table.dataTable thead > tr > th:nth-child(4),
    #chapters-table.dataTable tbody > tr > td:nth-child(4) { text-align: center; }

    /* Keep the sort arrows tight against the centred header labels */
    #chapters-table.dataTable thead > tr > th:nth-child(1) span.dt-column-order { position: static; }

    /* Body */
    #chapters-table tbody td {
        padding: 0.9375rem 1.5rem;
        vertical-align: middle;
        border-top: none;
        border-bottom: 1px solid rgba(118, 117, 134, 0.08);
    }
    .dark #chapters-table tbody td { border-bottom-color: rgba(51, 65, 85, 0.6); }
    #chapters-table tbody tr:last-child td { border-bottom: none; }
    #chapters-table tbody tr { transition: background-color 0.15s ease; }
    #chapters-table tbody tr:hover { background: rgba(70, 72, 212, 0.035); }
    .dark #chapters-table tbody tr:hover { background: rgba(70, 72, 212, 0.12); }
    #chapters-table.dataTable tbody tr.odd,
    #chapters-table.dataTable tbody tr.even,
    #chapters-table.dataTable tbody tr > .sorting_1 { background: transparent; box-shadow: none; }
    #chapters-table tbody td.dt-empty {
        padding: 3.5rem 1.5rem;
        text-align: center;
        color: rgb(118, 117, 134);
    }

    /* Footer chrome: length menu, info line, pagination */
    #chapters-table_wrapper .dt-layout-row:last-child {
        padding: 0.875rem 1.5rem;
        border-top: 1px solid rgba(118, 117, 134, 0.12);
        background: rgba(242, 244, 246, 0.35);
    }
    .dark #chapters-table_wrapper .dt-layout-row:last-child {
        border-top-color: rgb(51, 65, 85);
        background: rgba(15, 23, 42, 0.35);
    }
    #chapters-table_wrapper .dt-layout-row:first-child { padding: 0.875rem 1.5rem 0.25rem; }
    #chapters-table_wrapper .dt-length,
    #chapters-table_wrapper .dt-info {
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(118, 117, 134);
    }
    .dark #chapters-table_wrapper .dt-length,
    .dark #chapters-table_wrapper .dt-info { color: rgb(148, 163, 184); }
    #chapters-table_wrapper select {
        background: #ffffff;
        border: 1px solid rgba(118, 117, 134, 0.3);
        border-radius: 0.625rem;
        padding: 0.25rem 0.5rem;
        margin: 0 0.375rem;
        outline: none;
    }
    .dark #chapters-table_wrapper select {
        background: rgb(15, 23, 42);
        border-color: rgb(51, 65, 85);
        color: rgb(226, 232, 240);
    }
    #chapters-table_wrapper .dt-paging .dt-paging-button {
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
    #chapters-table_wrapper .dt-paging .dt-paging-button:hover:not(.disabled) {
        background: rgba(70, 72, 212, 0.08) !important;
        color: #4648d4 !important;
    }
    #chapters-table_wrapper .dt-paging .dt-paging-button.current {
        background: #4648d4 !important;
        color: #ffffff !important;
    }
    #chapters-table_wrapper .dt-paging .dt-paging-button.disabled { opacity: 0.4; }

    /* The shimmer below stands in for DataTables' "Processing..." box */
    #chapters-table_wrapper .dt-processing { display: none !important; }

    /* Loading shimmer: real <tr>s inside the table body, so the skeleton
       occupies exactly the rows' space and never covers the header or footer. */
    tr.chapters-shimmer-row td > .shimmer-bar + .shimmer-bar { margin-top: 0.4375rem; }

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

    {{-- Ambient Background Glow --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-[-1]">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-primary-fixed-dim/20 rounded-full blur-[120px]"></div>
        <div class="absolute top-[60%] -right-[10%] w-[40%] h-[40%] bg-tertiary-fixed-dim/10 rounded-full blur-[100px]"></div>
    </div>

    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses') }}">Courses</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-on-surface-variant dark:text-slate-500">{{ $courseTitle }}</span>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Chapters</span>
    </div>

    @php
        $filtersOpen = request()->hasAny(['title', 'course', 'status']);
    @endphp

    {{-- Toolbar: Filter toggle + Add button --}}
    <div class="flex justify-end items-center gap-3 mb-4">
        <button type="button" id="filterToggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-800 text-on-surface-variant dark:text-slate-300 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-colors shadow-sm {{ $filtersOpen ? 'is-active' : '' }}"
            title="Toggle Filters">
            <i class="fa-solid fa-filter text-sm"></i>
        </button>
        <button type="button" onclick="openCreateChapterModal()"
            class="flex items-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            New Chapter
        </button>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div class="filter-card-panel glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <form id="chapters-filter-form" action="{{ route('courses.chapters', $courseId) }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Search</label>
                            <div class="relative group">
                                <span class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-sm"></span>
                                <input name="title" value="{{ $filters['title'] ?? '' }}"
                                    class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none"
                                    placeholder="Search by title..." type="text">
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Status</label>
                            <select name="status" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Status</option>
                                <option value="Published" {{ ($filters['status'] ?? '') == 'Published' ? 'selected' : '' }}>Published</option>
                                <option value="Draft" {{ ($filters['status'] ?? '') == 'Draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <button type="button" id="chaptersClearFilters" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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

    {{-- Bento Stats Grid --}}
    {{-- Bento Stats Grid --}}
    <div id="chapter-stats" class="app-stats grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 relative overflow-hidden border border-outline-variant/30 dark:border-slate-700 shadow-sm flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Total Chapters</h3>
                <span class="fa-solid fa-book-bookmark text-primary bg-primary/10 p-2 rounded-lg text-sm"></span>
            </div>
            <div>
                <span class="app-stat-value text-3xl font-bold text-on-surface dark:text-white" data-stat="total">{{ $stats['total'] }}</span>
                <span class="app-stat-shimmer shimmer-bar" aria-hidden="true"></span>
            </div>
        </div>
        <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 relative overflow-hidden border border-outline-variant/30 dark:border-slate-700 shadow-sm flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Published</h3>
                <span class="fa-solid fa-circle-check text-secondary bg-secondary-container/20 p-2 rounded-lg text-sm"></span>
            </div>
            <div>
                <span class="app-stat-value text-3xl font-bold text-on-surface dark:text-white" data-stat="published">{{ $stats['published'] }}</span>
                <span class="app-stat-shimmer shimmer-bar" aria-hidden="true"></span>
            </div>
        </div>
        <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 relative overflow-hidden border border-outline-variant/30 dark:border-slate-700 shadow-sm flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Drafts</h3>
                <span class="fa-solid fa-file-signature text-tertiary bg-tertiary-container/20 p-2 rounded-lg text-sm"></span>
            </div>
            <div>
                <span class="app-stat-value text-3xl font-bold text-on-surface dark:text-white" data-stat="drafts">{{ $stats['drafts'] }}</span>
                <span class="app-stat-shimmer shimmer-bar" aria-hidden="true"></span>
            </div>
        </div>
    </div>

    {{-- Chapter List Data Table (server-side, Yajra DataTables) --}}
    <div class="chapters-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto w-full">
            <table id="chapters-table" class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="w-2/5">Chapter</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- One skeleton row, cloned into the table body while a draw is in flight --}}
    <template id="chapters-shimmer-row">
        <tr class="chapters-shimmer-row" aria-hidden="true">
            <td><span class="shimmer-bar shimmer-chip"></span></td>
            <td>
                <span class="shimmer-bar" style="width:55%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:35%"></span>
            </td>
            <td><span class="shimmer-bar shimmer-pill" style="width:70%"></span></td>
            <td><span class="shimmer-bar shimmer-dots"></span></td>
        </tr>
    </template>


    {{-- Create Chapter Modal --}}
    <div id="create-chapter-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeCreateChapterModal()"></div>
        <!-- Panel -->
        <div class="relative w-full max-w-2xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Create New Chapter</h3>
                <button type="button" onclick="closeCreateChapterModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="create-chapter-form" data-ajax-form action="{{ route('courses.chapters.store', $courseId) }}" method="POST" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 -mb-2">
                    Adding to <span class="text-primary">{{ $courseTitle }}</span>
                </p>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter Number</label>
                    <input name="num" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="e.g. 1" type="number">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Title</label>
                    <input name="title" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="Enter chapter title" type="text">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Description</label>
                    <textarea name="desc" data-quill data-quill-no-attachments data-quill-height="150px" placeholder="Brief description of the chapter content"></textarea>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Status</label>
                    <select name="status" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        <option value="Draft" selected>Draft</option>
                        <option value="Published">Published</option>
                    </select>
                    <p class="text-xs text-outline dark:text-slate-500">Drafts stay hidden from students until published.</p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeCreateChapterModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Creating…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Create Chapter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Chapter Modal --}}
    <div id="edit-chapter-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeEditChapterModal()"></div>
        <!-- Panel -->
        <div class="relative w-full max-w-2xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Edit Chapter</h3>
                <button type="button" onclick="closeEditChapterModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="edit-chapter-form" data-ajax-form action="#" method="POST" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                @method('PUT')
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 -mb-2">
                    Editing in <span class="text-primary">{{ $courseTitle }}</span>
                </p>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter Number</label>
                    <input id="edit-chapter-num" name="num" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="e.g. 1" type="number">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Title</label>
                    <input id="edit-chapter-title" name="title" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="Enter chapter title" type="text">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Description</label>
                    <textarea id="edit-chapter-desc" name="desc" data-quill data-quill-no-attachments data-quill-height="150px" placeholder="Brief description of the chapter content"></textarea>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Status</label>
                    <select id="edit-chapter-status" name="status" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        <option value="Draft">Draft</option>
                        <option value="Published">Published</option>
                    </select>
                    <p class="text-xs text-outline dark:text-slate-500">Switching back to Draft hides the chapter from students.</p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditChapterModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Saving…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let chaptersTable = null;

        document.addEventListener('DOMContentLoaded', () => {
            const filterToggle = document.getElementById('filterToggle');
            const filterCardWrapper = document.getElementById('filterCardWrapper');

            filterToggle?.addEventListener('click', () => {
                const isOpen = filterCardWrapper?.classList.toggle('is-open');
                filterToggle.classList.toggle('is-active', isOpen);
            });

            const filterForm = document.getElementById('chapters-filter-form');

            // Server-side table: paging, ordering and filtering all happen in
            // ChapterController@data, so only the visible page is ever loaded.
            chaptersTable = App.dataTable('#chapters-table', {
                order: [[0, 'asc']],
                shimmerTemplate: '#chapters-shimmer-row',
                statsContainer: '#chapter-stats',
                language: {
                    emptyTable: 'No chapters yet for this course.',
                    zeroRecords: 'No chapters match these filters.',
                },
                ajax: {
                    url: '{{ route('courses.chapters.data', $courseId) }}',
                    data: (params) => {
                        const filters = new FormData(filterForm);
                        // DataTables reserves `search`, so the filter box travels
                        // as search_term and is mapped back on the server.
                        params.search_term = filters.get('title') ?? '';
                        params.status = filters.get('status') ?? '';
                        return params;
                    },
                },
                columns: [
                    { data: 'number_cell', name: 'chapter_number', className: 'text-center' },
                    { data: 'title_cell', name: 'title' },
                    { data: 'status_cell', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center whitespace-nowrap' },
                ],
                // The cards describe the whole course, and arrive with each draw.
                onStats: (stats) => {
                    document.querySelectorAll('#chapter-stats [data-stat]').forEach(node => {
                        node.textContent = stats[node.dataset.stat] ?? 0;
                    });
                },
            });

            filterForm?.addEventListener('submit', (e) => {
                e.preventDefault();
                chaptersTable.ajax.reload();
            });

            document.getElementById('chaptersClearFilters')?.addEventListener('click', () => {
                filterForm.reset();
                filterForm.querySelectorAll('select').forEach(select => { select.value = ''; });
                filterForm.querySelector('input[name="title"]').value = '';
                chaptersTable.ajax.reload();
            });

            // ── Add / Edit submit over AJAX ─────────────────────────────────
            const createForm = document.getElementById('create-chapter-form');
            const editForm = document.getElementById('edit-chapter-form');

            createForm?.addEventListener('ajax:success', () => {
                closeCreateChapterModal();
                resetChapterForm(createForm);
                chaptersTable.ajax.reload(null, false);
            });

            editForm?.addEventListener('ajax:success', () => {
                closeEditChapterModal();
                chaptersTable.ajax.reload(null, false);
            });
        });

        function openCreateChapterModal() {
            document.getElementById('create-chapter-modal-container').classList.remove('hidden');
        }

        function closeCreateChapterModal() {
            document.getElementById('create-chapter-modal-container').classList.add('hidden');
        }

        // Fills the edit modal from the row's data-* attributes before opening it.
        // The description is a Quill editor, so it is set through the handle the
        // layout's initializer attaches to the textarea.
        function openEditChapterModal(trigger) {
            const form = document.getElementById('edit-chapter-form');

            // The row decides which chapter this submit updates.
            form.action = `{{ url("courses/{$courseId->uuid}/chapters") }}/${trigger.dataset.id}`;
            App.clearFieldErrors(form);

            document.getElementById('edit-chapter-num').value = trigger.dataset.num ?? '';
            document.getElementById('edit-chapter-title').value = trigger.dataset.title ?? '';
            document.getElementById('edit-chapter-status').value = trigger.dataset.status ?? 'Draft';

            const desc = document.getElementById('edit-chapter-desc');
            if (desc.setQuillContent) {
                desc.setQuillContent(trigger.dataset.desc ?? '');
            } else {
                desc.value = trigger.dataset.desc ?? '';
            }

            document.getElementById('edit-chapter-modal-container').classList.remove('hidden');
        }

        function closeEditChapterModal() {
            document.getElementById('edit-chapter-modal-container').classList.add('hidden');
        }

        // Clears inputs plus the Quill editor, which a native form.reset() misses.
        function resetChapterForm(form) {
            form.reset();
            App.clearFieldErrors(form);
            form.querySelectorAll('textarea[data-quill]').forEach(textarea => {
                textarea.setQuillContent?.('');
            });
        }

        // Deletes through the chapters.destroy endpoint, then refreshes the table.
        async function deleteChapter(trigger) {
            const title = trigger.dataset.title ?? 'this chapter';

            const confirmed = await App.confirmDelete({
                title: 'Delete chapter?',
                text: `“${title}” and everything under it will be removed from the listing.`,
            });
            if (!confirmed) return;

            try {
                const payload = await App.request(
                    `{{ url("courses/{$courseId->uuid}/chapters") }}/${trigger.dataset.id}`,
                    { method: 'DELETE' }
                );
                App.toast('success', payload.message || 'Chapter deleted successfully.');
                chaptersTable?.ajax.reload(null, false);
            } catch (error) {
                App.toast('error', error.message);
            }
        }
    </script>
@endpush
