@extends('layouts.app')

@section('title', 'Flashcard Management')
@section('meta-description', 'Organize and manage flashcard study sets across all curricula.')

@section('page-title', 'Flashcard Management')
@section('page-subtitle', 'Organize and manage study sets across all curricula.')

@section('content')
    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Flashcard Management</span>
    </div>

    {{-- Page Header & Actions --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface dark:text-white">Flashcard Management</h1>
            <p class="font-body-md text-body-md text-on-surface-variant dark:text-slate-400 mt-1">Organize and manage study sets across all curricula.</p>
        </div>
        <a href="{{ route('flashcards.create') }}" class="bg-gradient-to-r from-primary to-primary-container text-on-primary rounded-full py-2.5 px-6 text-sm font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-plus text-sm"></i>
            Create New Set
        </a>
    </div>

    {{-- Filters Card --}}
    <div class="bg-surface-container-lowest/70 dark:bg-slate-800 rounded-xl p-5 mb-6 shadow-sm border border-outline-variant/30 dark:border-slate-700 flex flex-wrap gap-4 items-end relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-2">Curriculum Chapter</label>
            <select class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none transition-all">
                <option value="">All Chapters</option>
                <option>Physics 101</option>
                <option>Organic Chemistry</option>
                <option>World History II</option>
                <option>Intro to Psychology</option>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-2">Topic</label>
            <select class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none transition-all">
                <option value="">All Topics</option>
                <option>Kinematics</option>
                <option>Nomenclature</option>
                <option>Cold War</option>
                <option>Memory</option>
            </select>
        </div>
        <div class="flex-2 min-w-[250px]">
            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-2">Search Sets</label>
            <input class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none transition-all" placeholder="Title, tag, or keyword..." type="text">
        </div>
        <div class="flex gap-2">
            <button class="bg-surface hover:bg-surface-variant dark:bg-slate-700 border border-outline-variant/30 text-on-surface-variant rounded-lg p-2 transition-colors shadow-sm" title="Clear Filters">
                <i class="fa-solid fa-filter-circle-xmark"></i>
            </button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant/20 bg-surface-container-low/40 dark:bg-slate-900/40 text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-6 font-semibold">Flashcard Set Title</th>
                        <th class="py-4 px-6 font-semibold">Chapter</th>
                        <th class="py-4 px-6 font-semibold">Topic</th>
                        <th class="py-4 px-6 font-semibold">Total Cards</th>
                        <th class="py-4 px-6 font-semibold">Last Updated</th>
                        <th class="py-4 px-6 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-on-surface dark:text-slate-300 divide-y divide-outline-variant/10 dark:divide-slate-700">
                    @foreach($flashcards as $i => $card)
                    <tr class="hover:bg-primary/5 transition-colors group">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                @php
                                    $icons = ['fa-flask text-primary bg-primary/10', 'fa-dna text-tertiary bg-tertiary/10', 'fa-earth-americas text-secondary bg-secondary-container/30', 'fa-brain text-error bg-error-container/40'];
                                    $iconClass = $icons[$i % count($icons)];
                                @endphp
                                <div class="w-8 h-8 rounded-md flex items-center justify-center {!! $iconClass !!}">
                                    <i class="fa-solid {!! explode(' ', $iconClass)[0] !!} text-sm"></i>
                                </div>
                                <span class="font-semibold text-on-surface dark:text-white">{{ $card['title'] }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-on-surface-variant dark:text-slate-400">{{ $card['chapter'] }}</td>
                        <td class="py-4 px-6 text-on-surface-variant dark:text-slate-400">{{ $card['topic'] }}</td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-primary/10 text-primary">
                                {{ $card['cards_count'] }} Cards
                            </span>
                        </td>
                        <td class="py-4 px-6 text-on-surface-variant dark:text-slate-400">{{ $card['updated_at'] }}</td>
                        <td class="py-4 px-6 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                                <button class="w-8 h-8 rounded-lg text-on-surface-variant hover:text-primary hover:bg-primary/10 transition-colors" title="Preview">
                                    <i class="fa-solid fa-eye text-sm"></i>
                                </button>
                                <a href="{{ route('flashcards.create') }}" class="flex items-center justify-center w-8 h-8 rounded-lg text-on-surface-variant hover:text-secondary hover:bg-secondary/10 transition-colors" title="Edit">
                                    <i class="fa-solid fa-pen text-sm"></i>
                                </a>
                                <button class="w-8 h-8 rounded-lg text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors" title="Delete">
                                    <i class="fa-solid fa-trash text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-outline-variant/20 dark:border-slate-700 bg-surface-container-lowest/30 dark:bg-slate-800/60 flex items-center justify-between">
            <span class="text-xs font-medium text-on-surface-variant dark:text-slate-400">Showing {{ $flashcards->count() }} of 24 sets</span>
            <div class="flex gap-1">
                <button class="p-1.5 rounded-md text-outline hover:bg-surface-variant dark:hover:bg-slate-700 disabled:opacity-50" disabled>
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <button class="w-8 h-8 rounded-md bg-primary text-white text-xs font-bold flex items-center justify-center">1</button>
                <button class="w-8 h-8 rounded-md text-on-surface-variant hover:bg-surface-variant dark:hover:bg-slate-700 text-xs font-medium">2</button>
                <button class="w-8 h-8 rounded-md text-on-surface-variant hover:bg-surface-variant dark:hover:bg-slate-700 text-xs font-medium">3</button>
                <button class="p-1.5 rounded-md text-outline hover:bg-surface-variant dark:hover:bg-slate-700">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>
@endsection
