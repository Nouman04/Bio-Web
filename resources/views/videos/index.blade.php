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
</style>
@endpush

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold text-on-surface dark:text-white">Video Lessons</h2>
            <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-1">Manage and organize your instructional video content.</p>
        </div>
        <a href="{{ route('videos.create') }}" class="bg-gradient-to-r from-primary to-primary-container text-white px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg hover:scale-[1.02] transition-all flex items-center gap-2">
            <i class="fa-solid fa-plus text-[16px]"></i>
            Add New Lesson
        </a>
    </div>

    {{-- Filter Bar (Glass Panel) --}}
    <div class="glass-panel dark:bg-slate-800/80 rounded-xl p-4 flex flex-wrap gap-4 items-end shadow-sm mb-6 border-outline-variant/30 dark:border-slate-700">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Search by Title</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm"></i>
                <input class="w-full pl-9 pr-3 py-2 bg-surface-container-lowest dark:bg-slate-900 rounded-lg border border-outline-variant/30 dark:border-slate-700 shadow-inner focus:ring-1 focus:ring-primary focus:border-primary text-sm text-on-surface dark:text-slate-200 outline-none" placeholder="Lesson title..." type="text">
            </div>
        </div>
        <div class="w-full sm:w-auto min-w-[140px]">
            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Chapter</label>
            <select class="w-full py-2 pl-3 pr-8 bg-surface-container-lowest dark:bg-slate-900 rounded-lg border border-outline-variant/30 dark:border-slate-700 shadow-inner focus:ring-1 focus:ring-primary focus:border-primary text-sm text-on-surface dark:text-slate-200 appearance-none cursor-pointer outline-none">
                <option>All Chapters</option>
                <option>Chapter 1: Intro</option>
                <option>Chapter 2: Basics</option>
            </select>
        </div>
        <div class="w-full sm:w-auto min-w-[140px]">
            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Topic</label>
            <select class="w-full py-2 pl-3 pr-8 bg-surface-container-lowest dark:bg-slate-900 rounded-lg border border-outline-variant/30 dark:border-slate-700 shadow-inner focus:ring-1 focus:ring-primary focus:border-primary text-sm text-on-surface dark:text-slate-200 appearance-none cursor-pointer outline-none">
                <option>All Topics</option>
                <option>Mathematics</option>
                <option>Science</option>
            </select>
        </div>
        <div class="w-full sm:w-auto min-w-[140px]">
            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Date</label>
            <input class="w-full py-2 px-3 bg-surface-container-lowest dark:bg-slate-900 rounded-lg border border-outline-variant/30 dark:border-slate-700 shadow-inner focus:ring-1 focus:ring-primary focus:border-primary text-sm text-on-surface dark:text-slate-200 outline-none" type="date">
        </div>
        <button class="bg-surface-variant dark:bg-slate-700 text-on-surface-variant dark:text-slate-300 py-2 px-4 rounded-lg text-sm font-semibold hover:bg-outline-variant transition-colors flex items-center gap-2">
            <i class="fa-solid fa-filter text-sm"></i>
            Filter
        </button>
    </div>

    {{-- Grid Layout for Video Cards --}}
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
                        <button class="text-on-surface-variant dark:text-slate-400 hover:text-primary transition-colors mt-0.5">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
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
            <div class="col-span-full py-12 flex flex-col items-center justify-center text-on-surface-variant dark:text-slate-500 bg-surface-container-lowest/50 dark:bg-slate-800/50 rounded-2xl border-2 border-dashed border-outline-variant/30 dark:border-slate-700">
                <i class="fa-solid fa-video-slash text-4xl mb-3 text-outline/50"></i>
                <p>No video lessons found.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination (Simple) --}}
    <div class="mt-8 flex justify-center">
        <nav class="flex items-center gap-1 bg-surface-container-lowest dark:bg-slate-800 rounded-full p-1 shadow-sm border border-outline-variant/30 dark:border-slate-700">
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
@endsection
