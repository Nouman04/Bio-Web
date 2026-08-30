@extends('layouts.app')

@section('title', 'Diagrams')
@section('meta-description', 'Manage diagrams and visual assets in Your Biology.')

@section('page-title', 'Diagram Management')
@section('page-subtitle', 'Upload and organise the visuals used across your curriculum.')

@push('styles')
<style>
    /* ── Diagrams table ─────────────────────────────────────────────────────
       Same treatment as the courses, chapters and summaries grids: DataTables'
       own chrome folded into the panel so it reads as one quiet surface. */
    .diagrams-panel { overflow: hidden; }
    #diagrams-table_wrapper { padding: 0.25rem 0 0; font-size: 0.875rem; }

    /* Header */
    #diagrams-table thead th {
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
    .dark #diagrams-table thead th {
        color: rgb(148, 163, 184);
        background: rgba(15, 23, 42, 0.4);
        border-bottom-color: rgb(51, 65, 85);
    }
    #diagrams-table.dataTable thead th.dt-orderable-asc:hover,
    #diagrams-table.dataTable thead th.dt-orderable-desc:hover { color: #001330; }

    /* DataTables' own `table.dataTable thead>tr>th` rule outranks utility classes,
       so the centred columns are aligned here to keep header and cell in line. */
    #diagrams-table.dataTable thead > tr > th:nth-child(1),
    #diagrams-table.dataTable tbody > tr > td:nth-child(1),
    #diagrams-table.dataTable thead > tr > th:nth-child(6),
    #diagrams-table.dataTable tbody > tr > td:nth-child(6) { text-align: center; }

    /* Body */
    #diagrams-table tbody td {
        padding: 0.9375rem 1.5rem;
        vertical-align: middle;
        border-top: none;
        border-bottom: 1px solid rgba(118, 117, 134, 0.08);
    }
    .dark #diagrams-table tbody td { border-bottom-color: rgba(51, 65, 85, 0.6); }
    #diagrams-table tbody tr:last-child td { border-bottom: none; }
    #diagrams-table tbody tr { transition: background-color 0.15s ease; }
    #diagrams-table tbody tr:hover { background: rgba(0, 19, 48, 0.035); }
    .dark #diagrams-table tbody tr:hover { background: rgba(0, 19, 48, 0.12); }
    #diagrams-table.dataTable tbody tr.odd,
    #diagrams-table.dataTable tbody tr.even,
    #diagrams-table.dataTable tbody tr > .sorting_1 { background: transparent; box-shadow: none; }
    #diagrams-table tbody td.dt-empty {
        padding: 3.5rem 1.5rem;
        text-align: center;
        color: rgb(118, 117, 134);
    }

    /* Footer chrome: length menu, info line, pagination */
    #diagrams-table_wrapper .dt-layout-row:last-child {
        padding: 0.875rem 1.5rem;
        border-top: 1px solid rgba(118, 117, 134, 0.12);
        background: rgba(242, 244, 246, 0.35);
    }
    .dark #diagrams-table_wrapper .dt-layout-row:last-child {
        border-top-color: rgb(51, 65, 85);
        background: rgba(15, 23, 42, 0.35);
    }
    #diagrams-table_wrapper .dt-layout-row:first-child { padding: 0.875rem 1.5rem 0.25rem; }
    #diagrams-table_wrapper .dt-length,
    #diagrams-table_wrapper .dt-info {
        font-size: 0.75rem;
        font-weight: 500;
        color: rgb(118, 117, 134);
    }
    .dark #diagrams-table_wrapper .dt-length,
    .dark #diagrams-table_wrapper .dt-info { color: rgb(148, 163, 184); }
    #diagrams-table_wrapper select {
        background: #ffffff;
        border: 1px solid rgba(118, 117, 134, 0.3);
        border-radius: 0.625rem;
        padding: 0.25rem 0.5rem;
        margin: 0 0.375rem;
        outline: none;
    }
    .dark #diagrams-table_wrapper select {
        background: rgb(15, 23, 42);
        border-color: rgb(51, 65, 85);
        color: rgb(226, 232, 240);
    }
    #diagrams-table_wrapper .dt-paging .dt-paging-button {
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
    #diagrams-table_wrapper .dt-paging .dt-paging-button:hover:not(.disabled) {
        background: rgba(0, 19, 48, 0.08) !important;
        color: #001330 !important;
    }
    #diagrams-table_wrapper .dt-paging .dt-paging-button.current {
        background: #001330 !important;
        color: #ffffff !important;
    }
    #diagrams-table_wrapper .dt-paging .dt-paging-button.disabled { opacity: 0.4; }

    /* The shimmer below stands in for DataTables' "Processing..." box */
    #diagrams-table_wrapper .dt-processing { display: none !important; }

    /* Loading shimmer: real <tr>s inside the table body, so the skeleton
       occupies exactly the rows' space and never covers the header or footer. */
    tr.diagrams-shimmer-row td > .shimmer-bar + .shimmer-bar { margin-top: 0.4375rem; }
    .shimmer-thumb { width: 3.5rem; height: 3.5rem; border-radius: 0.5rem; margin: 0 auto; }

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
    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Diagrams</span>
    </div>

    @php
        $filtersOpen = request()->hasAny(['title', 'topic', 'date_from', 'date_to']);
    @endphp

    {{-- Toolbar: Filter toggle + Add button --}}
    <div class="flex justify-end items-center gap-3 mb-4">
        <button type="button" id="filterToggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-800 text-on-surface-variant dark:text-slate-300 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-colors shadow-sm {{ $filtersOpen ? 'is-active' : '' }}"
            title="Toggle Filters">
            <i class="fa-solid fa-filter text-sm"></i>
        </button>
        <button type="button" onclick="openAddDiagramModal()"
            class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            Add New Diagram
        </button>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div class="filter-card-panel glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <form id="diagrams-filter-form" action="{{ route('diagrams') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic</label>
                            <select name="topic" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Topics</option>
                                @forelse($topics as $topic)
                                    <option value="{{ $topic->uuid }}" {{ ($filters['topic'] ?? '') == $topic->uuid ? 'selected' : '' }}>{{ $topic->title }}</option>
                                @empty
                                    <option value="" disabled>No topics yet</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Added Between</label>
                            <div class="flex items-center gap-2">
                                <input name="date_from" value="{{ $filters['date_from'] ?? '' }}" type="date"
                                    class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <span class="text-xs text-on-surface-variant">to</span>
                                <input name="date_to" value="{{ $filters['date_to'] ?? '' }}" type="date"
                                    class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <button type="button" id="diagramsClearFilters" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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

    {{-- Diagram List Data Table (server-side, Yajra DataTables) --}}
    <div class="diagrams-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto w-full">
            <table id="diagrams-table" class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="w-24">Preview</th>
                        <th class="w-1/3">Diagram</th>
                        <th>Chapter</th>
                        <th>Topic</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- One skeleton row, cloned into the table body while a draw is in flight --}}
    <template id="diagrams-shimmer-row">
        <tr class="diagrams-shimmer-row" aria-hidden="true">
            <td><span class="shimmer-bar shimmer-thumb"></span></td>
            <td>
                <span class="shimmer-bar" style="width:55%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:35%"></span>
            </td>
            <td><span class="shimmer-bar shimmer-pill" style="width:70%"></span></td>
            <td><span class="shimmer-bar shimmer-pill" style="width:60%"></span></td>
            <td>
                <span class="shimmer-bar" style="width:60%"></span>
                <span class="shimmer-bar shimmer-bar-sm" style="width:40%"></span>
            </td>
            <td><span class="shimmer-bar shimmer-dots"></span></td>
        </tr>
    </template>

    {{-- Add Diagram Modal --}}
    <div id="add-diagram-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeAddDiagramModal()"></div>
        <div class="relative w-full max-w-3xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Add New Diagram</h3>
                <button type="button" onclick="closeAddDiagramModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="add-diagram-form" data-ajax-form action="{{ route('diagrams.store') }}" method="POST" enctype="multipart/form-data" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                {{-- Image --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Image</label>
                    <div class="relative group border-2 border-dashed border-outline-variant/60 dark:border-slate-600 bg-surface-container-low/50 dark:bg-slate-900/50 hover:border-primary dark:hover:border-primary hover:bg-primary/5 transition-colors rounded-xl flex flex-col items-center justify-center p-6 cursor-pointer overflow-hidden">
                        <img class="diagram-preview hidden max-h-40 rounded-lg mb-3 object-contain" alt="">
                        <div class="diagram-placeholder flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full bg-primary-container/20 text-primary flex items-center justify-center mb-2 group-hover:scale-110 transition-transform duration-300">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <p class="text-sm font-semibold text-on-surface dark:text-white">Click or drag an image to upload</p>
                            <p class="text-xs font-medium text-outline dark:text-slate-500 mt-1">JPG, PNG, GIF or WEBP up to 10MB</p>
                        </div>
                        <input name="image" type="file" accept="image/*" class="diagram-file absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter <span class="font-normal text-outline">(Optional)</span></label>
                        <select name="chapter_id" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            <option value="">Select a chapter</option>
                            @foreach($chapters as $chapter)
                                <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic <span class="font-normal text-outline">(Optional)</span></label>
                        <select name="topic_id" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            <option value="">Select a topic</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Title</label>
                    <input name="title" type="text" oninput="autoGenerateDiagramSlug(this.value, 'add')" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="Enter a descriptive title for this diagram">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Slug</label>
                    <input id="add-diagram-slug" name="slug" type="text" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="e.g. mitosis-diagram">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Description <span class="font-normal text-outline">(Optional)</span></label>
                    <textarea name="content" data-quill data-quill-height="180px" placeholder="Add context, alt text, or usage notes here..."></textarea>
                </div>
                @include('partials.question-widget', ['qwFieldName' => 'question_ids', 'qwLabel' => 'Associate with Questions (Optional)'])
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAddDiagramModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Uploading…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Create Diagram</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Diagram Modal --}}
    <div id="edit-diagram-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeEditDiagramModal()"></div>
        <div class="relative w-full max-w-3xl mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Edit Diagram</h3>
                <button type="button" onclick="closeEditDiagramModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="edit-diagram-form" data-ajax-form action="#" method="POST" enctype="multipart/form-data" class="p-6 flex flex-col gap-4 max-h-[75vh] overflow-y-auto">
                @csrf
                @method('PUT')
                {{-- Image --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Image</label>
                    <div class="relative group border-2 border-dashed border-outline-variant/60 dark:border-slate-600 bg-surface-container-low/50 dark:bg-slate-900/50 hover:border-primary dark:hover:border-primary hover:bg-primary/5 transition-colors rounded-xl flex flex-col items-center justify-center p-6 cursor-pointer overflow-hidden">
                        <img id="edit-diagram-preview" class="diagram-preview hidden max-h-40 rounded-lg mb-3 object-contain" alt="">
                        <div class="diagram-placeholder flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full bg-primary-container/20 text-primary flex items-center justify-center mb-2 group-hover:scale-110 transition-transform duration-300">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <p class="text-sm font-semibold text-on-surface dark:text-white">Click or drag to replace the image</p>
                            <p class="text-xs font-medium text-outline dark:text-slate-500 mt-1">Leave empty to keep the current one</p>
                        </div>
                        <input name="image" type="file" accept="image/*" class="diagram-file absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter <span class="font-normal text-outline">(Optional)</span></label>
                        <select id="edit-diagram-chapter" name="chapter_id" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            <option value="">Select a chapter</option>
                            @foreach($chapters as $chapter)
                                <option value="{{ $chapter->id }}">{{ $chapter->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic <span class="font-normal text-outline">(Optional)</span></label>
                        <select id="edit-diagram-topic" name="topic_id" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            <option value="">Select a topic</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Title</label>
                    <input id="edit-diagram-title" name="title" type="text" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="Enter a descriptive title for this diagram">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Slug</label>
                    <input id="edit-diagram-slug" name="slug" type="text" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="e.g. mitosis-diagram">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Description <span class="font-normal text-outline">(Optional)</span></label>
                    <textarea id="edit-diagram-content" name="content" data-quill data-quill-height="180px" placeholder="Add context, alt text, or usage notes here..."></textarea>
                </div>
                @include('partials.question-widget', ['qwFieldName' => 'question_ids', 'qwLabel' => 'Associate with Questions (Optional)'])
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditDiagramModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" data-loading-text="Saving…" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let diagramsTable = null;

        document.addEventListener('DOMContentLoaded', () => {
            const filterToggle = document.getElementById('filterToggle');
            const filterCardWrapper = document.getElementById('filterCardWrapper');

            filterToggle?.addEventListener('click', () => {
                const isOpen = filterCardWrapper?.classList.toggle('is-open');
                filterToggle.classList.toggle('is-active', isOpen);
            });

            const filterForm = document.getElementById('diagrams-filter-form');

            // Server-side table: paging, ordering and filtering all happen in
            // ImageController@data, so only the visible page is ever loaded.
            diagramsTable = App.dataTable('#diagrams-table', {
                order: [[4, 'desc']],
                shimmerTemplate: '#diagrams-shimmer-row',
                language: {
                    emptyTable: 'No diagrams uploaded yet.',
                    zeroRecords: 'No diagrams match these filters.',
                },
                ajax: {
                    url: '{{ route('diagrams.data') }}',
                    data: (params) => {
                        const filters = new FormData(filterForm);
                        // DataTables reserves `search`, so the filter box travels
                        // as search_term and is mapped back on the server.
                        params.search_term = filters.get('title') ?? '';
                        params.topic = filters.get('topic') ?? '';
                        params.date_from = filters.get('date_from') ?? '';
                        params.date_to = filters.get('date_to') ?? '';
                        return params;
                    },
                },
                columns: [
                    { data: 'preview_cell', name: 'image_path', orderable: false, searchable: false },
                    { data: 'title_cell', name: 'title' },
                    { data: 'chapter_cell', name: 'chapter.title', orderable: false },
                    { data: 'topic_cell', name: 'topic.title', orderable: false },
                    { data: 'date_cell', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center whitespace-nowrap' },
                ],
            });

            filterForm?.addEventListener('submit', (e) => {
                e.preventDefault();
                diagramsTable.ajax.reload();
            });

            document.getElementById('diagramsClearFilters')?.addEventListener('click', () => {
                filterForm.reset();
                filterForm.querySelectorAll('select').forEach(select => { select.value = ''; });
                filterForm.querySelectorAll('input').forEach(input => { input.value = ''; });
                diagramsTable.ajax.reload();
            });

            // Show the chosen file straight away in whichever modal it came from.
            document.querySelectorAll('.diagram-file').forEach(input => {
                input.addEventListener('change', () => {
                    const dropzone = input.closest('div');
                    const preview = dropzone.querySelector('.diagram-preview');
                    const placeholder = dropzone.querySelector('.diagram-placeholder');
                    const file = input.files[0];
                    if (!file) return;

                    preview.src = URL.createObjectURL(file);
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                });
            });

            // ── Add / Edit submit over AJAX ─────────────────────────────────
            const addForm = document.getElementById('add-diagram-form');
            const editForm = document.getElementById('edit-diagram-form');

            addForm?.addEventListener('ajax:success', () => {
                closeAddDiagramModal();
                resetDiagramForm(addForm);
                diagramsTable.ajax.reload(null, false);
            });

            editForm?.addEventListener('ajax:success', () => {
                closeEditDiagramModal();
                diagramsTable.ajax.reload(null, false);
            });
        });

        function openAddDiagramModal() {
            document.getElementById('add-diagram-modal-container').classList.remove('hidden');
        }

        function closeAddDiagramModal() {
            document.getElementById('add-diagram-modal-container').classList.add('hidden');
        }

        // Fills the edit modal from the row's data-* attributes, then fetches the
        // questions already linked so the picker opens pre-populated.
        function openEditDiagramModal(trigger) {
            const form = document.getElementById('edit-diagram-form');
            const id = trigger.dataset.id;

            form.action = `{{ url('diagrams') }}/${id}`;
            App.clearFieldErrors(form);

            document.getElementById('edit-diagram-chapter').value = trigger.dataset.chapterId ?? '';
            document.getElementById('edit-diagram-topic').value = trigger.dataset.topicId ?? '';
            document.getElementById('edit-diagram-title').value = trigger.dataset.title ?? '';
            document.getElementById('edit-diagram-slug').value = trigger.dataset.slug ?? '';

            const content = document.getElementById('edit-diagram-content');
            if (content.setQuillContent) {
                content.setQuillContent(trigger.dataset.content ?? '');
            } else {
                content.value = trigger.dataset.content ?? '';
            }

            // Show the current image; picking a file replaces it, leaving it keeps it.
            const preview = document.getElementById('edit-diagram-preview');
            const placeholder = preview.parentElement.querySelector('.diagram-placeholder');
            form.querySelector('.diagram-file').value = '';
            if (trigger.dataset.image) {
                preview.src = trigger.dataset.image;
                preview.classList.remove('hidden');
                placeholder.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
                placeholder.classList.remove('hidden');
            }

            const widget = form.querySelector('.question-widget');
            widget?.resetQuestions?.();
            App.request(`{{ url('diagrams') }}/${id}/questions`)
                .then(questions => widget?.setQuestions?.(questions))
                .catch(() => App.toast('error', 'Could not load the linked questions.'));

            document.getElementById('edit-diagram-modal-container').classList.remove('hidden');
        }

        function closeEditDiagramModal() {
            document.getElementById('edit-diagram-modal-container').classList.add('hidden');
        }

        // Clears inputs, the Quill editor, the preview and the question picker,
        // none of which a native form.reset() fully handles.
        function resetDiagramForm(form) {
            form.reset();
            App.clearFieldErrors(form);
            form.querySelectorAll('textarea[data-quill]').forEach(textarea => {
                textarea.setQuillContent?.('');
            });
            form.querySelector('.question-widget')?.resetQuestions?.();

            const preview = form.querySelector('.diagram-preview');
            preview?.classList.add('hidden');
            form.querySelector('.diagram-placeholder')?.classList.remove('hidden');
        }

        // Deletes through the diagrams.destroy endpoint, then refreshes the table.
        async function deleteDiagram(trigger) {
            const title = trigger.dataset.title ?? 'this diagram';

            const confirmed = await App.confirmDelete({
                title: 'Delete diagram?',
                text: `“${title}” will be removed from the listing.`,
            });
            if (!confirmed) return;

            try {
                const payload = await App.request(`{{ url('diagrams') }}/${trigger.dataset.id}`, { method: 'DELETE' });
                App.toast('success', payload.message || 'Diagram deleted successfully.');
                diagramsTable?.ajax.reload(null, false);
            } catch (error) {
                App.toast('error', error.message);
            }
        }

        function autoGenerateDiagramSlug(title, target) {
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)+/g, '');
            const input = document.getElementById(`${target}-diagram-slug`);
            if (input) input.value = slug;
        }
    </script>
@endpush
