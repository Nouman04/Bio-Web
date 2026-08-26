@extends('layouts.student')

@section('title', $video->title . ' – Video Lesson')

@push('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
    .hover-shadow:hover {
        box-shadow: 0px 10px 30px rgba(70, 72, 212, 0.08);
        transform: translateY(-2px);
    }
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@section('content')
<div class="w-full max-w-[1600px] mx-auto flex-1 flex flex-col xl:flex-row gap-8 pt-4">
    <!-- Primary Learning Area -->
    <div class="flex-1 flex flex-col gap-6">
        <!-- Breadcrumbs -->
        <nav class="flex text-xs text-on-surface-variant items-center gap-2 overflow-x-auto whitespace-nowrap pb-2 scrollbar-hide">
            <a class="hover:text-primary transition-colors" href="{{ route('student.courses') }}">My Courses</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a class="hover:text-primary transition-colors" href="{{ route('student.courses.show', $course) }}">{{ $course->title }}</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a class="hover:text-primary transition-colors" href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}">{{ $chapter->title }}</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a class="hover:text-primary transition-colors" href="{{ route('student.chapters.videos', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}">Videos</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-on-surface font-medium">{{ $video->title }}</span>
        </nav>

        {{-- The real player. Wrapped so watching reports back: progress.js
             reports as it plays and completes the lesson at 90%. --}}
        <x-module-progress :record="$video">
            @if($video->video_url && ! $video->is_external)
                <video controls preload="metadata"
                    class="w-full aspect-video bg-black rounded-xl overflow-hidden shadow-md"
                    src="{{ $video->video_url }}"></video>
            @elseif($video->is_external)
                <div class="w-full aspect-video bg-black rounded-xl overflow-hidden relative shadow-md flex flex-col items-center justify-center gap-4">
                    <span class="material-symbols-outlined text-white/70" style="font-size:64px;">play_circle</span>
                    <a href="{{ $video->video_url }}" target="_blank" rel="noopener"
                        class="bg-primary text-on-primary font-semibold text-sm px-6 py-2.5 rounded-full inline-flex items-center gap-2">
                        Watch on the host site
                        <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                    </a>
                    <p class="text-white/60 text-xs">Hosted elsewhere — tick it off below when you have finished.</p>
                </div>
            @else
                <div class="w-full aspect-video bg-black rounded-xl overflow-hidden relative shadow-md flex flex-col items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-white/40" style="font-size:56px;">videocam_off</span>
                    <p class="text-white/60 text-sm">This lesson has no video file yet.</p>
                </div>
            @endif
        </x-module-progress>

        <!-- Lesson Info -->
        <div class="flex flex-col gap-4 bg-surface-container-lowest p-6 rounded-xl glass-panel relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary to-primary-container"></div>
            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        @if($video->topic)
                            <span class="bg-secondary-container/20 text-secondary px-2 py-0.5 rounded text-xs uppercase tracking-wider">{{ $video->topic->title }}</span>
                        @endif
                        @if($done)
                            <span class="bg-tertiary/10 text-tertiary px-2 py-0.5 rounded text-xs font-semibold">Completed</span>
                        @elseif($watched > 0)
                            <span class="bg-primary/10 text-primary px-2 py-0.5 rounded text-xs font-semibold">{{ $watched }}% watched</span>
                        @endif
                        <span class="text-on-surface-variant text-xs">Added {{ $video->created_at?->diffForHumans() }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-on-surface mb-2">{{ $video->title }}</h1>
                    <p class="text-sm text-on-surface-variant max-w-3xl">{{ $video->description ? strip_tags($video->description) : 'No description for this lesson yet.' }}</p>
                </div>
            </div>

            @if($video->addedBy)
                <div class="h-px bg-outline-variant/30 w-full my-2"></div>
                <div class="flex items-center gap-4">
                    {{-- No avatar column, so the initial stands in for a photo. --}}
                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold border-2 border-surface-container-highest shadow-sm shrink-0">
                        {{ Str::upper(Str::substr($video->addedBy->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-on-surface">{{ $video->addedBy->name }}</div>
                        <div class="text-xs text-on-surface-variant">Instructor</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Sidebar / Secondary Content -->
    <aside class="w-full xl:w-[400px] flex flex-col gap-6">
        <!-- Chapter Playlist -->
        <div class="bg-surface-container-lowest rounded-xl glass-panel overflow-hidden flex flex-col">
            <div class="p-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low">
                <h3 class="text-sm font-semibold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">format_list_bulleted</span>
                    Chapter Playlist
                </h3>
                <span class="text-xs text-on-surface-variant bg-surface-container py-1 px-2 rounded-md">
                    {{ $completedCount }}/{{ $playlist->count() }} {{ Str::plural('Lesson', $playlist->count()) }}
                </span>
            </div>
            <div class="flex flex-col max-h-[400px] overflow-y-auto p-2 scrollbar-hide space-y-1">
                @foreach($playlist as $lesson)
                    @php
                        $isCurrent = $lesson->id === $video->id;
                        $lessonDone = $state[$lesson->id]['completed'] ?? false;
                        $lessonWatched = $state[$lesson->id]['progress'] ?? 0;
                    @endphp

                    @if($isCurrent)
                        <div class="flex gap-3 p-3 rounded-lg bg-primary/10 border-l-4 border-primary items-start">
                            <div class="mt-1 text-primary relative flex h-5 w-5 items-center justify-center shrink-0">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-20"></span>
                                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">play_circle</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-primary">{{ $lesson->title }}</h4>
                                <p class="text-xs text-primary/80 mt-1">
                                    Currently watching
                                    @if($lessonWatched > 0)
                                        &middot; {{ $lessonWatched }}%
                                    @endif
                                </p>
                            </div>
                        </div>
                    @else
                        <a class="flex gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors group items-start"
                            href="{{ route('student.chapters.videos.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'videoId' => $lesson->uuid]) }}">
                            <div class="mt-1 shrink-0 {{ $lessonDone ? 'text-tertiary' : 'text-outline-variant' }}">
                                <span class="material-symbols-outlined text-[20px]"
                                    @if($lessonDone) style="font-variation-settings: 'FILL' 1;" @endif>
                                    {{ $lessonDone ? 'check_circle' : 'play_circle' }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold {{ $lessonDone ? 'text-on-surface-variant line-through' : 'text-on-surface' }} group-hover:text-primary transition-colors">{{ $lesson->title }}</h4>
                                <p class="text-xs text-on-surface-variant/70 mt-1">
                                    @if($lessonDone)
                                        Completed
                                    @elseif($lessonWatched > 0)
                                        {{ $lessonWatched }}% watched
                                    @else
                                        Not started
                                    @endif
                                </p>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>

            @if($next)
                <a href="{{ route('student.chapters.videos.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'videoId' => $next->uuid]) }}"
                    class="p-4 border-t border-outline-variant/10 flex items-center justify-between gap-2 hover:bg-surface-container-low transition-colors group">
                    <span class="min-w-0">
                        <span class="block text-on-surface-variant text-xs">Up next</span>
                        <span class="block text-on-surface text-sm font-semibold truncate group-hover:text-primary transition-colors">{{ $next->title }}</span>
                    </span>
                    <span class="material-symbols-outlined text-primary group-hover:translate-x-1 transition-transform shrink-0">arrow_forward</span>
                </a>
            @endif
        </div>

        <!-- Resources Section -->
        <div class="bg-surface-container-lowest rounded-xl glass-panel p-6 flex flex-col gap-4">
            <h3 class="text-sm font-semibold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary-container">topic</span>
                Lesson Resources
            </h3>
            <div class="grid grid-cols-1 gap-3">
                @if($quiz)
                    <a href="{{ route('student.chapters.quizzes.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'quizId' => $quiz->uuid]) }}"
                        class="flex items-center p-3 rounded-lg border border-outline-variant/20 hover:border-primary/50 hover:bg-surface-container-low transition-all group">
                        <div class="w-10 h-10 rounded-md bg-error/10 text-error flex items-center justify-center mr-3 shrink-0">
                            <span class="material-symbols-outlined">quiz</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-on-surface group-hover:text-primary transition-colors truncate">{{ $quiz->title }}</div>
                            <div class="text-xs text-on-surface-variant">
                                {{ $quiz->questions_count }} {{ Str::plural('question', $quiz->questions_count) }}@if($quiz->duration) &middot; {{ $quiz->duration }} mins @endif
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-outline-variant group-hover:text-primary transition-colors shrink-0">arrow_forward_ios</span>
                    </a>
                @endif

                <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                    class="flex items-center p-3 rounded-lg border border-outline-variant/20 hover:border-primary/50 hover:bg-surface-container-low transition-all group">
                    <div class="w-10 h-10 rounded-md bg-secondary/10 text-secondary flex items-center justify-center mr-3 shrink-0">
                        <span class="material-symbols-outlined">dashboard</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-on-surface group-hover:text-primary transition-colors">Back to the chapter</div>
                        <div class="text-xs text-on-surface-variant">Notes, flashcards and more</div>
                    </div>
                    <span class="material-symbols-outlined text-outline-variant group-hover:text-primary transition-colors shrink-0">arrow_forward_ios</span>
                </a>
            </div>
        </div>
    </aside>
</div>
@endsection
