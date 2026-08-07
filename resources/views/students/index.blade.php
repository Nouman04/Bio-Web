@extends('layouts.app')

@section('title', 'Students')
@section('meta-description', 'Manage and view all enrolled students in EduAdmin LMS.')

@section('page-title', 'Students')
@section('page-subtitle', 'Manage and view all enrolled students.')

@push('styles')
<style>
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
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Students</span>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 p-4 rounded-2xl border border-outline-variant/30 dark:border-slate-700 flex flex-col gap-1">
            <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Total</span>
            <span class="text-2xl font-bold text-primary dark:text-primary-fixed-dim">{{ $totalCount }}</span>
        </div>
        <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 p-4 rounded-2xl border border-outline-variant/30 dark:border-slate-700 flex flex-col gap-1">
            <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Active</span>
            <span class="text-2xl font-bold text-tertiary dark:text-tertiary-fixed-dim">{{ $activeCount }}</span>
        </div>
        <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 p-4 rounded-2xl border border-outline-variant/30 dark:border-slate-700 flex flex-col gap-1">
            <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Inactive</span>
            <span class="text-2xl font-bold text-on-surface-variant dark:text-slate-400">{{ $inactiveCount }}</span>
        </div>
        <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 p-4 rounded-2xl border border-outline-variant/30 dark:border-slate-700 flex flex-col gap-1">
            <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">New This Month</span>
            <span class="text-2xl font-bold text-secondary dark:text-secondary-fixed-dim">12</span>
        </div>
    </div>

    @php
        $filtersOpen = request()->hasAny(['search', 'course', 'status', 'date_from', 'date_to']);
    @endphp

    {{-- Toolbar: Filter toggle --}}
    <div class="flex justify-end items-center gap-3 mb-4">
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
                <form action="{{ route('students') }}" method="GET">
                    <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                    <input type="hidden" name="direction" value="{{ $filters['direction'] }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                                <option value="CS101" {{ ($filters['course'] ?? '') == 'CS101' ? 'selected' : '' }}>Computer Science 101</option>
                                <option value="PHYS101" {{ ($filters['course'] ?? '') == 'PHYS101' ? 'selected' : '' }}>Physics 101</option>
                                <option value="CHEM101" {{ ($filters['course'] ?? '') == 'CHEM101' ? 'selected' : '' }}>Chemistry 101</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Date Range</label>
                            <div class="relative">
                                <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none z-10 text-sm"></i>
                                <input id="students-date-range" name="date_range" type="text" value="{{ (($filters['date_from'] ?? '') && ($filters['date_to'] ?? '')) ? ($filters['date_from'] . ' to ' . $filters['date_to']) : '' }}" class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none" placeholder="Select date range" readonly>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Status</label>
                            <select name="status" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Statuses</option>
                                <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <a href="{{ route('students') }}" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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
    <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-3xl overflow-hidden shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant/20 bg-surface-container-low/40 dark:bg-slate-900/40">
                        <th class="py-4 px-6 text-sm font-semibold text-on-surface-variant dark:text-slate-400">
                            @php
                                $nextDir = ($filters['sort'] == 'name' && $filters['direction'] == 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('students', array_merge(request()->query(), ['sort' => 'name', 'direction' => $nextDir])) }}" class="flex items-center gap-1 hover:text-primary transition-colors">
                                Student Name
                                @if($filters['sort'] == 'name')
                                    <i class="fa-solid {{ $filters['direction'] == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }} text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs opacity-40"></i>
                                @endif
                            </a>
                        </th>
                        <th class="py-4 px-6 text-sm font-semibold text-on-surface-variant dark:text-slate-400">Enrolled Courses</th>
                        <th class="py-4 px-6 text-sm font-semibold text-on-surface-variant dark:text-slate-400">
                            @php
                                $nextDirDate = ($filters['sort'] == 'date' && $filters['direction'] == 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('students', array_merge(request()->query(), ['sort' => 'date', 'direction' => $nextDirDate])) }}" class="flex items-center gap-1 hover:text-primary transition-colors">
                                Join Date
                                @if($filters['sort'] == 'date')
                                    <i class="fa-solid {{ $filters['direction'] == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }} text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs opacity-40"></i>
                                @endif
                            </a>
                        </th>
                        <th class="py-4 px-6 text-sm font-semibold text-on-surface-variant dark:text-slate-400">Status</th>
                        <th class="py-4 px-6 text-sm font-semibold text-on-surface-variant dark:text-slate-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 dark:divide-slate-700">
                    @forelse($students as $idx => $s)
                        <tr class="hover:bg-primary/5 transition-colors">
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold shadow-sm">
                                        {{ strtoupper(substr($s['name'], 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-on-background dark:text-white">{{ $s['name'] }}</p>
                                        <p class="text-xs text-on-surface-variant dark:text-slate-400">{{ $s['email'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 text-sm text-on-surface dark:text-slate-300">
                                {{ $s['courses'] }}
                            </td>
                            <td class="py-3.5 px-6 text-sm text-on-surface dark:text-slate-300">
                                {{ \Carbon\Carbon::parse($s['date'])->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 px-6">
                                @if($s['status'] === 'active')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-tertiary/10 text-tertiary dark:bg-tertiary/20 dark:text-tertiary-fixed-dim">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-700/50 dark:text-slate-400">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button class="p-2 text-on-surface-variant dark:text-slate-400 hover:text-primary transition-colors rounded-full hover:bg-primary/10">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </button>
                                    <button class="p-2 text-on-surface-variant dark:text-slate-400 hover:text-primary transition-colors rounded-full hover:bg-primary/10">
                                        <i class="fa-solid fa-pen text-sm"></i>
                                    </button>
                                    <button class="p-2 text-on-surface-variant dark:text-slate-400 hover:text-error transition-colors rounded-full hover:bg-error/10">
                                        <i class="fa-solid fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-on-surface-variant">
                                No students found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Simple Pagination footer --}}
        @if($totalPages > 1)
            <div class="px-6 py-4 border-t border-outline-variant/10 dark:border-slate-700 flex items-center justify-between bg-surface-container-low/20 dark:bg-slate-900/10">
                <span class="text-xs text-on-surface-variant dark:text-slate-400">
                    Showing page {{ $currentPage }} of {{ $totalPages }}
                </span>
                <div class="flex gap-1">
                    <a href="{{ $currentPage > 1 ? route('students', array_merge(request()->query(), ['page' => $currentPage - 1])) : '#' }}"
                       class="p-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-primary/10 transition-colors {{ $currentPage <= 1 ? 'pointer-events-none opacity-50' : '' }}">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                    <a href="{{ $currentPage < $totalPages ? route('students', array_merge(request()->query(), ['page' => $currentPage + 1])) : '#' }}"
                       class="p-1.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-primary/10 transition-colors {{ $currentPage >= $totalPages ? 'pointer-events-none opacity-50' : '' }}">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        @endif
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

            flatpickr("#students-date-range", {
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
