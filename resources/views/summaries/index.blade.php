@extends('layouts.app')

@section('title', 'Summary Management')
@section('meta-description', 'Organize and manage comprehensive learning summaries.')

@section('page-title', 'Summary Management')
@section('page-subtitle', 'Organize and manage comprehensive learning summaries.')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    
    .table-row-hover:hover {
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
        background-color: rgba(70, 72, 212, 0.02);
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

    .modal { display: none; }
    .modal.active { display: flex; }
</style>
@endpush

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface dark:text-white mb-1">Summary Management</h1>
            <p class="font-body-md text-body-md text-on-surface-variant dark:text-slate-400">Organize and manage comprehensive learning summaries.</p>
        </div>
        <button onclick="document.getElementById('addSummaryModal').classList.add('active')" class="py-2.5 px-6 bg-gradient-to-r from-primary to-primary-container text-white font-semibold rounded-full shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-plus text-sm"></i>
            Add New Summary
        </button>
    </div>

    {{-- Content Area - Glass Card --}}
    <div class="glass-card dark:bg-slate-800/80 rounded-xl p-6 shadow-sm flex flex-col min-h-[60vh] border-outline-variant/30 dark:border-slate-700">
        {{-- Filters --}}
        <div class="flex flex-wrap gap-4 mb-6 pb-6 border-b border-outline-variant/20 dark:border-slate-700">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</label>
                <select class="bg-white dark:bg-slate-900 border border-outline-variant/50 text-on-surface dark:text-slate-200 text-sm rounded-lg pl-4 pr-10 py-2 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary cursor-pointer min-w-[200px] shadow-sm">
                    <option>All Chapters</option>
                    <option>Physics 101</option>
                    <option>Calculus Fundamentals</option>
                    <option>Organic Chemistry</option>
                </select>
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic</label>
                <select class="bg-white dark:bg-slate-900 border border-outline-variant/50 text-on-surface dark:text-slate-200 text-sm rounded-lg pl-4 pr-10 py-2 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary cursor-pointer min-w-[200px] shadow-sm">
                    <option>All Topics</option>
                    <option>Mechanics</option>
                    <option>Integration</option>
                    <option>Molecular Bonds</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="flex-1 overflow-auto rounded-lg border border-outline-variant/20 dark:border-slate-700 bg-white/50 dark:bg-slate-900/50">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead class="sticky top-0 bg-surface-container-low/90 dark:bg-slate-800/90 backdrop-blur-sm border-b border-outline-variant/30 dark:border-slate-700 z-10">
                    <tr>
                        <th class="py-3 px-4 text-xs uppercase tracking-wider text-on-surface-variant dark:text-slate-400 font-semibold">Title</th>
                        <th class="py-3 px-4 text-xs uppercase tracking-wider text-on-surface-variant dark:text-slate-400 font-semibold">Chapter</th>
                        <th class="py-3 px-4 text-xs uppercase tracking-wider text-on-surface-variant dark:text-slate-400 font-semibold">Topic</th>
                        <th class="py-3 px-4 text-xs uppercase tracking-wider text-on-surface-variant dark:text-slate-400 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($summaries as $summary)
                        <tr class="border-b border-outline-variant/10 dark:border-slate-700 table-row-hover bg-white/40 dark:bg-slate-800/40">
                            <td class="py-4 px-4 font-medium text-on-surface dark:text-slate-200">{{ $summary['title'] }}</td>
                            <td class="py-4 px-4 text-on-surface-variant dark:text-slate-400">{{ $summary['chapter'] }}</td>
                            <td class="py-4 px-4 text-on-surface-variant dark:text-slate-400">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $summary['topic_color'] }}">{{ $summary['topic'] }}</span>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-md transition-colors" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error/10 rounded-md transition-colors" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-on-surface-variant dark:text-slate-500">
                                No summaries found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Summary Modal --}}
    <div id="addSummaryModal" class="modal fixed inset-0 z-50 items-center justify-center p-4 sm:p-6 bg-black/40 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-surface-container-lowest dark:bg-slate-900 w-full max-w-3xl rounded-xl flex flex-col max-h-[90vh] overflow-hidden shadow-2xl animate-[fadeIn_0.2s_ease-out]">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-outline-variant/30 dark:border-slate-700 flex items-center justify-between bg-surface-container-lowest dark:bg-slate-800">
                <h2 class="text-lg font-bold text-on-surface dark:text-white">Add New Summary</h2>
                <button onclick="document.getElementById('addSummaryModal').classList.remove('active')" class="text-on-surface-variant hover:text-error hover:bg-error/10 w-8 h-8 rounded-full transition-colors flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto flex-1 flex flex-col gap-6">
                <form action="{{ route('summaries.store') }}" method="POST" id="summary-form">
                    @csrf
                    
                    {{-- Metadata Row --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</label>
                            <select name="chapter" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                <option disabled selected value="">Select Chapter</option>
                                <option value="ch1">Chapter 1: Introduction to Biology</option>
                                <option value="ch2">Chapter 2: Cell Structure</option>
                                <option value="ch3">Chapter 3: Genetics</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic</label>
                            <select name="topic" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                <option disabled selected value="">Select Topic</option>
                                <option value="t1">1.1 What is Life?</option>
                                <option value="t2">1.2 Scientific Method</option>
                                <option value="t3">2.1 Organelles</option>
                            </select>
                        </div>
                    </div>
                    
                    {{-- Title Input --}}
                    <div class="flex flex-col gap-2 mb-6">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Summary Title</label>
                        <input name="title" required class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="Enter summary title..." type="text">
                    </div>
                    
                    {{-- Content Editor --}}
                    <div class="flex flex-col gap-2 flex-1 min-h-[300px]">
                        <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Content</label>
                        <div class="flex flex-col flex-1 border border-outline-variant/50 dark:border-slate-700 rounded-lg overflow-hidden bg-white dark:bg-slate-800 focus-within:ring-1 focus-within:ring-primary focus-within:border-primary transition-all">
                            {{-- Rich Text Toolbar --}}
                            <div class="flex items-center gap-1 p-2 border-b border-outline-variant/30 dark:border-slate-700 bg-surface-container-low dark:bg-slate-900/50 flex-wrap">
                                <button type="button" class="w-8 h-8 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded transition-colors flex items-center justify-center"><i class="fa-solid fa-bold"></i></button>
                                <button type="button" class="w-8 h-8 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded transition-colors flex items-center justify-center"><i class="fa-solid fa-italic"></i></button>
                                <button type="button" class="w-8 h-8 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded transition-colors flex items-center justify-center"><i class="fa-solid fa-underline"></i></button>
                                <div class="w-px h-5 bg-outline-variant/50 mx-1"></div>
                                <button type="button" class="w-8 h-8 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded transition-colors flex items-center justify-center"><i class="fa-solid fa-list-ul"></i></button>
                                <div class="w-px h-5 bg-outline-variant/50 mx-1"></div>
                                <button type="button" class="w-8 h-8 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded transition-colors flex items-center justify-center"><i class="fa-solid fa-link"></i></button>
                                <button type="button" class="w-8 h-8 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded transition-colors flex items-center justify-center"><i class="fa-solid fa-image"></i></button>
                            </div>
                            {{-- Editor Text Area --}}
                            <textarea name="content" required class="w-full flex-1 p-4 bg-transparent border-none text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:ring-0 resize-none min-h-[250px]" placeholder="Start writing your summary content here..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            
            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-outline-variant/30 dark:border-slate-700 bg-surface-container-low dark:bg-slate-800 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('addSummaryModal').classList.remove('active')" class="px-6 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-variant dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </button>
                <button type="submit" form="summary-form" class="px-6 py-2 rounded-full text-sm font-semibold text-white bg-primary hover:bg-primary-container shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Summary
                </button>
            </div>
        </div>
    </div>
@endsection
