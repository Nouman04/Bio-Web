@extends('layouts.app')

@section('title', 'Quiz Management')
@section('meta-description', 'Manage and organize quizzes and assessments.')

@section('page-title', 'Quiz Management')
@section('page-subtitle', 'Manage and organize quizzes and assessments.')

@push('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
    }
</style>
@endpush

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold text-on-surface dark:text-white">Quiz Management</h2>
            <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-1">Manage and organize quizzes and assessments.</p>
        </div>
        <a href="{{ route('quizzes.create') }}" class="bg-gradient-to-r from-primary to-primary-container text-white px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg hover:scale-[1.02] transition-all flex items-center gap-2">
            <i class="fa-solid fa-plus text-[16px]"></i>
            Create Quiz
        </a>
    </div>

    {{-- Filters & Controls Glass Panel --}}
    <div class="glass-panel dark:bg-slate-800/80 rounded-2xl p-6 shadow-sm flex flex-col lg:flex-row gap-4 items-center justify-between mb-6 border-outline-variant/30 dark:border-slate-700">
        <form action="{{ route('quizzes') }}" method="GET" class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
            {{-- Search --}}
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[14px]"></i>
                <input name="search" value="{{ $filters['search'] ?? '' }}" class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/50 dark:border-slate-700 rounded-lg text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary shadow-inner inset" placeholder="Search by title..." type="text"/>
            </div>
            {{-- Dropdowns --}}
            <div class="relative w-full sm:w-48">
                <select name="course" onchange="this.form.submit()" class="w-full py-2 pl-4 pr-10 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/50 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary shadow-inner inset text-on-surface dark:text-slate-200 appearance-none">
                    <option value="">All Courses</option>
                    <option value="CS101" {{ ($filters['course'] ?? '') == 'CS101' ? 'selected' : '' }}>Computer Science 101</option>
                    <option value="BIO201" {{ ($filters['course'] ?? '') == 'BIO201' ? 'selected' : '' }}>Advanced Biology</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-xs"></i>
            </div>
            <div class="relative w-full sm:w-48">
                <select name="chapter" onchange="this.form.submit()" class="w-full py-2 pl-4 pr-10 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/50 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary shadow-inner inset text-on-surface dark:text-slate-200 appearance-none">
                    <option value="">All Chapters</option>
                    <option value="1" {{ ($filters['chapter'] ?? '') == '1' ? 'selected' : '' }}>Chapter 1: Biology Basics</option>
                    <option value="2" {{ ($filters['chapter'] ?? '') == '2' ? 'selected' : '' }}>Chapter 2: Cell Structure</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-xs"></i>
            </div>
            <div class="relative w-full sm:w-48">
                <select name="status" onchange="this.form.submit()" class="w-full py-2 pl-4 pr-10 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/50 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary shadow-inner inset text-on-surface dark:text-slate-200 appearance-none">
                    <option value="">All Status</option>
                    <option value="Published" {{ ($filters['status'] ?? '') == 'Published' ? 'selected' : '' }}>Published</option>
                    <option value="Draft" {{ ($filters['status'] ?? '') == 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Closed" {{ ($filters['status'] ?? '') == 'Closed' ? 'selected' : '' }}>Closed</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-xs"></i>
            </div>
            <div class="relative w-full sm:w-64">
                <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[14px] pointer-events-none"></i>
                <input id="quizzes-date-range" name="date_range" type="text" value="{{ (($filters['date_from'] ?? '') && ($filters['date_to'] ?? '')) ? ($filters['date_from'] . ' to ' . $filters['date_to']) : '' }}" class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/50 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary shadow-inner inset text-on-surface dark:text-slate-200" placeholder="Select date range" readonly/>
            </div>
            <a href="{{ route('quizzes') }}" class="p-2.5 text-on-surface-variant border border-outline-variant rounded-lg hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors flex items-center justify-center" title="Reset Filters">
                <i class="fa-solid fa-arrow-rotate-left"></i>
            </a>
        </form>
    </div>

    {{-- Data Table Glass Panel --}}
    <div class="glass-panel dark:bg-slate-800/80 bg-white/50 dark:bg-slate-900/50 rounded-2xl overflow-hidden shadow-sm border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="border-b border-outline-variant/20 dark:border-slate-700 text-xs text-on-surface-variant dark:text-slate-400 bg-surface-container-lowest/50 dark:bg-slate-800/50">
                        <th class="p-4 font-semibold uppercase">Quiz Title</th>
                        <th class="p-4 font-semibold hidden sm:table-cell uppercase">Chapter</th>
                        <th class="p-4 font-semibold hidden lg:table-cell uppercase">Date Created</th>
                        <th class="p-4 font-semibold uppercase text-center">Status</th>
                        <th class="p-4 font-semibold hidden md:table-cell uppercase text-center">Responses</th>
                        <th class="p-4 font-semibold text-right uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($quizzes as $quiz)
                        <tr class="border-b border-outline-variant/10 dark:border-slate-700 bg-white/40 dark:bg-slate-800/40 hover-lift">
                            <td class="p-4">
                                <p class="font-medium text-on-surface dark:text-slate-200">{{ $quiz['title'] }}</p>
                                <p class="text-on-surface-variant dark:text-slate-400 text-xs mt-0.5">{{ $quiz['meta'] }}</p>
                            </td>
                            <td class="p-4 hidden sm:table-cell text-on-surface-variant dark:text-slate-400">{{ $quiz['chapter'] }}</td>
                            <td class="p-4 hidden lg:table-cell text-on-surface-variant dark:text-slate-400">{{ $quiz['date_created'] }}</td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full {{ $quiz['status_color'] }} text-xs font-semibold">
                                    {{ $quiz['status'] }}
                                </span>
                            </td>
                            <td class="p-4 hidden md:table-cell text-center">
                                <span class="font-medium text-on-surface dark:text-slate-300">{{ $quiz['responses'] }}</span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('quizzes.edit', $quiz['id']) }}" class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-full transition-colors dark:hover:bg-slate-700" title="Edit">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                    <button class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-full transition-colors dark:hover:bg-slate-700" title="View Results">
                                        <i class="fa-solid fa-chart-simple text-xs"></i>
                                    </button>
                                    <button class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-error hover:bg-error/10 rounded-full transition-colors dark:hover:bg-slate-700" title="Delete">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-on-surface-variant dark:text-slate-500">
                                No quizzes found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination (Simple) --}}
        <div class="px-6 py-4 border-t border-outline-variant/10 dark:border-slate-700 flex items-center justify-between bg-surface-container-lowest/30 dark:bg-slate-800/30">
            <span class="text-xs font-medium text-on-surface-variant dark:text-slate-400">Showing 1 to 3 of 24 results</span>
            <div class="flex gap-1">
                <button class="w-8 h-8 flex items-center justify-center rounded text-outline dark:text-slate-500 hover:bg-surface-variant dark:hover:bg-slate-700 transition-colors disabled:opacity-50" disabled>
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <button class="w-8 h-8 flex items-center justify-center rounded text-on-surface dark:text-slate-300 hover:bg-surface-variant dark:hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        flatpickr("#quizzes-date-range", {
            mode: "range",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    const form = instance.input.closest('form');
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
    </script>
    @endpush
@endsection
