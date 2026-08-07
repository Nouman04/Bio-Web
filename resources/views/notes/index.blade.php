@extends('layouts.app')

@section('title', 'Study Notes')
@section('meta-description', 'Manage study notes and resources.')

@section('page-title', 'Study Notes')
@section('page-subtitle', 'Manage and organize lecture notes, resources, and study materials.')

@push('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .modal { display: none; }
    .modal.active { display: flex; }
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
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Study Notes</span>
    </div>

    @php
        $filtersOpen = request()->hasAny(['chapter', 'topic', 'search']);
    @endphp

    {{-- Toolbar: Filter toggle + Add button --}}
    <div class="flex justify-end items-center gap-3 mb-4">
        <button type="button" id="filterToggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-800 text-on-surface-variant dark:text-slate-300 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-colors shadow-sm {{ $filtersOpen ? 'is-active' : '' }}"
            title="Toggle Filters">
            <i class="fa-solid fa-filter text-sm"></i>
        </button>
        <button type="button" onclick="document.getElementById('addNoteModal').classList.add('active')" class="flex items-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            Add Note
        </button>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div class="filter-card-panel glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
                <form action="{{ route('notes') }}" method="GET" class="relative">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</label>
                            <select name="chapter" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Chapters</option>
                                <option value="Ch1" {{ ($filters['chapter'] ?? '') == 'Ch1' ? 'selected' : '' }}>Chapter 1: Fundamentals</option>
                                <option value="Ch2" {{ ($filters['chapter'] ?? '') == 'Ch2' ? 'selected' : '' }}>Chapter 2: Advanced</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic</label>
                            <select name="topic" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Topics</option>
                                <option value="Basics" {{ ($filters['topic'] ?? '') == 'Basics' ? 'selected' : '' }}>Basics</option>
                                <option value="Advanced" {{ ($filters['topic'] ?? '') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Search Notes</label>
                            <div class="relative group">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-sm"></i>
                                <input name="search" value="{{ $filters['search'] ?? '' }}" class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none" placeholder="Title, tags..." type="text">
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <a href="{{ route('notes') }}" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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

    {{-- List Card --}}
    <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-3xl overflow-hidden shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($notes as $note)
                    <div class="bg-surface-container-low dark:bg-slate-900/50 rounded-2xl p-5 hover:shadow-lg transition-shadow group flex flex-col h-full border border-outline-variant/30 dark:border-slate-700 relative">
                        <div class="flex justify-between items-start mb-3">
                            <span class="inline-flex items-center px-2 py-1 rounded {{ $note['course_tag_color'] }} text-[10px] font-bold uppercase tracking-wider">{{ $note['course'] }}</span>
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                                <button class="w-7 h-7 flex items-center justify-center rounded-md text-on-surface-variant hover:text-primary hover:bg-primary/10"><i class="fa-solid fa-pen text-xs"></i></button>
                                <button class="w-7 h-7 flex items-center justify-center rounded-md text-on-surface-variant hover:text-error hover:bg-error/10"><i class="fa-solid fa-trash text-xs"></i></button>
                            </div>
                        </div>
                        <h3 class="font-semibold text-on-surface dark:text-white mb-2 line-clamp-1">{{ $note['title'] }}</h3>
                        <p class="text-sm text-on-surface-variant dark:text-slate-400 mb-4 line-clamp-3 flex-1">
                            {{ $note['excerpt'] }}
                        </p>
                        <div class="flex items-center justify-between text-xs text-outline pt-4 border-t border-outline-variant/20 mt-auto">
                            <div class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-day"></i> {{ $note['date'] }}</div>
                            <div class="flex items-center gap-1.5"><i class="fa-solid fa-paperclip"></i> {{ $note['attachments'] }} Files</div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 flex flex-col items-center justify-center text-on-surface-variant dark:text-slate-500 bg-surface-container-low/50 dark:bg-slate-900/50 rounded-2xl border-2 border-dashed border-outline-variant/30 dark:border-slate-700">
                        <i class="fa-regular fa-note-sticky text-4xl mb-3 block opacity-30"></i>
                        <p>No notes found.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="px-6 py-4 border-t border-outline-variant/20 dark:border-slate-700 flex items-center justify-between bg-surface-container-lowest/30 dark:bg-slate-800/60">
            <span class="text-xs font-medium text-on-surface-variant dark:text-slate-400">
                Showing {{ $notes->count() }} {{ Str::plural('note', $notes->count()) }}
            </span>
        </div>
    </div>

    {{-- Add Note Modal --}}
    <div id="addNoteModal" class="modal fixed inset-0 z-50 items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        <div class="bg-surface dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden animate-[fadeIn_0.2s_ease-out]">
            <div class="px-6 py-4 border-b border-outline-variant/20 flex justify-between items-center bg-surface-container-lowest dark:bg-slate-800">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Add New Note</h3>
                <button onclick="document.getElementById('addNoteModal').classList.remove('active')" class="text-on-surface-variant hover:text-error transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-error/10">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-6">
                <form action="{{ route('notes.store') }}" method="POST" id="note-form" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Note Title</label>
                            <input name="title" type="text" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-on-surface dark:text-slate-200 focus:ring-1 focus:ring-primary focus:border-primary outline-none" placeholder="e.g. Week 1 Summary" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Course / Tag</label>
                            <select name="course" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-on-surface dark:text-slate-200 focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                                <option>Physics 101</option>
                                <option>Biology</option>
                                <option>General</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Content</label>
                        <textarea name="content" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-on-surface dark:text-slate-200 focus:ring-1 focus:ring-primary focus:border-primary outline-none min-h-[120px] resize-y" placeholder="Write your note content here..." required></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Attachments</label>
                        <div class="border-2 border-dashed border-outline-variant/50 dark:border-slate-600 rounded-lg p-6 flex flex-col items-center justify-center text-outline bg-surface-container-lowest dark:bg-slate-800 hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl mb-2 text-primary/60"></i>
                            <span class="text-sm font-medium">Click to upload files (PDF, images, docx)</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="px-6 py-4 border-t border-outline-variant/20 bg-surface-container-lowest dark:bg-slate-800 flex justify-end gap-3">
                <button onclick="document.getElementById('addNoteModal').classList.remove('active')" class="px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container dark:hover:bg-slate-700 rounded-full transition-colors">Cancel</button>
                <button type="submit" form="note-form" class="px-6 py-2 text-sm font-semibold text-white bg-primary rounded-full hover:bg-primary-container shadow-md transition-all">Save Note</button>
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
        });
    </script>
    @endpush
@endsection
