@extends('layouts.app')

@section('title', 'Question Bank')
@section('meta-description', 'Manage and organize assessment questions across all courses in EduAdmin LMS.')

@section('page-title', 'Question Bank')
@section('page-subtitle', 'Manage and organize assessment items across all courses.')

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
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Question Bank</span>
    </div>

    @php
        $filtersOpen = request()->hasAny(['category', 'course', 'chapter', 'topic', 'type', 'difficulty']);
    @endphp

    {{-- Toolbar: Filter toggle + Add button --}}
    <div class="flex justify-end items-center gap-3 mb-4">
        <button type="button" id="filterToggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-800 text-on-surface-variant dark:text-slate-300 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-colors shadow-sm {{ $filtersOpen ? 'is-active' : '' }}"
            title="Toggle Filters">
            <i class="fa-solid fa-filter text-sm"></i>
        </button>
        <a href="{{ route('questions.create') }}" class="flex items-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            Add Questions
        </a>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div id="filterCard" class="filter-card-panel glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <form action="{{ route('questions') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        {{-- Category --}}
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Category</label>
                            <select name="category" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Categories</option>
                                <option>Computer Science</option>
                                <option>Mathematics</option>
                            </select>
                        </div>
                        {{-- Course --}}
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Course</label>
                            <select name="course" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Courses</option>
                                <option>Intro to Programming</option>
                                <option>Data Structures</option>
                            </select>
                        </div>
                        {{-- Chapter --}}
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</label>
                            <select name="chapter" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">Select Chapter</option>
                            </select>
                        </div>
                        {{-- Topic --}}
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic</label>
                            <select name="topic" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">Select Topic</option>
                            </select>
                        </div>
                        {{-- Type --}}
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Question Type</label>
                            <select name="type" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Types</option>
                                <option value="MCQ" {{ ($filters['type'] ?? '') == 'MCQ' ? 'selected' : '' }}>Multiple Choice</option>
                                <option value="Theory" {{ ($filters['type'] ?? '') == 'Theory' ? 'selected' : '' }}>Theory / Essay</option>
                                <option value="TF" {{ ($filters['type'] ?? '') == 'TF' ? 'selected' : '' }}>True / False</option>
                            </select>
                        </div>
                        {{-- Difficulty --}}
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Difficulty</label>
                            <select name="difficulty" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Levels</option>
                                <option value="Easy" {{ ($filters['difficulty'] ?? '') == 'Easy' ? 'selected' : '' }}>Easy</option>
                                <option value="Medium" {{ ($filters['difficulty'] ?? '') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option value="Hard" {{ ($filters['difficulty'] ?? '') == 'Hard' ? 'selected' : '' }}>Hard</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <a href="{{ route('questions') }}" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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
                    <tr class="border-b border-outline-variant/20 bg-surface-container-low/40 dark:bg-slate-900/40 text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-6 font-semibold w-2/5">Question</th>
                        <th class="py-4 px-6 font-semibold">Type</th>
                        <th class="py-4 px-6 font-semibold hidden md:table-cell">Chapter / Topic</th>
                        <th class="py-4 px-6 font-semibold">Difficulty</th>
                        <th class="py-4 px-6 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-on-surface dark:text-slate-300 divide-y divide-outline-variant/10 dark:divide-slate-700">
                    @forelse($questions as $q)
                        <tr class="hover:bg-primary/5 transition-colors group">
                            <td class="py-4 px-6">
                                <div class="line-clamp-2 pr-4 font-medium text-on-surface dark:text-white">{{ $q['text'] }}</div>
                                <div class="text-xs text-outline mt-1 md:hidden">{{ $q['chapter'] }} &rsaquo; {{ $q['topic'] }}</div>
                            </td>
                            <td class="py-4 px-6">
                                @if($q['type'] === 'MCQ')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-100 text-blue-800 text-xs font-semibold dark:bg-blue-900/30 dark:text-blue-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> MCQ
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-purple-100 text-purple-800 text-xs font-semibold dark:bg-purple-900/30 dark:text-purple-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span> Theory
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 hidden md:table-cell text-on-surface-variant dark:text-slate-400">
                                <div class="font-medium text-on-surface dark:text-slate-300">{{ $q['chapter'] }}</div>
                                <div class="text-xs">{{ $q['topic'] }}</div>
                            </td>
                            <td class="py-4 px-6">
                                @php
                                    $diffClass = match($q['difficulty']) {
                                        'Easy'   => 'bg-tertiary-container/20 text-on-tertiary-container border-tertiary/20',
                                        'Hard'   => 'bg-error-container/50 text-on-error-container border-error/20',
                                        default  => 'bg-secondary-fixed/50 text-on-secondary-fixed-variant border-secondary-fixed-dim/30',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold border {{ $diffClass }}">
                                    {{ $q['difficulty'] }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <div class="relative inline-block text-left action-dropdown">
                                    <button type="button" class="action-dropdown-trigger w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:text-primary hover:bg-primary/10 transition-colors" title="Actions">
                                        <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
                                    </button>
                                    <div class="action-dropdown-menu hidden absolute right-0 z-20 mt-1 w-44 rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 shadow-lg py-1">
                                        <button type="button" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
                                            <i class="fa-solid fa-eye w-4 text-on-surface-variant"></i>
                                            View
                                        </button>
                                        <button type="button" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
                                            <i class="fa-solid fa-pen w-4 text-on-surface-variant"></i>
                                            Edit
                                        </button>
                                        <button type="button" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
                                            <i class="fa-solid fa-copy w-4 text-on-surface-variant"></i>
                                            Duplicate
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
                            <td colspan="5" class="py-16 text-center text-on-surface-variant dark:text-slate-500">
                                <i class="fa-regular fa-circle-question text-4xl mb-3 block opacity-30"></i>
                                No questions found. Try adjusting your filters or
                                <a href="{{ route('questions.create') }}" class="text-primary font-semibold hover:underline">add new questions</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="px-6 py-4 border-t border-outline-variant/20 dark:border-slate-700 flex items-center justify-between bg-surface-container-lowest/30 dark:bg-slate-800/60">
            <span class="text-xs font-medium text-on-surface-variant dark:text-slate-400">
                Showing {{ $questions->count() }} questions
            </span>
            <div class="flex gap-1">
                <button class="p-1.5 rounded-md text-outline hover:bg-surface-variant disabled:opacity-50 dark:hover:bg-slate-700" disabled>
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <button class="w-8 h-8 rounded-md bg-primary text-white text-xs font-bold flex items-center justify-center">1</button>
                <button class="p-1.5 rounded-md text-outline hover:bg-surface-variant dark:hover:bg-slate-700">
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

            document.querySelectorAll('.action-dropdown').forEach(dropdown => {
                const trigger = dropdown.querySelector('.action-dropdown-trigger');
                const menu = dropdown.querySelector('.action-dropdown-menu');

                trigger?.addEventListener('click', (e) => {
                    e.stopPropagation();
                    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    menu?.classList.toggle('hidden');
                });
            });

            document.addEventListener('click', () => {
                document.querySelectorAll('.action-dropdown-menu').forEach(menu => {
                    menu.classList.add('hidden');
                });
            });
        });
    </script>
    @endpush

@endsection
