@extends('layouts.app')

@section('title', 'Assign Questions')
@section('meta-description', 'Assign questions to curriculum topics in EduAdmin LMS.')

@section('page-title', 'Topic Question Assignment')
@section('page-subtitle', 'Configure targeted question banks for specific curriculum topics.')

@section('content')

    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('topics') }}">Topics</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Assign Questions</span>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <!-- Left Column: Controls & List -->
        <div class="xl:col-span-8 2xl:col-span-9 flex flex-col gap-6">
            <!-- Assignment Controls Card -->
            <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-6 border border-outline-variant/30 dark:border-slate-700 shadow-sm flex flex-col gap-4">
                <h2 class="text-lg font-bold text-on-surface dark:text-white mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-primary"></i>
                    Assignment Target
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</label>
                        <select class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            <option>Chapter 1: Fundamentals</option>
                            <option>Chapter 2: Advanced Mechanics</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic</label>
                        <select class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                            <option selected>{{ $topicName }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Question Bank Selection -->
            <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl overflow-hidden flex flex-col border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <div class="p-4 border-b border-outline-variant/30 dark:border-slate-700 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-surface-container-low/40 dark:bg-slate-900/40">
                    <h2 class="text-base font-bold text-on-surface dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-database text-primary"></i>
                        Question Bank
                    </h2>
                    <div class="flex items-center gap-3 w-full md:w-auto flex-wrap">
                        <select class="bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl text-xs font-semibold py-1.5 pl-3 pr-8 focus:ring-2 focus:ring-primary/20 text-on-surface outline-none">
                            <option value="all">All Types</option>
                            <option value="mcq">MCQ</option>
                            <option value="theory">Theory</option>
                        </select>
                        <select class="bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl text-xs font-semibold py-1.5 pl-3 pr-8 focus:ring-2 focus:ring-primary/20 text-on-surface outline-none">
                            <option value="all">Any Difficulty</option>
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>
                </div>
                <!-- Table -->
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-lowest/60 dark:bg-slate-800 border-b border-outline-variant/20 dark:border-slate-700 text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-4 w-12 text-center">Assign</th>
                                <th class="py-3 px-4">Question Text</th>
                                <th class="py-3 px-4 w-24 text-center">Type</th>
                                <th class="py-3 px-4 w-24 text-center">Difficulty</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-on-surface dark:text-slate-300 divide-y divide-outline-variant/10 dark:divide-slate-700">
                            @foreach($questions as $q)
                                <tr class="hover:bg-primary/5 transition-colors">
                                    <td class="py-3 px-4 text-center">
                                        <input type="checkbox" checked class="rounded border-outline-variant text-primary focus:ring-primary/50 w-4 h-4 cursor-pointer">
                                    </td>
                                    <td class="py-3 px-4 text-on-surface-variant dark:text-slate-400 font-medium">{{ $q['text'] }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded bg-secondary-container/20 text-on-secondary-container text-xs font-semibold">
                                            {{ $q['type'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded bg-error-container/20 text-on-error-container text-xs font-semibold">
                                            {{ $q['difficulty'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Summary Panel -->
        <div class="xl:col-span-4 2xl:col-span-3">
            <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-6 flex flex-col gap-4 border border-outline-variant/30 dark:border-slate-700 shadow-sm sticky top-6">
                <div class="flex items-center gap-3 pb-3 border-b border-outline-variant/20 dark:border-slate-700">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-on-surface dark:text-white">Summary</h3>
                        <p class="text-xs text-on-surface-variant dark:text-slate-400">Review before confirming</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 py-2">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-on-surface-variant dark:text-slate-400 font-medium">Selected Questions</span>
                        <span class="text-lg font-bold text-primary">{{ $questions->count() }}</span>
                    </div>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Schedule Date</label>
                    <input type="date" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                </div>
                <div class="flex flex-col gap-2 pt-2 border-t border-outline-variant/20 dark:border-slate-700">
                    <a href="{{ route('topics') }}" class="w-full py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-on-primary text-sm font-semibold shadow-md hover:shadow-lg transition-all active:scale-95 text-center">
                        Confirm Assignment
                    </a>
                    <a href="{{ route('topics') }}" class="w-full py-2.5 rounded-full border border-outline-variant text-on-surface-variant dark:text-slate-400 text-sm font-semibold hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors text-center">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection
