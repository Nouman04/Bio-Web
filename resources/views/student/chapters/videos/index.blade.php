@extends('layouts.student')

@section('title', 'Chapter Videos – ' . $chapter->title)

@push('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }

    .hover-lift {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
    }
</style>
@endpush

@section('content')
<div class="py-8 max-w-[1600px] w-full mx-auto flex-1 flex flex-col">
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-on-surface-variant text-xs mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('student.courses') }}">My Courses</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <a class="hover:text-primary transition-colors" href="{{ route('student.courses.show', $course) }}">{{ $course->title }}</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <a class="hover:text-primary transition-colors" href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}">{{ $chapter->title }}</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span class="text-on-surface font-semibold">Videos</span>
    </nav>

    <!-- Header & Controls -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-on-surface mb-2">Chapter Videos</h1>
            <p class="text-sm text-on-surface-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">video_library</span>
                @if($search)
                    {{ $videos->total() }} {{ Str::plural('lesson', $videos->total()) }} matched
                @else
                    {{ $videos->total() }} {{ Str::plural('Video Lesson', $videos->total()) }} Available
                @endif
            </p>
        </div>

        {{-- Search and sort share one form, so changing either keeps the other. --}}
        <x-chapter-filter placeholder="Find a specific lesson"
            :search="$search"
            :active="$search || $sort !== array_key_first($sorts)"
            :clear="route('student.chapters.videos', ['courseId' => $courseId, 'chapterId' => $chapterId])">

            <x-chapter-filter.select name="sort" icon="sort">
                @foreach($sorts as $value => $label)
                    <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                @endforeach
            </x-chapter-filter.select>
        </x-chapter-filter>
    </div>

    <!-- Video Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($videos as $video)
            @php
                $watched = $state[$video['id']]['progress'] ?? 0;
                $done = $state[$video['id']]['completed'] ?? false;
                // Lessons carry no cover image, so the tile is a gradient keyed
                // off the record — stable per lesson, and no stock art.
                $palettes = [
                    ['#4648d4', '#7c3aed'], ['#0891b2', '#4648d4'], ['#c026d3', '#7c3aed'],
                    ['#059669', '#0891b2'], ['#ea580c', '#c026d3'],
                ];
                $palette = $palettes[$video['tint']];
            @endphp

            {{-- The card is one link, so the save button sits beside it rather
                 than inside — a button nested in an anchor would navigate. --}}
            <div class="relative">
            <x-save-button type="video" :uuid="$video['uuid']"
                class="absolute top-3 right-3 z-30 bg-inverse-surface/80 backdrop-blur-sm text-inverse-on-surface hover:text-primary p-1.5 rounded-md" />

            <a href="{{ route('student.chapters.videos.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'videoId' => $video['uuid']]) }}"
                class="glass-panel rounded-xl overflow-hidden flex flex-col hover-lift group border-outline-variant/30 shadow-sm relative block h-full">

                <div class="relative h-48 w-full overflow-hidden flex items-center justify-center"
                    style="background-image: linear-gradient(135deg, {{ $palette[0] }}, {{ $palette[1] }});">
                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center transform scale-90 group-hover:scale-100 transition-all shadow-lg border border-white/30">
                            <span class="material-symbols-outlined text-white text-3xl ml-1" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                        </div>
                    </div>

                    {{-- How far in they got, from the progress tables. --}}
                    @if($watched > 0 && ! $done)
                        <div class="absolute bottom-0 left-0 h-1 bg-surface-container w-full z-20">
                            <div class="h-full bg-secondary-container" style="width: {{ $watched }}%"></div>
                        </div>
                    @endif

                    @if($video['is_external'])
                        <div class="absolute bottom-3 right-3 bg-inverse-surface/80 backdrop-blur-sm text-inverse-on-surface text-[10px] px-2 py-1 rounded-md z-20 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[12px]">open_in_new</span>
                            External
                        </div>
                    @endif

                    @if($done)
                        <div class="absolute top-3 left-3 bg-tertiary-container/90 text-on-tertiary-container text-[10px] px-2 py-1 rounded-md shadow-sm border border-tertiary/20 font-semibold flex items-center gap-1 z-20">
                            <span class="material-symbols-outlined text-[12px]">check_circle</span>
                            Completed
                        </div>
                    @elseif($watched > 0)
                        <div class="absolute top-3 left-3 bg-secondary-container/90 text-on-secondary-container text-[10px] px-2 py-1 rounded-md shadow-sm border border-secondary/20 font-semibold flex items-center gap-1 z-20">
                            <span class="material-symbols-outlined text-[12px]">schedule</span>
                            In Progress ({{ $watched }}%)
                        </div>
                    @endif
                </div>

                <div class="p-5 flex flex-col flex-1">
                    <h3 class="text-lg font-semibold text-on-surface mb-2 line-clamp-2 leading-tight group-hover:text-primary transition-colors">{{ $video['title'] }}</h3>
                    <p class="text-sm text-on-surface-variant mb-4 line-clamp-2">{{ $video['excerpt'] }}</p>
                    <div class="mt-auto">
                        <div class="flex justify-between items-center mb-4 text-xs text-outline gap-2">
                            <span class="truncate">{{ $video['author'] ?? 'Unknown' }}</span>
                            <span class="shrink-0">{{ $video['added_human'] }}</span>
                        </div>
                        @if($done)
                            <div class="w-full bg-surface-container-high text-on-surface text-sm font-semibold py-2.5 rounded-full flex justify-center items-center gap-2 group-hover:bg-primary group-hover:text-white transition-all">
                                Watch Again
                            </div>
                        @elseif($watched > 0)
                            <div class="w-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold py-2.5 rounded-full flex justify-center items-center gap-2 group-hover:shadow-md transition-all">
                                Resume Video
                            </div>
                        @else
                            <div class="w-full bg-surface-container-high text-on-surface text-sm font-semibold py-2.5 rounded-full flex justify-center items-center gap-2 group-hover:bg-primary group-hover:text-white transition-all">
                                Watch Now
                            </div>
                        @endif
                    </div>
                </div>
            </a>
            </div>
        @empty
            <div class="col-span-full glass-panel rounded-xl py-20 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 rounded-full bg-primary/5 flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-primary text-4xl">
                        {{ $search ? 'search_off' : 'videocam_off' }}
                    </span>
                </div>
                <h3 class="text-on-surface font-semibold text-lg mb-2">
                    {{ $search ? 'Nothing matched' : 'No video lessons yet' }}
                </h3>
                <p class="text-on-surface-variant text-sm max-w-md mb-6">
                    @if($search)
                        No lesson in this chapter matches that search.
                    @else
                        Nothing has been published for this chapter yet. Check back soon.
                    @endif
                </p>
                <a href="{{ $search
                        ? route('student.chapters.videos', ['courseId' => $courseId, 'chapterId' => $chapterId])
                        : route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                    class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full inline-flex items-center gap-2">
                    {{ $search ? 'Clear search' : 'Back to the chapter' }}
                    <span class="material-symbols-outlined text-sm">{{ $search ? 'restart_alt' : 'arrow_forward' }}</span>
                </a>
            </div>
        @endforelse
    </div>

    @if($videos->hasPages())
        <div class="mt-10 flex justify-center">
            {{ $videos->links() }}
        </div>
    @endif
</div>
@endsection
