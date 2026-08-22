@extends('layouts.app')

@section('title', 'Topics')
@section('meta-description', 'Manage and view course topics in EduAdmin LMS.')

@section('page-title', 'Topics')
@section('page-subtitle', $chapter->title . ' · ' . $course->title)

@push('styles')
<style>
    /* ── Topics table ────────────────────────────────────────────────────
       Same treatment as the courses and chapters grids: DataTables' own chrome
       folded into the panel so it reads as one quiet surface. */
    .topics-panel { overflow: hidden; }
    #topics-table_wrapper { padding: 0.25rem 0 0; font-size: 0.875rem; }

    /* Header */
    #topics-table thead th {
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
    .dark #topics-table thead th {
        color: rgb(148, 163, 184);
        background: rgba(15, 23, 42, 0.4);
        border-bottom-color: rgb(51, 65, 85);
    }
    #topics-table.dataTable thead th.dt-orderable-asc:hover,
    #topics-table.dataTable thead th.dt-orderable-desc:hover { color: #4648d4; }

    /* DataTables' own `table.dataTable thead>tr>th` rule outranks utility classes,
       so the centred columns are aligned here to keep header and cell in line. */
    #topics-table.dataTable thead > tr > th:nth-child(2),
    #topics-table.dataTable tbody > tr > td:nth-child(2),
    #topics-table.dataTable thead > tr > th:nth-child(3),
    #topics-table.dataTable tbody > tr > td:nth-child(3) { text-align: center; }

    /* Keep the sort arrows tight against the centred header labels */
    #topics-table.dataTable thead > tr > th:nth-child(2) span.dt-column-order { position: static; }

    /* Body */
    #topics-table tbody td {
        padding: 0.9375rem 1.5rem;
        vertical-align: middle;
        border-top: none;
        border-bottom: 1px solid rgba(118, 117, 134, 0.08);
    }
    .dark #topics-table tbody td { border-bottom-color: rgba(51, 65, 85, 0.6); }
    #topics-table tbody tr:last-child td { border-bottom: none; }
    #topics-table tbody tr { transition: background-color 0.15s ease; }
    #topics-table tbody tr:hover { background: rgba(70, 72, 212, 0.035); }
    .dark #topics-table tbody tr:hover { background: rgba(70, 72, 212, 0.12); }
    #topics-table.dataTable tbody tr.odd,
    #topics-table.dataTable tbody tr.even,
    #topics-table.dataTable tbody tr > .sorting_1 { background: transparent; box-shadow: none; }
    #topics-table tbody td.dt-empty {
        padding: 3.5rem 1.5rem;
        text-align: center;
        color: rgb(118, 117, 134);
    }

    /* Footer chrome: length menu, info line, pagination */
    #topics-table_wrapper .dt-layout-row:last-child {
        padding: 0.875rem 1.5rem;
        border-top: 1px solid rgba(118, 117, 134, 0.12);
        background: rgba(242, 244, 246, 0.35);
    }
    .dark #topics-table_wrapper .dt-layout-row:last-child {
        border-top-color: rgb(51, 65, 85);
        background: rgba(15, 23, 42, 0.35);
    }
    #topics-table_wrapper .dt-layout-row:first-child { padding: 0.875rem 1.5rem 0.25rem; }
    #topics-table_wrapper .dt-length,
    #topics-table_wrapper .dt-info {
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(118, 117, 134);
    }
    .dark #topics-table_wrapper .dt-length,
    .dark #topics-table_wrapper .dt-info { color: rgb(148, 163, 184); }
    #topics-table_wrapper select {
        background: #ffffff;
        border: 1px solid rgba(118, 117, 134, 0.3);
        border-radius: 0.625rem;
        padding: 0.25rem 0.5rem;
        margin: 0 0.375rem;
        outline: none;
    }
    .dark #topics-table_wrapper select {
        background: rgb(15, 23, 42);
        border-color: rgb(51, 65, 85);
        color: rgb(226, 232, 240);
    }
    #topics-table_wrapper .dt-paging .dt-paging-button {
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
    #topics-table_wrapper .dt-paging .dt-paging-button:hover:not(.disabled) {
        background: rgba(70, 72, 212, 0.08) !important;
        color: #4648d4 !important;
    }
    #topics-table_wrapper .dt-paging .dt-paging-button.current {
        background: #4648d4 !important;
        color: #ffffff !important;
    }
    #topics-table_wrapper .dt-paging .dt-paging-button.disabled { opacity: 0.4; }

    /* The shimmer below stands in for DataTables' "Processing..." box */
    #topics-table_wrapper .dt-processing { display: none !important; }

    /* Loading shimmer: real <tr>s inside the table body, so the skeleton
       occupies exactly the rows' space and never covers the header or footer. */
    tr.topics-shimmer-row td > .shimmer-bar + .shimmer-bar { margin-top: 0.4375rem; }

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
    {{-- Breadcrumbs — course › chapter › topics --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6 flex-wrap">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses') }}">Courses</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters', $course) }}">{{ $course->title }}</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters.dashboard', [$course, $chapter]) }}">{{ $chapter->title }}</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Topics</span>
    </div>

    @php
        $filtersOpen = request()->hasAny(['title']);
    @endphp

    {{-- Toolbar: Filter toggle + Add button --}}
    <div class="flex justify-end items-center gap-3 mb-4">
        <button type="button" id="filterToggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-800 text-on-surface-variant dark:text-slate-300 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-colors shadow-sm {{ $filtersOpen ? 'is-active' : '' }}"
            title="Toggle Filters">
            <i class="fa-solid fa-filter text-sm"></i>
        </button>
        <button type="button" onclick="openAddTopicModal()"
            class="flex items-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            Add New Topic
        </button>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div class="filter-card-panel glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <form id="topics-filter-form" action="{{ route('topics', [$course, $chapter]) }}" method="GET">
                    <div class="grid grid-cols-1 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Search</label>
                            <div class="relative group">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-sm"></i>
                                <input name="title" value="{{ $filters['title'] ?? '' }}"
                                    class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none"
                                    placeholder="Search topics in this chapter..." type="text">
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <button type="button" id="topicsClearFilters" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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

    {{-- Topic List Data Table (server-side, Yajra DataTables) --}}
    <div class="topics-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto w-full">
            <table id="topics-table" class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="w-2/3">Topic</th>
                        <th>Questions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- One skeleton row, cloned into the table body while a draw is in flight --}}
    <template id="topics-shimmer-row">
        <tr class="topics-shimmer-row" aria-hidden="true">
            <td>
                <span class="shimmer-bar" style="width:55%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:35%"></span>
            </td>
            <td><span class="shimmer-bar shimmer-chip"></span></td>
            <td><span class="shimmer-bar shimmer-dots"></span></td>
        </tr>
    </template>

    {{-- Add Topic Modal --}}
    <div id="add-topic-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeAddTopicModal()"></div>
        <div class="relative w-full max-w-3xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Add New Topic</h3>
                <button type="button" onclick="closeAddTopicModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="add-topic-form" data-ajax-form action="{{ route('topics.store', [$course, $chapter]) }}" method="POST" enctype="multipart/form-data" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 -mb-2">
                    Adding to <span class="text-primary">{{ $chapter->title }}</span>
                </p>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic Name</label>
                    <input name="title" type="text" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="Enter topic name">
                </div>

                @include('partials.parent-topic-picker')
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Content</label>
                    <textarea name="content" data-quill data-quill-height="220px" placeholder="Enter topic content..."></textarea>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">
                        Attachments <span class="font-normal text-outline dark:text-slate-500">(Optional)</span>
                    </label>
                    <div class="relative group border-2 border-dashed border-outline-variant/60 dark:border-slate-600 bg-surface-container-low/50 dark:bg-slate-900/50 hover:border-primary dark:hover:border-primary hover:bg-primary/5 transition-colors rounded-xl flex flex-col items-center justify-center p-5 cursor-pointer overflow-hidden">
                        <div class="w-10 h-10 rounded-full bg-primary-container/20 text-primary flex items-center justify-center mb-2 group-hover:scale-110 transition-transform duration-300">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <p class="text-sm font-semibold text-on-surface dark:text-white">Click or drag files to this area to upload</p>
                        <p class="text-xs font-medium text-outline dark:text-slate-500 mt-1">PDF, DOC, JPG, PNG up to 10MB each</p>
                        <input name="attachments[]" type="file" multiple
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,image/*"
                            class="topic-attachments absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                    <ul class="topic-attachments-list hidden flex-col gap-1.5 mt-1"></ul>
                </div>
                @include('partials.question-widget', ['qwFieldName' => 'question_ids', 'qwLabel' => 'Linked Questions (Optional)', 'qwPastPaper' => true])
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAddTopicModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Creating…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Add Topic</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Topic Modal --}}
    <div id="edit-topic-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeEditTopicModal()"></div>
        <div class="relative w-full max-w-3xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Edit Topic</h3>
                <button type="button" onclick="closeEditTopicModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="edit-topic-form" data-ajax-form action="#" method="POST" enctype="multipart/form-data" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                @method('PUT')
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 -mb-2">
                    Editing in <span class="text-primary">{{ $chapter->title }}</span>
                </p>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic Name</label>
                    <input id="edit-topic-title" name="title" type="text" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="Enter topic name">
                </div>

                @include('partials.parent-topic-picker')
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Content</label>
                    <textarea id="edit-topic-content" name="content" data-quill data-quill-height="220px" placeholder="Enter topic content..."></textarea>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">
                        Add Attachments <span class="font-normal text-outline dark:text-slate-500">(Optional)</span>
                    </label>
                    <div class="relative group border-2 border-dashed border-outline-variant/60 dark:border-slate-600 bg-surface-container-low/50 dark:bg-slate-900/50 hover:border-primary dark:hover:border-primary hover:bg-primary/5 transition-colors rounded-xl flex flex-col items-center justify-center p-5 cursor-pointer overflow-hidden">
                        <div class="w-10 h-10 rounded-full bg-primary-container/20 text-primary flex items-center justify-center mb-2 group-hover:scale-110 transition-transform duration-300">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <p class="text-sm font-semibold text-on-surface dark:text-white">Click or drag files to add</p>
                        <p class="text-xs font-medium text-outline dark:text-slate-500 mt-1">New files are added to the existing attachments</p>
                        <input name="attachments[]" type="file" multiple
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,image/*"
                            class="topic-attachments absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                    <ul class="topic-attachments-list hidden flex-col gap-1.5 mt-1"></ul>
                </div>
                @include('partials.question-widget', ['qwFieldName' => 'question_ids', 'qwLabel' => 'Linked Questions (Optional)', 'qwPastPaper' => true])
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditTopicModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Saving…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let topicsTable = null;

        document.addEventListener('DOMContentLoaded', () => {
            const filterToggle = document.getElementById('filterToggle');
            const filterCardWrapper = document.getElementById('filterCardWrapper');

            filterToggle?.addEventListener('click', () => {
                const isOpen = filterCardWrapper?.classList.toggle('is-open');
                filterToggle.classList.toggle('is-active', isOpen);
            });

            const filterForm = document.getElementById('topics-filter-form');

            // Server-side table: paging, ordering and filtering all happen in
            // TopicController@data, so only the visible page is ever loaded.
            topicsTable = App.dataTable('#topics-table', {
                order: [[0, 'asc']],
                shimmerTemplate: '#topics-shimmer-row',
                language: {
                    emptyTable: 'No topics found.',
                    zeroRecords: 'No topics match these filters.',
                },
                ajax: {
                    url: '{{ route('topics.data', [$course, $chapter]) }}',
                    data: (params) => {
                        const filters = new FormData(filterForm);
                        // DataTables reserves `search`, so the filter box travels
                        // as search_term and is mapped back on the server. The
                        // chapter comes from the URL, not a filter.
                        params.search_term = filters.get('title') ?? '';
                        return params;
                    },
                },
                columns: [
                    { data: 'title_cell', name: 'title' },
                    { data: 'questions_cell', name: 'questionables_count', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center whitespace-nowrap' },
                ],
            });

            filterForm?.addEventListener('submit', (e) => {
                e.preventDefault();
                topicsTable.ajax.reload();
            });

            document.getElementById('topicsClearFilters')?.addEventListener('click', () => {
                filterForm.reset();
                filterForm.querySelectorAll('select').forEach(select => { select.value = ''; });
                filterForm.querySelector('input[name="title"]').value = '';
                topicsTable.ajax.reload();
            });

            // List the chosen attachments, with per-file removal.
            document.querySelectorAll('.topic-attachments').forEach(input => {
                // The list sits outside the dropzone, so scope the lookup to the
                // form: each modal has exactly one input and one list.
                const list = input.closest('form')?.querySelector('.topic-attachments-list');
                if (!list) return;

                function render() {
                    list.innerHTML = '';
                    list.classList.toggle('hidden', input.files.length === 0);
                    list.classList.toggle('flex', input.files.length > 0);

                    Array.from(input.files).forEach((file, index) => {
                        const item = document.createElement('li');
                        item.className = 'flex items-center justify-between gap-3 px-3 py-2 rounded-lg bg-surface-container-low dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700 text-sm';
                        item.innerHTML = `
                            <span class="flex items-center gap-2 min-w-0 text-on-surface dark:text-slate-200">
                                <i class="fa-solid fa-paperclip text-xs text-on-surface-variant"></i>
                                <span class="truncate">${file.name}</span>
                            </span>
                            <span class="flex items-center gap-2 shrink-0">
                                <span class="text-xs text-on-surface-variant dark:text-slate-400">${(file.size / 1024).toFixed(0)} KB</span>
                                <button type="button" data-index="${index}" class="w-6 h-6 flex items-center justify-center rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </span>`;
                        list.appendChild(item);
                    });
                }

                input.addEventListener('change', render);

                list.addEventListener('click', (e) => {
                    const button = e.target.closest('button[data-index]');
                    if (!button) return;

                    // FileList is read-only, so rebuild it without the removed file.
                    const remaining = new DataTransfer();
                    Array.from(input.files).forEach((file, index) => {
                        if (index !== Number(button.dataset.index)) remaining.items.add(file);
                    });
                    input.files = remaining.files;
                    render();
                });
            });

            // ── Add / Edit submit over AJAX ─────────────────────────────────
            const addForm = document.getElementById('add-topic-form');
            const editForm = document.getElementById('edit-topic-form');

            addForm?.addEventListener('ajax:success', () => {
                closeAddTopicModal();
                resetTopicForm(addForm);
                topicsTable.ajax.reload(null, false);
            });

            editForm?.addEventListener('ajax:success', () => {
                closeEditTopicModal();
                resetTopicAttachments(editForm);
                topicsTable.ajax.reload(null, false);
            });
        });

        function openAddTopicModal() {
            document.getElementById('add-topic-modal-container').classList.remove('hidden');
        }

        function closeAddTopicModal() {
            document.getElementById('add-topic-modal-container').classList.add('hidden');
        }

        // Fills the edit modal from the row's data-* attributes, then fetches the
        // questions already linked so the picker opens pre-populated.
        function openEditTopicModal(trigger) {
            const form = document.getElementById('edit-topic-form');
            const id = trigger.dataset.id;

            form.action = `{{ url("courses/{$course->uuid}/chapters/{$chapter->uuid}/topics") }}/${id}`;
            App.clearFieldErrors(form);

            document.getElementById('edit-topic-title').value = trigger.dataset.title ?? '';

            const content = document.getElementById('edit-topic-content');
            if (content.setQuillContent) {
                content.setQuillContent(trigger.dataset.content ?? '');
            } else {
                content.value = trigger.dataset.content ?? '';
            }

            resetTopicAttachments(form);

            // Pre-select the saved parent; a topic may not parent itself.
            const parentPicker = form.querySelector('.parent-topic-picker');
            parentPicker?.setParent?.(
                trigger.dataset.parentId
                    ? { id: trigger.dataset.parentId, text: trigger.dataset.parentTitle, meta: trigger.dataset.parentMeta }
                    : null,
                id
            );

            const widget = form.querySelector('.question-widget');
            widget?.resetQuestions?.();
            App.request(`{{ url("courses/{$course->uuid}/chapters/{$chapter->uuid}/topics") }}/${id}/questions`)
                .then(questions => widget?.setQuestions?.(questions))
                .catch(() => App.toast('error', 'Could not load the linked questions.'));

            document.getElementById('edit-topic-modal-container').classList.remove('hidden');
        }

        function closeEditTopicModal() {
            document.getElementById('edit-topic-modal-container').classList.add('hidden');
        }

        function resetTopicAttachments(form) {
            const input = form.querySelector('.topic-attachments');
            const list = form.querySelector('.topic-attachments-list');
            if (input) input.value = '';
            if (list) {
                list.innerHTML = '';
                list.classList.add('hidden');
                list.classList.remove('flex');
            }
        }

        // Clears inputs, the Quill editor, attachments and the question picker,
        // none of which a native form.reset() fully handles.
        function resetTopicForm(form) {
            form.reset();
            App.clearFieldErrors(form);
            form.querySelectorAll('textarea[data-quill]').forEach(textarea => {
                textarea.setQuillContent?.('');
            });
            form.querySelector('.question-widget')?.resetQuestions?.();
            resetTopicAttachments(form);
        }

        // Deletes through the topics.destroy endpoint, then refreshes the table.
        async function deleteTopic(trigger) {
            const title = trigger.dataset.title ?? 'this topic';

            const confirmed = await App.confirmDelete({
                title: 'Delete topic?',
                text: `“${title}” will be removed from the listing.`,
            });
            if (!confirmed) return;

            try {
                const payload = await App.request(`{{ url("courses/{$course->uuid}/chapters/{$chapter->uuid}/topics") }}/${trigger.dataset.id}`, { method: 'DELETE' });
                App.toast('success', payload.message || 'Topic deleted successfully.');
                topicsTable?.ajax.reload(null, false);
            } catch (error) {
                App.toast('error', error.message);
            }
        }
    </script>
@endpush
