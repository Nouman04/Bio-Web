@extends('layouts.student')

@section('title', 'Explore Courses – Catalog')
@section('meta-description', 'Discover your next learning adventure — browse all available courses')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0px 10px 30px rgba(99,102,241,0.08);
        border-color: rgba(99,102,241,0.2);
    }
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-primary-container/20 to-surface-container-lowest p-8 md:p-12 border border-white/50 glass-card mt-4 mb-8">
    <div class="relative z-10 max-w-2xl">
        <h2 class="text-on-surface mb-4" style="font-size:48px;line-height:56px;letter-spacing:-0.02em;font-weight:700;">Discover Your Next Learning Adventure</h2>
        <p class="text-on-surface-variant mb-8" style="font-size:18px;line-height:28px;">
            @if($browsing)
                The {{ $latest }} newest courses you have not subscribed to. Search to reach the rest of the library.
            @else
                {{ $courses->total() }} {{ Str::plural('course', $courses->total()) }} matched.
            @endif
        </p>
        {{-- Search runs on the server; the form keeps the current filters. --}}
        <form method="GET" class="flex flex-wrap gap-3 max-w-xl">
            <input type="hidden" name="category" value="{{ $category }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <div class="relative flex-1 min-w-[240px]">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
                <input type="search" name="search" value="{{ $search }}"
                    placeholder="Search courses by title or description"
                    class="w-full bg-surface-container-lowest border border-outline-variant/40 rounded-full pl-11 pr-4 py-3 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/50">
            </div>
            <button type="submit"
                class="bg-primary text-on-primary px-6 py-3 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all hover:-translate-y-0.5 bg-gradient-to-r from-primary to-primary-container flex items-center gap-2">
                Start Exploring
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </button>
        </form>
    </div>
    <!-- Decorative blobs -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-primary/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
    <div class="absolute bottom-0 right-20 w-48 h-48 bg-secondary-container/20 rounded-full blur-2xl translate-y-1/3 pointer-events-none"></div>
</section>

<!-- Filters & Sorting Bar -->
<section class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-4 border-b border-surface-variant mb-8">
    {{-- Category and sort in one form, so changing either keeps the other and
         the current search. --}}
    <form method="GET" class="flex flex-col sm:flex-row sm:items-center gap-4 w-full justify-between">
        <input type="hidden" name="search" value="{{ $search }}">

        <div class="flex items-center gap-2">
            <span class="text-on-surface-variant text-xs">Category:</span>
            <div class="relative">
                <select name="category" onchange="this.form.submit()"
                    class="appearance-none bg-surface-container-lowest border border-outline-variant text-on-surface text-xs rounded-lg pl-3 pr-8 py-1.5 focus:ring-primary focus:border-primary shadow-sm cursor-pointer min-w-[160px]">
                    <option value="">All categories</option>
                    @foreach($categories as $title)
                        <option value="{{ $title }}" @selected($category === $title)>{{ $title }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-sm">expand_more</span>
            </div>
            @if($category || $search)
                <a href="{{ route('student.catalog') }}"
                    class="text-on-surface-variant hover:text-primary text-xs flex items-center gap-1 transition-colors" title="Clear filters">
                    <span class="material-symbols-outlined text-sm">restart_alt</span>
                    Clear
                </a>
            @endif
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto">
            <span class="text-on-surface-variant text-xs">Sort by:</span>
            <div class="relative">
                <select name="sort" onchange="this.form.submit()"
                    class="appearance-none bg-surface-container-lowest border border-outline-variant text-on-surface text-xs rounded-lg pl-3 pr-8 py-1.5 focus:ring-primary focus:border-primary shadow-sm cursor-pointer">
                    <option value="newest" @selected($sort === 'newest')>Newest</option>
                    <option value="title" @selected($sort === 'title')>Title A–Z</option>
                    <option value="chapters" @selected($sort === 'chapters')>Most chapters</option>
                </select>
                <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-sm">expand_more</span>
            </div>
        </div>
    </form>
</section>

<!-- Course Grid -->
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($courses as $course)
        @php
            $palettes = [
                ['#4648d4', '#7c3aed'], ['#0891b2', '#4648d4'], ['#c026d3', '#7c3aed'],
                ['#059669', '#0891b2'], ['#ea580c', '#c026d3'],
            ];
            $palette = $palettes[$course->id % count($palettes)];
        @endphp
        <article class="glass-card rounded-xl overflow-hidden flex flex-col h-full group">
            <div class="relative h-40 overflow-hidden flex items-center justify-center"
                style="background-image: linear-gradient(135deg, {{ $palette[0] }}, {{ $palette[1] }});">
                <span class="text-white/90 font-bold transition-transform duration-500 group-hover:scale-110" style="font-size:44px;line-height:1;">
                    {{ Str::upper(Str::substr($course->title, 0, 1)) }}
                </span>
                <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-md text-primary text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded">
                    {{ $course->category?->title ?: 'General' }}
                </div>
            </div>
            <div class="p-4 flex flex-col flex-1">
                <h3 class="text-on-surface mb-2 line-clamp-2 leading-tight" style="font-size:20px;line-height:28px;font-weight:600;">{{ $course->title }}</h3>
                <p class="text-on-surface-variant mb-4 line-clamp-2 text-sm">{{ $course->excerpt ?: 'No description yet.' }}</p>
                <div class="mt-auto pt-4 border-t border-surface-variant flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1 text-outline">
                        <span class="material-symbols-outlined text-sm">menu_book</span>
                        <span class="text-on-surface text-xs font-medium">{{ $course->chapters_count }}</span>
                        <span class="text-outline text-xs">{{ Str::plural('chapter', $course->chapters_count) }}</span>
                    </div>
                    {{-- A priced course goes to its plan; a free one opens. --}}
                    @if($course->hasStripePlan())
                        <a href="{{ route('public.subscribe.plans', $course) }}"
                            class="text-primary text-xs font-semibold hover:underline flex items-center gap-1 whitespace-nowrap">
                            {{ $course->plan?->formatted_price ?? 'Subscribe' }}
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    @else
                        <a href="{{ route('student.chapters', ['courseId' => $course->uuid]) }}"
                            class="text-primary text-xs font-semibold hover:underline flex items-center gap-1 whitespace-nowrap">
                            Start Free <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="col-span-full py-20 flex flex-col items-center justify-center text-center glass-card rounded-2xl">
            <div class="w-24 h-24 bg-primary/5 rounded-full flex items-center justify-center mb-6">
                <span class="material-symbols-outlined text-5xl text-primary" style="font-variation-settings: 'FILL' 1;">search_off</span>
            </div>
            <h3 class="text-on-surface mb-3" style="font-size:24px;line-height:32px;font-weight:600;">
                {{ $search || $category ? 'Nothing matched' : 'Nothing left to explore' }}
            </h3>
            <p class="text-on-surface-variant max-w-lg mx-auto mb-8 text-lg leading-relaxed">
                @if($search || $category)
                    No course matches those filters. Try a different search, or clear them.
                @else
                    You are subscribed to everything published so far. New courses will appear here.
                @endif
            </p>
            @if($search || $category)
                <a href="{{ route('student.catalog') }}"
                    class="bg-primary text-on-primary text-sm font-semibold py-3 px-8 rounded-full inline-flex items-center gap-2">
                    Clear filters
                    <span class="material-symbols-outlined text-sm">restart_alt</span>
                </a>
            @endif
        </div>
    @endforelse
</section>

{{-- Browsing shows a fixed few and points at the search; a filtered list pages
     through the rest, keeping the search and filters as it goes. --}}
@if($browsing)
    @if($courses->isNotEmpty())
        <p class="text-center text-on-surface-variant text-sm pt-8 pb-4">
            Looking for something else? Search above to browse the whole library.
        </p>
    @endif
@elseif($courses->hasPages())
    <div class="flex justify-center pt-8 pb-4">
        {{ $courses->links() }}
    </div>
@endif
@endsection
