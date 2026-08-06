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
</style>
@endpush

@section('content')
    {{-- Breadcrumbs & Header --}}
    <div class="flex flex-col gap-2 mb-6">
        <nav class="flex items-center gap-2 text-on-surface-variant text-xs">
            <a class="hover:text-primary transition-colors" href="#">Curriculum</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <span class="text-on-surface dark:text-slate-200 font-semibold">Guides</span>
        </nav>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h1 class="text-2xl md:text-3xl font-bold text-on-surface dark:text-white">Guides</h1>
            <a href="{{ route('guides.create') }}" class="bg-gradient-to-r from-primary to-primary-container text-white text-sm px-6 py-2.5 rounded-full shadow-sm hover:shadow-md hover:from-primary-container hover:to-primary transition-all duration-300 flex items-center gap-2 active:scale-95">
                <i class="fa-solid fa-plus text-xs"></i>
                Add New Guide
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="glass-panel dark:bg-slate-800/80 rounded-xl p-6 hover-ambient-shadow transition-shadow duration-300 mb-6 dark:border-slate-700 border-outline-variant/30">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="relative w-full">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm"></i>
                <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary shadow-inner transition-all outline-none" placeholder="Search Title..." type="text">
            </div>
            <div class="relative w-full">
                <select class="w-full pl-4 pr-10 py-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary shadow-inner transition-all outline-none appearance-none">
                    <option value="">All Chapters</option>
                    <option value="1">Chapter 1: Foundations</option>
                    <option value="2">Chapter 2: Advanced Topics</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-xs"></i>
            </div>
            <div class="relative w-full">
                <select class="w-full pl-4 pr-10 py-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary shadow-inner transition-all outline-none appearance-none">
                    <option value="">All Topics</option>
                    <option value="math">Mathematics</option>
                    <option value="sci">Science</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-xs"></i>
            </div>
            <div class="relative w-full">
                <select class="w-full pl-4 pr-10 py-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary shadow-inner transition-all outline-none appearance-none">
                    <option value="">All Types</option>
                    <option value="theory">Theory Guide</option>
                    <option value="atp">ATP Guide</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-xs"></i>
            </div>
            <div class="relative w-full">
                <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm"></i>
                <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary shadow-inner transition-all outline-none appearance-none text-on-surface-variant" type="date">
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
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full text-on-surface-variant hover:text-primary transition-colors hover:bg-surface-container-highest dark:hover:bg-slate-700" title="Edit">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                <button class="w-8 h-8 flex items-center justify-center rounded-full text-on-surface-variant hover:text-error transition-colors hover:bg-error/10" title="Delete">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
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
@endsection
