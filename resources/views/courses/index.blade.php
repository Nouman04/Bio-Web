@extends('layouts.app')

@section('title', 'Courses')
@section('meta-description', 'Manage courses and content catalog in EduAdmin LMS.')

@section('page-title', 'Course Management')
@section('page-subtitle', 'Manage and organize your educational content catalog.')

@push('styles')
<style>
    /* ── Courses table ──────────────────────────────────────────────────────
       DataTables ships its own chrome; these rules fold it into the panel so
       the grid reads as one quiet surface rather than a widget dropped in. */
    .courses-panel { overflow: hidden; }
    #courses-table_wrapper { padding: 0.25rem 0 0; font-size: 0.875rem; }

    /* Header */
    #courses-table thead th {
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
    .dark #courses-table thead th {
        color: rgb(148, 163, 184);
        background: rgba(15, 23, 42, 0.4);
        border-bottom-color: rgb(51, 65, 85);
    }
    #courses-table.dataTable thead th.dt-orderable-asc:hover,
    #courses-table.dataTable thead th.dt-orderable-desc:hover { color: #001330; }

    /* DataTables' own `table.dataTable thead>tr>th` rule outranks utility classes,
       so the last two columns are aligned here to keep header and cell in line. */
    #courses-table.dataTable thead > tr > th:nth-child(4),
    #courses-table.dataTable tbody > tr > td:nth-child(4),
    #courses-table.dataTable thead > tr > th:nth-child(5),
    #courses-table.dataTable tbody > tr > td:nth-child(5) { text-align: center; }

    /* Keep the sort arrows tight against the centred header labels */
    #courses-table.dataTable thead > tr > th:nth-child(4) span.dt-column-order { position: static; }

    /* Body */
    #courses-table tbody td {
        padding: 0.9375rem 1.5rem;
        vertical-align: middle;
        border-top: none;
        border-bottom: 1px solid rgba(118, 117, 134, 0.08);
    }
    .dark #courses-table tbody td { border-bottom-color: rgba(51, 65, 85, 0.6); }
    #courses-table tbody tr:last-child td { border-bottom: none; }
    #courses-table tbody tr { transition: background-color 0.15s ease; }
    #courses-table tbody tr:hover { background: rgba(0, 19, 48, 0.035); }
    .dark #courses-table tbody tr:hover { background: rgba(0, 19, 48, 0.12); }
    #courses-table.dataTable tbody tr.odd,
    #courses-table.dataTable tbody tr.even,
    #courses-table.dataTable tbody tr > .sorting_1 { background: transparent; box-shadow: none; }
    #courses-table tbody td.dt-empty {
        padding: 3.5rem 1.5rem;
        text-align: center;
        color: rgb(118, 117, 134);
    }

    /* Footer chrome: length menu, info line, pagination */
    #courses-table_wrapper .dt-layout-row:last-child {
        padding: 0.875rem 1.5rem;
        border-top: 1px solid rgba(118, 117, 134, 0.12);
        background: rgba(242, 244, 246, 0.35);
    }
    .dark #courses-table_wrapper .dt-layout-row:last-child {
        border-top-color: rgb(51, 65, 85);
        background: rgba(15, 23, 42, 0.35);
    }
    #courses-table_wrapper .dt-layout-row:first-child { padding: 0.875rem 1.5rem 0.25rem; }
    #courses-table_wrapper .dt-length,
    #courses-table_wrapper .dt-info {
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(118, 117, 134);
    }
    .dark #courses-table_wrapper .dt-length,
    .dark #courses-table_wrapper .dt-info { color: rgb(148, 163, 184); }
    #courses-table_wrapper select {
        background: #ffffff;
        border: 1px solid rgba(118, 117, 134, 0.3);
        border-radius: 0.625rem;
        padding: 0.25rem 0.5rem;
        margin: 0 0.375rem;
        outline: none;
    }
    .dark #courses-table_wrapper select {
        background: rgb(15, 23, 42);
        border-color: rgb(51, 65, 85);
        color: rgb(226, 232, 240);
    }
    #courses-table_wrapper .dt-paging .dt-paging-button {
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
    #courses-table_wrapper .dt-paging .dt-paging-button:hover:not(.disabled) {
        background: rgba(0, 19, 48, 0.08) !important;
        color: #001330 !important;
    }
    #courses-table_wrapper .dt-paging .dt-paging-button.current {
        background: #001330 !important;
        color: #ffffff !important;
    }
    #courses-table_wrapper .dt-paging .dt-paging-button.disabled { opacity: 0.4; }

    /* The shimmer below stands in for DataTables' "Processing..." box */
    #courses-table_wrapper .dt-processing { display: none !important; }

    /* Loading shimmer: real <tr>s inside the table body, so the skeleton
       occupies exactly the rows' space and never covers the header or footer. */
    tr.courses-shimmer-row td > .shimmer-bar + .shimmer-bar { margin-top: 0.4375rem; }

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
        color: var(--tw-color-primary, #001330);
        border-color: rgba(0, 19, 48, 0.35);
        background: rgba(0, 19, 48, 0.08);
    }
</style>
@endpush

@section('content')

    {{-- Ambient Background Glow --}}
    <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-primary/5 to-transparent pointer-events-none -z-10"></div>

    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Courses</span>
    </div>

    @php
        $filtersOpen = request()->hasAny(['search', 'category', 'created_by']);
    @endphp

    {{-- Toolbar: Filter toggle + Add button --}}
    <div class="flex justify-end items-center gap-3 mb-4">
        <button type="button" id="filterToggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-800 text-on-surface-variant dark:text-slate-300 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-colors shadow-sm {{ $filtersOpen ? 'is-active' : '' }}"
            title="Toggle Filters">
            <i class="fa-solid fa-filter text-sm"></i>
        </button>
        <button type="button" onclick="openAddCourseModal()"
            class="flex items-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            Add Course
        </button>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div class="filter-card-panel glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <form id="courses-filter-form" action="{{ route('courses') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Search</label>
                            <div class="relative group">
                                <span class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-sm"></span>
                                <input name="search" value="{{ $filters['search'] ?? '' }}"
                                    class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none"
                                    placeholder="Search by title or instructor..." type="text">
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Category</label>
                            <select name="category" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Categories</option>
                                @forelse($categories as $category)
                                    <option value="{{ $category->uuid }}" {{ ($filters['category'] ?? '') == $category->uuid ? 'selected' : '' }}>{{ $category->title }}</option>
                                @empty
                                    <option value="" disabled>No categories yet</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Instructor</label>
                            <select name="created_by" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Instructors</option>
                                @forelse($instructors as $instructor)
                                    <option value="{{ $instructor->uuid }}" {{ ($filters['created_by'] ?? '') == $instructor->uuid ? 'selected' : '' }}>{{ $instructor->name }}</option>
                                @empty
                                    <option value="" disabled>No instructors yet</option>
                                @endforelse
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <button type="button" id="coursesClearFilters" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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

    {{-- Course List Data Table (server-side, Yajra DataTables) --}}
    <div class="courses-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto w-full">
            <table id="courses-table" class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="w-2/5">Course</th>
                        <th>Category</th>
                        <th>Created By</th>
                        <th>Chapters</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- One skeleton row, cloned into the table body while a draw is in flight --}}
    <template id="courses-shimmer-row">
        <tr class="courses-shimmer-row" aria-hidden="true">
            <td>
                <span class="shimmer-bar" style="width:55%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:35%"></span>
            </td>
            <td><span class="shimmer-bar shimmer-pill" style="width:70%"></span></td>
            <td>
                <span class="shimmer-row-inline">
                    <span class="shimmer-avatar"></span>
                    <span class="shimmer-bar" style="width:60%"></span>
                </span>
            </td>
            <td><span class="shimmer-bar shimmer-chip"></span></td>
            <td><span class="shimmer-bar shimmer-dots"></span></td>
        </tr>
    </template>

    {{-- Add Course Modal (exact markup from html/courses/modal.html) --}}
    <div id="add-course-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeAddCourseModal()"></div>
        <!-- Panel -->
        <div class="relative w-full max-w-2xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Add New Course</h3>
                <button type="button" onclick="closeAddCourseModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="add-course-form" data-ajax-form action="{{ route('courses.store') }}" method="POST" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Category</label>
                    <select name="category_id" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        <option value="">Select a category</option>
                        @forelse($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->title }}</option>
                        @empty
                            <option value="" disabled>No categories yet</option>
                        @endforelse
                    </select>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Title</label>
                    <input name="title" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="Enter course title" type="text" oninput="autoGenerateSlug(this.value)">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Slug</label>
                    <input id="modal-slug-input" name="slug" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="e.g. advanced-react-patterns" type="text">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Description</label>
                    <textarea name="description" data-quill data-quill-no-attachments data-quill-height="150px" placeholder="Provide a brief overview of the course content"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAddCourseModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Creating…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Create Course</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Course Modal --}}
    <div id="edit-course-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeEditCourseModal()"></div>
        <!-- Panel -->
        <div class="relative w-full max-w-2xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Edit Course</h3>
                <button type="button" onclick="closeEditCourseModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="edit-course-form" data-ajax-form action="#" method="POST" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                @method('PUT')
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Category</label>
                    <select id="edit-course-category" name="category_id" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        <option value="">Select a category</option>
                        @forelse($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->title }}</option>
                        @empty
                            <option value="" disabled>No categories yet</option>
                        @endforelse
                    </select>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Title</label>
                    <input id="edit-course-title" name="title" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="Enter course title" type="text">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Slug</label>
                    <input id="edit-course-slug" name="slug" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="e.g. advanced-react-patterns" type="text">
                    <p class="text-xs text-outline dark:text-slate-500">Changing the slug changes the course's public URL.</p>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Description</label>
                    <textarea id="edit-course-description" name="description" data-quill data-quill-no-attachments data-quill-height="150px" placeholder="Provide a brief overview of the course content"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditCourseModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Saving…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let coursesTable = null;

        document.addEventListener('DOMContentLoaded', () => {
            const filterToggle = document.getElementById('filterToggle');
            const filterCardWrapper = document.getElementById('filterCardWrapper');

            filterToggle?.addEventListener('click', () => {
                const isOpen = filterCardWrapper?.classList.toggle('is-open');
                filterToggle.classList.toggle('is-active', isOpen);
            });

            const filterForm = document.getElementById('courses-filter-form');

            // Server-side table: paging, ordering and filtering all happen in
            // CourseController@data, so only the visible page is ever loaded.
            // Server-side table: paging, ordering and filtering all happen in
            // CourseController@data, so only the visible page is ever loaded.
            coursesTable = new DataTable('#courses-table', {
                processing: true,
                serverSide: true,
                searching: false,
                lengthMenu: [10, 25, 50, 100],
                pageLength: 10,
                order: [[0, 'asc']],
                language: {
                    emptyTable: 'No courses found.',
                    zeroRecords: 'No courses match these filters.',
                },
                ajax: {
                    url: '{{ route('courses.data') }}',
                    // jQuery caches GET requests by default, which would make
                    // ajax.reload() after an edit replay the stale response.
                    cache: false,
                    data: (params) => {
                        const filters = new FormData(filterForm);
                        // DataTables reserves `search`, so the filter box travels
                        // as search_term and is mapped back on the server.
                        params.search_term = filters.get('search') ?? '';
                        params.category = filters.get('category') ?? '';
                        params.created_by = filters.get('created_by') ?? '';
                        return params;
                    },
                },
                columns: [
                    { data: 'title_cell', name: 'title' },
                    { data: 'category_name', name: 'category.title', orderable: false },
                    { data: 'creator_name', name: 'creator.name', orderable: false },
                    { data: 'chapters_cell', name: 'chapters_count', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center whitespace-nowrap' },
                ],
            });

            // Swap DataTables' processing box for skeleton rows. They go inside the
            // table body, so the shimmer covers the rows only — never the header or
            // the pagination footer. DataTables wipes them when the draw lands.
            const shimmerTemplate = document.getElementById('courses-shimmer-row');
            const tableBody = document.querySelector('#courses-table tbody');

            function showShimmer() {
                if (!shimmerTemplate || !tableBody) return;

                tableBody.replaceChildren();
                for (let row = 0; row < 6; row++) {
                    tableBody.appendChild(shimmerTemplate.content.cloneNode(true));
                }
            }

            showShimmer();
            $('#courses-table').on('processing.dt', (e, settings, processing) => {
                if (processing) showShimmer();
            });

            // ── Add / Edit submit over AJAX ─────────────────────────────────
            // App.bindForms() already attached the submit handling; these just
            // react to the outcome.
            const addForm = document.getElementById('add-course-form');
            const editForm = document.getElementById('edit-course-form');

            addForm?.addEventListener('ajax:success', () => {
                closeAddCourseModal();
                resetCourseForm(addForm);
                coursesTable.ajax.reload(null, false);
            });

            editForm?.addEventListener('ajax:success', () => {
                closeEditCourseModal();
                coursesTable.ajax.reload(null, false);
            });

            filterForm?.addEventListener('submit', (e) => {
                e.preventDefault();
                coursesTable.ajax.reload();
            });

            document.getElementById('coursesClearFilters')?.addEventListener('click', () => {
                filterForm.reset();
                filterForm.querySelectorAll('select').forEach(select => { select.value = ''; });
                filterForm.querySelector('input[name="search"]').value = '';
                coursesTable.ajax.reload();
            });
        });

        function openAddCourseModal() {
            document.getElementById('add-course-modal-container').classList.remove('hidden');
        }

        function closeAddCourseModal() {
            document.getElementById('add-course-modal-container').classList.add('hidden');
        }

        // Fills the edit modal from the row's data-* attributes before opening it.
        // The description textarea has been upgraded to a Quill editor by the layout,
        // so its content is set through the handle the initializer attaches.
        function openEditCourseModal(trigger) {
            const form = document.getElementById('edit-course-form');

            // The row decides which course this submit updates.
            form.action = `{{ url('courses') }}/${trigger.dataset.id}`;
            App.clearFieldErrors(form);

            selectCourseCategory(trigger);
            document.getElementById('edit-course-title').value = trigger.dataset.title ?? '';
            document.getElementById('edit-course-slug').value = trigger.dataset.slug ?? '';

            const description = document.getElementById('edit-course-description');
            if (description.setQuillContent) {
                description.setQuillContent(trigger.dataset.description ?? '');
            } else {
                description.value = trigger.dataset.description ?? '';
            }

            document.getElementById('edit-course-modal-container').classList.remove('hidden');
        }

        // Clears inputs plus the Quill editor, which a native form.reset() misses.
        function resetCourseForm(form) {
            form.reset();
            App.clearFieldErrors(form);
            form.querySelectorAll('textarea[data-quill]').forEach(textarea => {
                textarea.setQuillContent?.('');
            });
        }

        function closeEditCourseModal() {
            document.getElementById('edit-course-modal-container').classList.add('hidden');
        }

        // Category options carry the database id. Prefer the row's category_id, and
        // fall back to matching the option label while courses still come from the
        // mock listing and carry only a category name.
        function selectCourseCategory(trigger) {
            const select = document.getElementById('edit-course-category');
            const categoryId = trigger.dataset.categoryId ?? '';

            if (categoryId) {
                select.value = categoryId;
                return;
            }

            const name = (trigger.dataset.category ?? '').trim();
            const match = Array.from(select.options).find(option => option.textContent.trim() === name);
            select.value = match ? match.value : '';
        }

        // Deletes through the courses.destroy endpoint, then refreshes the table.
        async function deleteCourse(trigger) {
            const title = trigger.dataset.title ?? 'this course';

            const confirmed = await App.confirmDelete({
                title: 'Delete course?',
                text: `“${title}” and everything under it will be removed from the listing.`,
            });
            if (!confirmed) return;

            try {
                const payload = await App.request(`{{ url('courses') }}/${trigger.dataset.id}`, { method: 'DELETE' });
                App.toast('success', payload.message || 'Course deleted successfully.');
                coursesTable?.ajax.reload(null, false);
            } catch (error) {
                App.toast('error', error.message);
            }
        }

        function autoGenerateSlug(title) {
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)+/g, '');
            const slugInput = document.getElementById('modal-slug-input');
            if (slugInput) {
                slugInput.value = slug;
            }
        }
    </script>
@endpush
