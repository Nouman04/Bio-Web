@extends('layouts.app')

@section('title', 'Topics')
@section('meta-description', 'Manage and view course topics in EduAdmin LMS.')

@section('page-title', 'Topics Management')
@section('page-subtitle', 'Manage and organize instructional content structure.')

@section('content')

    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-on-surface-variant dark:text-slate-500">Content</span>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Topics</span>
    </div>

    {{-- Filters & Action Row --}}
    <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-4 flex flex-col md:flex-row gap-4 justify-between items-center mb-6 border border-outline-variant/30 dark:border-slate-700">
        <form action="{{ route('topics') }}" method="GET" class="w-full flex flex-col md:flex-row gap-4 justify-between items-center">

            <div class="w-full md:w-auto flex-1 max-w-md relative group">
                <span class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors"></span>
                <input name="title" value="{{ $filters['title'] ?? '' }}"
                    class="w-full pl-10 pr-4 py-2.5 bg-surface-container-low dark:bg-slate-900 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm outline-none transition-all"
                    placeholder="Search by title..." type="text">
            </div>

            <select name="chapter" onchange="this.form.submit()"
                class="bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl px-4 py-2.5 text-sm text-on-surface focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                <option value="">All Chapters</option>
                <option value="Ch 1. Fundamentals" {{ ($filters['chapter'] ?? '') == 'Ch 1. Fundamentals' ? 'selected' : '' }}>Chapter 1: Fundamentals</option>
                <option value="Ch 2. Advanced" {{ ($filters['chapter'] ?? '') == 'Ch 2. Advanced' ? 'selected' : '' }}>Chapter 2: Advanced Mechanics</option>
            </select>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <a href="{{ route('topics') }}"
                    class="p-2.5 text-on-surface-variant border border-outline-variant rounded-xl hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors flex items-center justify-center"
                    title="Reset Filters">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                </a>
                <button type="button" onclick="openCreateTopicModal()"
                    class="flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all whitespace-nowrap">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Add New Topic
                </button>
            </div>
        </form>
    </div>

    {{-- Main Data Table Card --}}
    <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-3xl overflow-hidden shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant/20 bg-surface-container-low/40 dark:bg-slate-900/40 text-sm font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">
                        <th class="p-4 font-semibold w-1/4">Chapter</th>
                        <th class="p-4 font-semibold w-2/5">Topic Name</th>
                        <th class="p-4 font-semibold text-center w-1/5">Total Questions</th>
                        <th class="p-4 font-semibold text-right w-[15%]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 dark:divide-slate-700 text-sm text-on-surface">
                    @forelse($topics as $t)
                        <tr class="hover:bg-primary/5 transition-colors">
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-secondary-container/20 text-on-secondary-container text-xs font-semibold">
                                    {{ $t['chapter'] }}
                                </span>
                            </td>
                            <td class="p-4 font-semibold text-on-surface dark:text-white">{{ $t['name'] }}</td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-surface-container-low dark:bg-slate-900 text-on-surface dark:text-slate-300 text-xs font-bold shadow-sm">
                                    {{ $t['questions'] }}
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('topics.assign', $t['id']) }}" class="p-2 text-on-surface-variant dark:text-slate-400 hover:text-primary transition-colors rounded-full hover:bg-primary/10" title="Assign Questions">
                                        <i class="fa-solid fa-list-check text-sm"></i>
                                    </a>
                                    <button class="p-2 text-on-surface-variant dark:text-slate-400 hover:text-primary transition-colors rounded-full hover:bg-primary/10" title="Edit">
                                        <i class="fa-solid fa-pen text-sm"></i>
                                    </button>
                                    <button class="p-2 text-on-surface-variant dark:text-slate-400 hover:text-error transition-colors rounded-full hover:bg-error/10" title="Delete">
                                        <i class="fa-solid fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-on-surface-variant">
                                No topics found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create Topic Modal --}}
    <div id="create-topic-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeCreateTopicModal()"></div>
        <!-- Panel -->
        <div class="relative w-full max-w-md mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Add New Topic</h3>
                <button type="button" onclick="closeCreateTopicModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form action="#" method="POST" class="p-6 flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</label>
                    <select class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        <option>Select a chapter</option>
                        <option>Chapter 1: Fundamentals</option>
                        <option>Chapter 2: Advanced Mechanics</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic Name</label>
                    <input class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="Enter topic name" type="text">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeCreateTopicModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors">Add Topic</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openCreateTopicModal() {
            document.getElementById('create-topic-modal-container').classList.remove('hidden');
        }

        function closeCreateTopicModal() {
            document.getElementById('create-topic-modal-container').classList.add('hidden');
        }
    </script>
@endpush
