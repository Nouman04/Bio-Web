@extends('layouts.app')

@section('title', 'Courses')
@section('meta-description', 'Manage courses and content catalog in EduAdmin LMS.')

@section('page-title', 'Course Management')
@section('page-subtitle', 'Manage and organize your educational content catalog.')

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
                <form action="{{ route('courses') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Search</label>
                            <div class="relative group">
                                <span class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-sm"></span>
                                <input name="search" value="{{ $filters['search'] ?? '' }}"
                                    class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none"
                                    placeholder="Search courses..." type="text">
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Category</label>
                            <select name="category" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Categories</option>
                                <option value="Development" {{ ($filters['category'] ?? '') == 'Development' ? 'selected' : '' }}>Development</option>
                                <option value="Business" {{ ($filters['category'] ?? '') == 'Business' ? 'selected' : '' }}>Business</option>
                                <option value="Design" {{ ($filters['category'] ?? '') == 'Design' ? 'selected' : '' }}>Design</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Instructor</label>
                            <select name="created_by" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Instructors</option>
                                <option value="John Doe" {{ ($filters['created_by'] ?? '') == 'John Doe' ? 'selected' : '' }}>John Doe</option>
                                <option value="Jane Smith" {{ ($filters['created_by'] ?? '') == 'Jane Smith' ? 'selected' : '' }}>Jane Smith</option>
                                <option value="Mike Johnson" {{ ($filters['created_by'] ?? '') == 'Mike Johnson' ? 'selected' : '' }}>Mike Johnson</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <a href="{{ route('courses') }}" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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

    {{-- Course List Data Table (Glassmorphic) --}}
    <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-3xl overflow-hidden shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-label-md font-label-md text-on-surface-variant bg-surface-container-low/40 dark:bg-slate-900/40 border-b border-outline-variant/20">
                        <th class="px-6 py-4 font-semibold w-1/3">Title</th>
                        <th class="px-6 py-4 font-semibold">Category</th>
                        <th class="px-6 py-4 font-semibold">Created By</th>
                        <th class="px-6 py-4 font-semibold text-center">Total Chapters</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-body-sm font-body-sm text-on-surface divide-y divide-outline-variant/10 dark:divide-slate-700">
                    @forelse($courses as $c)
                        <tr class="hover:bg-primary/5 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0 text-primary">
                                        <i class="fa-solid fa-{{ $c['icon'] == 'trending_up' ? 'arrow-trend-up' : ($c['icon'] == 'brush' ? 'paintbrush' : 'code') }}"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('courses.chapters', $c['id']) }}" class="font-medium text-on-surface dark:text-white hover:text-primary transition-colors">{{ $c['title'] }}</a>
                                        <p class="text-xs text-on-surface-variant dark:text-slate-400">{{ $c['dateMsg'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-surface-container-low dark:bg-slate-900 rounded-full text-xs font-semibold text-on-surface dark:text-slate-300">
                                    {{ $c['category'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-on-surface dark:text-slate-300">{{ $c['instructor'] }}</td>
                            <td class="px-6 py-4 text-center font-medium text-on-surface dark:text-slate-300">{{ $c['chapters'] }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="relative inline-block text-left action-dropdown">
                                    <button type="button" class="action-dropdown-trigger w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant dark:text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors" title="Actions">
                                        <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
                                    </button>
                                    <div class="action-dropdown-menu hidden absolute right-0 z-20 mt-1 w-44 rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 shadow-lg py-1">
                                        <a href="{{ route('courses.chapters', $c['id']) }}" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
                                            <i class="fa-solid fa-book-bookmark w-4 text-on-surface-variant"></i>
                                            View Chapters
                                        </a>
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
                            <td colspan="5" class="py-12 text-center text-on-surface-variant">
                                No courses found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Course Modal (exact markup from html/courses/modal.html) --}}
    <div id="add-course-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeAddCourseModal()"></div>
        <!-- Panel -->
        <div class="relative w-full max-w-md mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Add New Course</h3>
                <button type="button" onclick="closeAddCourseModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form action="#" method="POST" class="p-6 flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Category</label>
                    <select class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        <option>Select a category</option>
                        <option>Development</option>
                        <option>Business</option>
                        <option>Design</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Title</label>
                    <input class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="Enter course title" type="text" oninput="autoGenerateSlug(this.value)">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Slug</label>
                    <input id="modal-slug-input" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="e.g. advanced-react-patterns" type="text">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Description</label>
                    <textarea class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface h-24" placeholder="Provide a brief overview of the course content"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeAddCourseModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors">Create Course</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const filterToggle = document.getElementById('filterToggle');
            const filterCardWrapper = document.getElementById('filterCardWrapper');

            filterToggle?.addEventListener('click', () => {
                const isOpen = filterCardWrapper?.classList.toggle('is-open');
                filterToggle.classList.toggle('is-active', isOpen);
            });
        });

        function openAddCourseModal() {
            document.getElementById('add-course-modal-container').classList.remove('hidden');
        }

        function closeAddCourseModal() {
            document.getElementById('add-course-modal-container').classList.add('hidden');
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
