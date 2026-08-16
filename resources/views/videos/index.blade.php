@extends('layouts.app')

@section('title', 'Video Lessons')
@section('meta-description', 'Manage and organize your instructional video content.')

@section('page-title', 'Video Lessons')
@section('page-subtitle', 'Manage and organize your instructional video content.')

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
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
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
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Video Lessons</span>
    </div>

    @php
        $filtersOpen = request()->hasAny(['search', 'chapter', 'topic', 'date_from', 'date_to']);
    @endphp

    {{-- Toolbar: Filter toggle + Add button --}}
    <div class="flex justify-end items-center gap-3 mb-4">
        <button type="button" id="filterToggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-800 text-on-surface-variant dark:text-slate-300 hover:text-primary hover:border-primary/40 hover:bg-primary/5 transition-colors shadow-sm {{ $filtersOpen ? 'is-active' : '' }}"
            title="Toggle Filters">
            <i class="fa-solid fa-filter text-sm"></i>
        </button>
        <a href="{{ route('videos.create') }}" class="flex items-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all">
            <i class="fa-solid fa-plus text-xs"></i>
            Add New Lesson
        </a>
    </div>

    {{-- Filters Card (toggleable) --}}
    <div id="filterCardWrapper" class="filter-card-wrapper {{ $filtersOpen ? 'is-open' : '' }}">
        <div class="filter-card-inner">
            <div class="filter-card-panel glass-panel dark:bg-slate-800/80 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <form action="{{ route('videos') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Search by Title</label>
                            <div class="relative group">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm"></i>
                                <input name="search" value="{{ $filters['search'] ?? '' }}" class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none" placeholder="Lesson title..." type="text">
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</label>
                            <select name="chapter" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Chapters</option>
                                <option value="Chapter 1: Intro" {{ ($filters['chapter'] ?? '') == 'Chapter 1: Intro' ? 'selected' : '' }}>Chapter 1: Intro</option>
                                <option value="Chapter 2: Basics" {{ ($filters['chapter'] ?? '') == 'Chapter 2: Basics' ? 'selected' : '' }}>Chapter 2: Basics</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Topic</label>
                            <select name="topic" class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
                                <option value="">All Topics</option>
                                <option value="Mathematics" {{ ($filters['topic'] ?? '') == 'Mathematics' ? 'selected' : '' }}>Mathematics</option>
                                <option value="Science" {{ ($filters['topic'] ?? '') == 'Science' ? 'selected' : '' }}>Science</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Date Range</label>
                            <div class="relative">
                                <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm pointer-events-none z-10"></i>
                                <input id="videos-date-range" name="date_range" type="text" value="{{ (($filters['date_from'] ?? '') && ($filters['date_to'] ?? '')) ? ($filters['date_from'] . ' to ' . $filters['date_to']) : '' }}" class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none" placeholder="Select date range" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-3 flex-wrap">
                        <a href="{{ route('videos') }}" class="text-sm text-on-surface-variant hover:text-error transition-colors flex items-center gap-1">
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
                @forelse($videos as $video)
                    {{-- Video Card --}}
                    <div class="glass-panel dark:bg-slate-800/80 rounded-2xl overflow-hidden hover-ambient-shadow border border-outline-variant/30 dark:border-slate-700 flex flex-col cursor-pointer group">
                        {{-- Thumbnail Area --}}
                        <div class="relative w-full aspect-video bg-surface-container-highest dark:bg-slate-700 overflow-hidden">
                            <img src="{{ $video['thumbnail'] }}" alt="{{ $video['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                                <div class="w-12 h-12 bg-white/90 dark:bg-white/80 rounded-full flex items-center justify-center shadow-lg text-primary transform scale-90 group-hover:scale-110 transition-all">
                                    <i class="fa-solid fa-play text-xl ml-1"></i>
                                </div>
                            </div>
                            <div class="absolute bottom-2 right-2 bg-black/70 text-white text-[10px] font-semibold px-2 py-1 rounded backdrop-blur-sm">
                                {{ explode(' • ', $video['meta'])[0] }}
                            </div>
                        </div>

                        {{-- Content Area --}}
                        <div class="p-4 flex flex-col flex-1 gap-2">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-bold text-on-surface dark:text-slate-200 line-clamp-2 leading-tight flex-1 group-hover:text-primary transition-colors">
                                    {{ $video['title'] }}
                                </h3>
                                <a href="{{ route('videos.edit', $video['id']) }}" title="Edit lesson" class="text-on-surface-variant dark:text-slate-400 hover:text-primary transition-colors mt-0.5">
                                    <i class="fa-solid fa-pen text-sm"></i>
                                </a>
                            </div>

                            <p class="text-xs text-on-surface-variant dark:text-slate-400 flex items-center gap-1.5 mt-1">
                                <i class="fa-solid fa-folder text-[10px]"></i>
                                <span class="truncate">{{ $video['chapter'] }}</span>
                            </p>

                            <div class="mt-auto pt-4 flex items-center justify-between">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $video['topic_color'] }} dark:bg-primary/20 dark:text-primary-container">
                                    {{ $video['topic'] }}
                                </span>
                                <span class="text-[10px] font-medium text-outline dark:text-slate-500">{{ $video['date_added'] }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 flex flex-col items-center justify-center text-on-surface-variant dark:text-slate-500 bg-surface-container-low/50 dark:bg-slate-900/50 rounded-2xl border-2 border-dashed border-outline-variant/30 dark:border-slate-700">
                        <i class="fa-solid fa-video-slash text-4xl mb-3 text-outline/50"></i>
                        <p>No video lessons found.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-outline-variant/20 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4 bg-surface-container-lowest/30 dark:bg-slate-800/60">
            <span class="text-xs font-medium text-on-surface-variant dark:text-slate-400">
                Showing {{ $videos->count() }} {{ Str::plural('lesson', $videos->count()) }}
            </span>
            <nav class="flex items-center gap-1 bg-surface-container-low dark:bg-slate-900 rounded-full p-1 shadow-sm border border-outline-variant/30 dark:border-slate-700">
                <button class="w-8 h-8 flex items-center justify-center rounded-full text-on-surface-variant dark:text-slate-400 hover:bg-surface-variant dark:hover:bg-slate-700 disabled:opacity-50 transition-colors" disabled>
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <button class="w-8 h-8 flex items-center justify-center rounded-full bg-primary text-white font-semibold text-sm shadow-md">1</button>
                <button class="w-8 h-8 flex items-center justify-center rounded-full text-on-surface dark:text-slate-300 hover:bg-surface-variant dark:hover:bg-slate-700 font-semibold text-sm transition-colors">2</button>
                <button class="w-8 h-8 flex items-center justify-center rounded-full text-on-surface dark:text-slate-300 hover:bg-surface-variant dark:hover:bg-slate-700 font-semibold text-sm transition-colors">3</button>
                <span class="px-2 text-on-surface-variant dark:text-slate-400">...</span>
                <button class="w-8 h-8 flex items-center justify-center rounded-full text-on-surface-variant dark:text-slate-400 hover:bg-surface-variant dark:hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            </nav>
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

            flatpickr("#videos-date-range", {
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
