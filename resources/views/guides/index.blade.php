@extends('layouts.app')

@section('title', 'Guides')
@section('meta-description', 'Manage and view educational guides.')

@section('page-title', 'Guides')
@section('page-subtitle', 'Design and structure comprehensive learning materials for the curriculum.')

@push('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .hover-ambient-shadow:hover {
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
    }
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
    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Guides</span>
    </div>

    @php
        $filtersOpen = request()->hasAny(['search', 'chapter', 'topic', 'type', 'date_from', 'date_to']);
    @endphp

    {{-- Toolbar: Filter toggle + Add button --}}
    <div class="flex justify-end items-center gap-3 mb-4">
        <button type="button" id="filterToggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-800 text-on-surface-variant dark:text-slate-300 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-colors shadow-sm {{ $filtersOpen ? 'is-active' : '' }}"
            title="Toggle Filters">
            <i class="fa-solid fa-filter text-sm"></i>
        </button>
        <a href="{{ route('guides.create') }}" class="flex items-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            Add New Guide
        </a>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div class="filter-card-panel glass-panel dark:bg-slate-800/80 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <form action="{{ route('guides') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Search</label>
                            <div class="relative group">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm"></i>
                                <input name="search" value="{{ $filters['search'] ?? '' }}" class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none" placeholder="Search title..." type="text">
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</label>
                            <select name="chapter" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Chapters</option>
                                <option value="1" {{ ($filters['chapter'] ?? '') == '1' ? 'selected' : '' }}>Chapter 1: Foundations</option>
                                <option value="2" {{ ($filters['chapter'] ?? '') == '2' ? 'selected' : '' }}>Chapter 2: Advanced Topics</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic</label>
                            <select name="topic" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Topics</option>
                                <option value="math" {{ ($filters['topic'] ?? '') == 'math' ? 'selected' : '' }}>Mathematics</option>
                                <option value="sci" {{ ($filters['topic'] ?? '') == 'sci' ? 'selected' : '' }}>Science</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Type</label>
                            <select name="type" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Types</option>
                                <option value="theory" {{ ($filters['type'] ?? '') == 'theory' ? 'selected' : '' }}>Theory Guide</option>
                                <option value="practical" {{ ($filters['type'] ?? '') == 'practical' ? 'selected' : '' }}>Practical Guide</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Date Range</label>
                            <div class="relative">
                                <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm pointer-events-none z-10"></i>
                                <input id="guides-date-range" name="date_range" type="text" value="{{ (($filters['date_from'] ?? '') && ($filters['date_to'] ?? '')) ? ($filters['date_from'] . ' to ' . $filters['date_to']) : '' }}" class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none" placeholder="Select date range" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <a href="{{ route('guides') }}" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
                            <i class="fa-solid fa-arrow-rotate-left text-xs"></i> Clear Filters
                        </a>
                        <button type="submit" class="px-4 py-2 bg-primary/10 text-primary text-sm font-semibold rounded-lg hover:bg-primary/20 transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="glass-panel dark:bg-slate-800/80 rounded-xl overflow-hidden hover-ambient-shadow transition-shadow duration-300 dark:border-slate-700 border-outline-variant/30 bg-white/50 dark:bg-slate-900/50">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="border-b border-outline-variant/20 dark:border-slate-700 bg-surface-container-lowest/50 dark:bg-slate-800/50">
                        <th class="p-4 text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Title</th>
                        <th class="p-4 text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Chapter</th>
                        <th class="p-4 text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Topic</th>
                        <th class="p-4 text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Type</th>
                        <th class="p-4 text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Created Date</th>
                        <th class="p-4 text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant/10 dark:divide-slate-700">
                    @forelse($guides as $guide)
                    <tr class="hover:bg-primary-fixed/10 dark:hover:bg-primary/5 transition-colors group bg-white/40 dark:bg-slate-800/40">
                        <td class="p-4 font-medium text-on-surface dark:text-slate-200">{{ $guide['title'] }}</td>
                        <td class="p-4 text-on-surface-variant dark:text-slate-400">{{ $guide['course'] }}</td>
                        <td class="p-4 text-on-surface-variant dark:text-slate-400">{{ $guide['author'] }}</td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs bg-primary-fixed-dim/20 text-primary border border-primary/20">Theory Guide</span>
                        </td>
                        <td class="p-4 text-on-surface-variant dark:text-slate-400">{{ $guide['last_updated'] }}</td>
                        <td class="p-4 text-right whitespace-nowrap">
                            <div class="relative inline-block text-left action-dropdown">
                                <button type="button" class="action-dropdown-trigger w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:text-primary hover:bg-primary/10 transition-colors" title="Actions">
                                    <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
                                </button>
                                <div class="action-dropdown-menu hidden absolute right-0 z-20 mt-1 w-44 rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 shadow-lg py-1">
                                    <button type="button" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
                                        <i class="fa-solid fa-pen w-4 text-on-surface-variant"></i>
                                        Edit
                                    </button>
                                    <div class="my-1 border-t border-outline-variant/20 dark:border-slate-700"></div>
                                    <button type="button" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-error hover:bg-error/5 transition-colors">
                                        <i class="fa-solid fa-trash w-4"></i>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-on-surface-variant dark:text-slate-500">
                            No guides found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination Placeholder --}}
        <div class="px-6 py-4 border-t border-outline-variant/10 dark:border-slate-700 flex items-center justify-between bg-surface-container-lowest/30 dark:bg-slate-800/30">
            <div class="text-sm text-on-surface-variant dark:text-slate-400">
                Showing <span class="font-medium text-on-surface dark:text-white">1</span> to <span class="font-medium text-on-surface dark:text-white">5</span> of <span class="font-medium text-on-surface dark:text-white">42</span> results
            </div>
            <div class="flex items-center gap-2">
                <button class="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-slate-700 transition-colors disabled:opacity-50" disabled>
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <div class="flex items-center gap-1">
                    <button class="w-8 h-8 rounded-lg bg-primary text-white text-sm flex items-center justify-center shadow-sm">1</button>
                    <button class="w-8 h-8 rounded-lg text-on-surface dark:text-slate-300 hover:bg-surface-container-highest dark:hover:bg-slate-700 text-sm flex items-center justify-center transition-colors">2</button>
                    <button class="w-8 h-8 rounded-lg text-on-surface dark:text-slate-300 hover:bg-surface-container-highest dark:hover:bg-slate-700 text-sm flex items-center justify-center transition-colors">3</button>
                </div>
                <button class="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const filterToggle = document.getElementById('filterToggle');
            const filterCardWrapper = document.getElementById('filterCardWrapper');

            filterToggle?.addEventListener('click', () => {
                const isOpen = filterCardWrapper?.classList.toggle('is-open');
                filterToggle.classList.toggle('is-active', isOpen);
            });

            flatpickr("#guides-date-range", {
            mode: "range",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    const form = instance.input.closest('form');
                    form.querySelectorAll('input[name="date_from"], input[name="date_to"]').forEach(el => el.remove());

                    const dateFromInput = document.createElement('input');
                    dateFromInput.type = 'hidden';
                    dateFromInput.name = 'date_from';
                    dateFromInput.value = flatpickr.formatDate(selectedDates[0], 'Y-m-d');

                    const dateToInput = document.createElement('input');
                    dateToInput.type = 'hidden';
                    dateToInput.name = 'date_to';
                    dateToInput.value = flatpickr.formatDate(selectedDates[1], 'Y-m-d');

                    form.appendChild(dateFromInput);
                    form.appendChild(dateToInput);
                }
            }
        });
        });
    </script>
    @endpush
@endsection
