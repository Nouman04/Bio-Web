@extends('layouts.app')

@section('title', 'Students')
@section('meta-description', 'Students subscribed to your courses.')

@section('page-title', 'Students')
@section('page-subtitle', 'Everyone subscribed to your courses, and what of theirs is waiting to be marked.')

@push('styles')
<style>
    /* ── Students table ─────────────────────────────────────────────────────
       DataTables ships its own chrome; these rules fold it into the panel so
       the grid reads as one quiet surface rather than a widget dropped in.
       Same treatment as the courses grid. */
    .students-panel { overflow: hidden; }
    #students-table_wrapper { padding: 0.25rem 0 0; font-size: 0.875rem; }

    /* Header */
    #students-table thead th {
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
    .dark #students-table thead th {
        color: rgb(148, 163, 184);
        background: rgba(15, 23, 42, 0.4);
        border-bottom-color: rgb(51, 65, 85);
    }
    #students-table.dataTable thead th.dt-orderable-asc:hover,
    #students-table.dataTable thead th.dt-orderable-desc:hover { color: #001330; }

    /* DataTables' own `table.dataTable thead>tr>th` rule outranks utility classes,
       so the last two columns are aligned here to keep header and cell in line. */
    #students-table.dataTable thead > tr > th:nth-child(4),
    #students-table.dataTable tbody > tr > td:nth-child(4),
    #students-table.dataTable thead > tr > th:nth-child(5),
    #students-table.dataTable tbody > tr > td:nth-child(5) { text-align: center; }

    /* Keep the sort arrows tight against the centred header labels */
    #students-table.dataTable thead > tr > th:nth-child(4) span.dt-column-order { position: static; }

    /* Body */
    #students-table tbody td {
        padding: 0.9375rem 1.5rem;
        vertical-align: middle;
        border-top: none;
        border-bottom: 1px solid rgba(118, 117, 134, 0.08);
    }
    .dark #students-table tbody td { border-bottom-color: rgba(51, 65, 85, 0.6); }
    #students-table tbody tr:last-child td { border-bottom: none; }
    #students-table tbody tr { transition: background-color 0.15s ease; }
    #students-table tbody tr:hover { background: rgba(0, 19, 48, 0.035); }
    .dark #students-table tbody tr:hover { background: rgba(0, 19, 48, 0.12); }
    #students-table.dataTable tbody tr.odd,
    #students-table.dataTable tbody tr.even,
    #students-table.dataTable tbody tr > .sorting_1 { background: transparent; box-shadow: none; }
    #students-table tbody td.dt-empty {
        padding: 3.5rem 1.5rem;
        text-align: center;
        color: rgb(118, 117, 134);
    }

    /* Footer chrome: length menu, info line, pagination */
    #students-table_wrapper .dt-layout-row:last-child {
        padding: 0.875rem 1.5rem;
        border-top: 1px solid rgba(118, 117, 134, 0.12);
        background: rgba(242, 244, 246, 0.35);
    }
    .dark #students-table_wrapper .dt-layout-row:last-child {
        border-top-color: rgb(51, 65, 85);
        background: rgba(15, 23, 42, 0.35);
    }
    #students-table_wrapper .dt-layout-row:first-child { padding: 0.875rem 1.5rem 0.25rem; }
    #students-table_wrapper .dt-length,
    #students-table_wrapper .dt-info {
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(118, 117, 134);
    }
    .dark #students-table_wrapper .dt-length,
    .dark #students-table_wrapper .dt-info { color: rgb(148, 163, 184); }
    #students-table_wrapper select {
        background: #ffffff;
        border: 1px solid rgba(118, 117, 134, 0.3);
        border-radius: 0.625rem;
        padding: 0.25rem 0.5rem;
        margin: 0 0.375rem;
        outline: none;
    }
    .dark #students-table_wrapper select {
        background: rgb(15, 23, 42);
        border-color: rgb(51, 65, 85);
        color: rgb(226, 232, 240);
    }
    #students-table_wrapper .dt-paging .dt-paging-button {
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
    #students-table_wrapper .dt-paging .dt-paging-button:hover:not(.disabled) {
        background: rgba(0, 19, 48, 0.08) !important;
        color: #001330 !important;
    }
    #students-table_wrapper .dt-paging .dt-paging-button.current {
        background: #001330 !important;
        color: #ffffff !important;
    }
    #students-table_wrapper .dt-paging .dt-paging-button.disabled { opacity: 0.4; }

    /* The shimmer below stands in for DataTables' "Processing..." box */
    #students-table_wrapper .dt-processing { display: none !important; }

    tr.students-shimmer-row td > .shimmer-bar + .shimmer-bar { margin-top: 0.4375rem; }

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
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Students</span>
    </div>

    {{-- Headline figures --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <span class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <i class="fa-solid fa-user-group"></i>
            </span>
            <div>
                <p class="text-2xl font-bold text-on-surface dark:text-white leading-tight">{{ $stats['total'] }}</p>
                <p class="text-xs text-on-surface-variant dark:text-slate-400">Subscribed students</p>
            </div>
        </div>
        <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <span class="w-11 h-11 rounded-xl bg-error/10 text-error flex items-center justify-center">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </span>
            <div>
                <p class="text-2xl font-bold text-on-surface dark:text-white leading-tight">{{ $stats['papers'] }}</p>
                <p class="text-xs text-on-surface-variant dark:text-slate-400">Papers waiting to be marked</p>
            </div>
        </div>
        <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <span class="w-11 h-11 rounded-xl bg-tertiary/10 text-tertiary flex items-center justify-center">
                <i class="fa-solid fa-user-clock"></i>
            </span>
            <div>
                <p class="text-2xl font-bold text-on-surface dark:text-white leading-tight">{{ $stats['pending'] }}</p>
                <p class="text-xs text-on-surface-variant dark:text-slate-400">Students waiting on you</p>
            </div>
        </div>
    </div>

    @php
        $filtersOpen = request()->hasAny(['search', 'course', 'status']);
    @endphp

    {{-- Toolbar --}}
    <div class="flex justify-between items-center gap-3 mb-4">
        <a href="{{ route('quizzes.review') }}"
            class="flex items-center gap-2 text-sm font-semibold text-on-surface-variant dark:text-slate-300 hover:text-primary transition-colors">
            <i class="fa-solid fa-list-check text-xs"></i>
            Everything awaiting review
        </a>
        <button type="button" id="filterToggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-800 text-on-surface-variant dark:text-slate-300 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-colors shadow-sm {{ $filtersOpen ? 'is-active' : '' }}"
            title="Toggle Filters">
            <i class="fa-solid fa-filter text-sm"></i>
        </button>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div class="filter-card-panel glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <form id="students-filter-form" action="{{ route('students') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Search</label>
                            <div class="relative group">
                                <span class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-sm"></span>
                                <input name="search" value="{{ $filters['search'] ?? '' }}"
                                    class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none"
                                    placeholder="Search by name or email..." type="text">
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Course</label>
                            <select name="course" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Courses</option>
                                @forelse($courses as $course)
                                    <option value="{{ $course->uuid }}" {{ ($filters['course'] ?? '') == $course->uuid ? 'selected' : '' }}>{{ $course->title }}</option>
                                @empty
                                    <option value="" disabled>No courses yet</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Marking</label>
                            <select name="status" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">Everyone</option>
                                <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Has papers waiting</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <button type="button" id="studentsClearFilters" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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

    {{-- Student roster (server-side, Yajra DataTables) --}}
    <div class="students-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto w-full">
            <table id="students-table" class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="w-2/5">Student</th>
                        <th>Email</th>
                        <th>Joined</th>
                        <th>Pending Quizzes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- One skeleton row, cloned into the table body while a draw is in flight --}}
    <template id="students-shimmer-row">
        <tr class="students-shimmer-row" aria-hidden="true">
            <td>
                <span class="shimmer-row-inline">
                    <span class="shimmer-avatar"></span>
                    <span class="shimmer-bar" style="width:55%"></span>
                </span>
            </td>
            <td><span class="shimmer-bar" style="width:70%"></span></td>
            <td>
                <span class="shimmer-bar" style="width:50%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:35%"></span>
            </td>
            <td><span class="shimmer-bar shimmer-chip"></span></td>
            <td><span class="shimmer-bar shimmer-dots"></span></td>
        </tr>
    </template>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const filterForm = document.getElementById('students-filter-form');

        // Server-side table: paging, ordering and filtering all happen in
        // StudentController@data, so only the visible page is ever loaded.
        const studentsTable = new DataTable('#students-table', {
            serverSide: true,
            processing: true,
            searching: false,
            lengthMenu: [10, 25, 50, 100],
            pageLength: 10,
            order: [[3, 'desc']],
            language: {
                emptyTable: 'Nobody is subscribed to your courses yet.',
                zeroRecords: 'No students match these filters.',
            },
            ajax: {
                url: '{{ route('students.data') }}',
                // jQuery caches GET requests by default, which would make
                // ajax.reload() replay the stale response.
                cache: false,
                data: (params) => {
                    const filters = new FormData(filterForm);
                    // DataTables reserves `search`, so the filter box travels
                    // as search_term and is mapped back on the server.
                    params.search_term = filters.get('search') ?? '';
                    params.course = filters.get('course') ?? '';
                    params.status = filters.get('status') ?? '';
                    return params;
                },
            },
            columns: [
                { data: 'student_cell', name: 'name' },
                { data: 'email_cell', name: 'email', orderable: false },
                { data: 'joined_cell', name: 'created_at' },
                { data: 'pending_cell', name: 'pending_quizzes_count', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center whitespace-nowrap' },
            ],
        });

        // Swap DataTables' processing box for skeleton rows. They go inside the
        // table body, so the shimmer covers the rows only — never the header or
        // the pagination footer. DataTables wipes them when the draw lands.
        const shimmerTemplate = document.getElementById('students-shimmer-row');
        const tableBody = document.querySelector('#students-table tbody');

        function showShimmer() {
            if (!shimmerTemplate || !tableBody) return;

            tableBody.replaceChildren();
            for (let row = 0; row < 6; row++) {
                tableBody.appendChild(shimmerTemplate.content.cloneNode(true));
            }
        }

        showShimmer();
        $('#students-table').on('processing.dt', (e, settings, processing) => {
            if (processing) showShimmer();
        });

        filterForm?.addEventListener('submit', (e) => {
            e.preventDefault();
            studentsTable.ajax.reload();
        });

        document.getElementById('studentsClearFilters')?.addEventListener('click', () => {
            filterForm.reset();
            studentsTable.ajax.reload();
        });

        // ── Filter card toggle ──────────────────────────────────────────────
        const filterToggle = document.getElementById('filterToggle');
        const filterWrapper = document.getElementById('filterCardWrapper');

        filterToggle?.addEventListener('click', () => {
            filterWrapper.classList.toggle('is-open');
            filterToggle.classList.toggle('is-active', filterWrapper.classList.contains('is-open'));
        });
    });
</script>
@endpush
