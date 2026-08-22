@extends('layouts.student')

@section('title', 'Educational Diagrams – ' . $chapter->title)
@section('meta-description', 'Visual diagrams and illustrations for this chapter')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .glass-card {
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.4);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        box-shadow: 0 10px 30px rgba(99,102,241,0.08);
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')

{{-- Breadcrumb + Header --}}
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-outline-variant/30 mb-8 pt-4">
    <div>
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-3 flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', $course) }}" class="hover:text-primary transition-colors">{{ $course->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">{{ $chapter->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface font-semibold">Diagrams</span>
        </nav>
        <h1 class="text-on-background" style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.02em;">Educational Diagrams</h1>
        <p class="text-on-surface-variant text-base mt-2 max-w-2xl">
            @if($search || $topic)
                <span class="font-semibold text-primary">{{ $diagrams->total() }}</span>
                {{ Str::plural('diagram', $diagrams->total()) }} matched in {{ $chapter->title }}.
            @else
                Visual aids for the concepts covered in {{ $chapter->title }}.
            @endif
        </p>
    </div>

    {{-- Search and topic, both handled on the server. --}}
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant" style="font-size:20px;">search</span>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search diagrams"
                class="bg-surface-container-lowest border border-outline-variant text-on-surface text-sm rounded-lg py-2.5 pl-10 pr-4 focus:ring-primary focus:border-primary transition-colors w-full sm:w-56">
        </div>
        @if($topics->isNotEmpty())
            <div class="relative">
                <select name="topic" onchange="this.form.submit()"
                    class="appearance-none bg-surface-container-lowest border border-outline-variant text-on-surface text-sm rounded-lg py-2.5 pl-4 pr-8 focus:ring-primary focus:border-primary hover:bg-surface-container-low transition-colors cursor-pointer">
                    <option value="">All topics</option>
                    @foreach($topics as $option)
                        <option value="{{ $option->uuid }}" @selected($topic === $option->uuid)>{{ $option->title }}</option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-on-surface-variant">
                    <span class="material-symbols-outlined" style="font-size:20px;">arrow_drop_down</span>
                </div>
            </div>
        @endif
        <button type="submit" class="flex items-center justify-center gap-2 bg-primary-container text-on-primary-container hover:bg-primary hover:text-on-primary p-2.5 rounded-lg transition-all">
            <span class="material-symbols-outlined" style="font-size:20px;">filter_list</span>
        </button>
        @if($search || $topic)
            <a href="{{ route('student.chapters.diagrams', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                class="text-on-surface-variant hover:text-primary text-sm flex items-center gap-1 transition-colors">
                <span class="material-symbols-outlined" style="font-size:20px;">restart_alt</span> Clear
            </a>
        @endif
    </form>
</div>

{{-- Gallery Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($diagrams as $diagram)
        @php $viewed = $state[$diagram->id]['completed'] ?? false; @endphp

        <div class="glass-card rounded-xl overflow-hidden flex flex-col group relative">
            <a href="{{ route('student.chapters.diagrams.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'diagramId' => $diagram->uuid]) }}"
                class="aspect-video relative overflow-hidden bg-surface-container-lowest border-b border-outline-variant/20 block">
                @if($diagram->image_url)
                    <img alt="{{ $diagram->title }}" loading="lazy"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        src="{{ $diagram->image_url }}"/>
                @else
                    {{-- No image uploaded, so the tile says so rather than
                         showing a broken picture. --}}
                    <div class="w-full h-full flex flex-col items-center justify-center gap-1 text-outline-variant">
                        <span class="material-symbols-outlined text-4xl">account_tree</span>
                        <span class="text-xs">No image</span>
                    </div>
                @endif

                @if($diagram->topic)
                    <div class="absolute top-3 left-3 bg-tertiary-container/90 backdrop-blur text-on-tertiary-container text-xs font-bold px-2 py-1 rounded-md flex items-center gap-1 shadow-sm max-w-[70%]">
                        <span class="material-symbols-outlined shrink-0" style="font-size:14px;">account_tree</span>
                        <span class="truncate">{{ $diagram->topic->title }}</span>
                    </div>
                @endif

                @if($viewed)
                    <div class="absolute top-3 right-12 bg-tertiary-container/90 backdrop-blur text-on-tertiary-container p-1.5 rounded-md shadow-sm" title="You have viewed this">
                        <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1;">check_circle</span>
                    </div>
                @endif
            </a>

            {{-- Outside the anchor, so saving does not open the diagram. --}}
            <x-save-button :record="$diagram"
                class="absolute top-3 right-3 bg-surface-container-lowest/90 backdrop-blur text-on-surface-variant hover:text-primary p-1.5 rounded-md shadow-sm z-10" />

            <div class="p-5 flex flex-col flex-1">
                <h3 class="text-on-surface font-semibold text-base mb-2 line-clamp-2 group-hover:text-primary transition-colors">{{ $diagram->title ?: 'Untitled diagram' }}</h3>
                <p class="text-on-surface-variant text-xs mb-4 line-clamp-2">
                    {{ $diagram->content ? Str::limit(strip_tags($diagram->content), 110) : 'No description yet.' }}
                </p>
                <div class="mt-auto pt-4 border-t border-outline-variant/20 flex items-center justify-between gap-2">
                    <span class="text-outline text-xs truncate">Added {{ $diagram->created_at?->diffForHumans() }}</span>
                    <a href="{{ route('student.chapters.diagrams.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'diagramId' => $diagram->uuid]) }}"
                        class="text-primary text-xs font-semibold hover:text-primary-container flex items-center gap-1 group/btn transition-colors shrink-0">
                        View Full
                        <span class="material-symbols-outlined text-sm group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full glass-card rounded-xl py-20 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 rounded-full bg-primary/5 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary text-4xl">{{ $search || $topic ? 'search_off' : 'account_tree' }}</span>
            </div>
            <h3 class="text-on-surface font-semibold text-lg mb-2">
                {{ $search || $topic ? 'Nothing matched' : 'No diagrams yet' }}
            </h3>
            <p class="text-on-surface-variant text-sm max-w-md mb-6">
                @if($search || $topic)
                    No diagram in this chapter matches those filters.
                @else
                    Nothing has been published for this chapter yet. Check back soon.
                @endif
            </p>
            <a href="{{ $search || $topic
                    ? route('student.chapters.diagrams', ['courseId' => $courseId, 'chapterId' => $chapterId])
                    : route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full inline-flex items-center gap-2">
                {{ $search || $topic ? 'Clear filters' : 'Back to the chapter' }}
                <span class="material-symbols-outlined text-sm">{{ $search || $topic ? 'restart_alt' : 'arrow_forward' }}</span>
            </a>
        </div>
    @endforelse
</div>

@if($diagrams->hasPages())
    <div class="mt-10 flex justify-center">
        {{ $diagrams->links() }}
    </div>
@endif

@endsection
