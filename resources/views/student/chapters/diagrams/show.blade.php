@extends('layouts.student')

@section('title', $diagram->title . ' – Diagram')
@section('meta-description', 'Diagram from ' . $chapter->title)

@push('styles')
<style>
    .glass-panel {
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.4);
    }
    .glass-panel-hover { transition: all 0.3s ease; }
    .glass-panel-hover:hover { box-shadow: 0 10px 30px rgba(0, 19, 48, 0.08); }
</style>
@endpush

@section('content')
<div class="w-full flex-1 pt-4">

    <!-- Header Section -->
    <div class="mb-8">
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', $course) }}" class="hover:text-primary transition-colors">{{ $course->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">{{ $chapter->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.diagrams', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Diagrams</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface font-semibold">{{ Str::limit($diagram->title, 40) }}</span>
        </nav>

        <div class="flex items-start justify-between gap-4 mt-3">
            <h2 class="text-3xl font-bold text-on-surface">{{ $diagram->title ?: 'Untitled diagram' }}</h2>
            <x-save-button type="diagram" :uuid="$diagram->uuid" class="text-outline hover:text-primary shrink-0 mt-1" />
        </div>

        @if($diagram->topic)
            <span class="inline-block mt-2 px-2.5 py-1 bg-surface-container-highest text-on-surface-variant rounded text-xs font-bold tracking-wider uppercase">
                {{ $diagram->topic->title }}
            </span>
        @endif
    </div>

    <!-- Two-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Left Column (Main) -->
        {{-- Wrapped so time spent studying the diagram counts towards progress,
             and so the manual tick is available. See ProgressService. --}}
        <x-module-progress :record="$diagram" class="lg:col-span-8 flex flex-col gap-6">

            <!-- Diagram Preview -->
            <div class="glass-panel rounded-xl p-2 glass-panel-hover">
                <div class="bg-surface-container-low rounded-lg overflow-hidden flex items-center justify-center relative group min-h-[400px]">
                    @if($diagram->image_url)
                        <img class="w-full h-auto max-h-[600px] object-contain"
                            src="{{ $diagram->image_url }}" alt="{{ $diagram->title }}" />

                        <div class="absolute inset-0 bg-inverse-surface/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-4">
                            <a href="{{ $diagram->image_url }}" target="_blank" rel="noopener"
                                class="bg-surface-container-lowest text-on-surface p-3 rounded-full shadow-lg hover:text-primary hover:scale-105 transition-all" title="Open full size">
                                <span class="material-symbols-outlined">zoom_in</span>
                            </a>
                            <a href="{{ $diagram->image_url }}" download
                                class="bg-surface-container-lowest text-on-surface p-3 rounded-full shadow-lg hover:text-primary hover:scale-105 transition-all" title="Download">
                                <span class="material-symbols-outlined">download</span>
                            </a>
                        </div>
                    @else
                        <div class="text-center py-16">
                            <span class="material-symbols-outlined text-on-surface-variant/40" style="font-size:48px;">image_not_supported</span>
                            <p class="text-on-surface-variant text-sm mt-2">No image has been uploaded for this diagram.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Description Section -->
            <div class="glass-panel rounded-xl p-6 glass-panel-hover">
                <h3 class="text-xl font-semibold mb-4 border-b border-surface-variant/50 pb-2">Description</h3>
                <div class="prose prose-sm max-w-none text-on-surface-variant space-y-4
                    [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mb-1
                    [&_a]:text-primary [&_img]:rounded-lg">
                    @if(filled($diagram->content))
                        {!! $diagram->content !!}
                    @else
                        <p class="text-sm">No description has been written for this diagram yet.</p>
                    @endif
                </div>
            </div>
        </x-module-progress>

        <!-- Right Column (Sidebar) -->
        <div class="lg:col-span-4 flex flex-col gap-6">

            <!-- Asset Info Card -->
            <div class="glass-panel rounded-xl p-6 glass-panel-hover">
                <div class="flex items-center gap-2 mb-4 border-b border-surface-variant/50 pb-2">
                    <span class="material-symbols-outlined text-on-surface-variant text-xl">info</span>
                    <h3 class="text-xl font-semibold">Asset Info</h3>
                </div>
                <ul class="space-y-3">
                    <li class="flex justify-between items-center gap-3">
                        <span class="text-sm font-semibold text-on-surface-variant shrink-0">Chapter</span>
                        <span class="text-sm font-medium text-right truncate">{{ $chapter->title }}</span>
                    </li>
                    @if($diagram->topic)
                        <li class="flex justify-between items-center gap-3">
                            <span class="text-sm font-semibold text-on-surface-variant shrink-0">Topic</span>
                            <span class="text-sm font-medium text-right truncate">{{ $diagram->topic->title }}</span>
                        </li>
                    @endif
                    @if($diagram->image_path)
                        <li class="flex justify-between items-center gap-3">
                            <span class="text-sm font-semibold text-on-surface-variant shrink-0">File type</span>
                            <span class="text-sm font-medium bg-surface-container-high px-2 py-0.5 rounded uppercase">
                                {{ pathinfo($diagram->image_path, PATHINFO_EXTENSION) ?: 'file' }}
                            </span>
                        </li>
                    @endif
                    <li class="flex justify-between items-center gap-3">
                        <span class="text-sm font-semibold text-on-surface-variant shrink-0">Added</span>
                        <span class="text-sm font-medium text-right">{{ $diagram->created_at?->format('d M Y') ?? '—' }}</span>
                    </li>
                    @if($diagram->addedBy)
                        <li class="flex justify-between items-center gap-3">
                            <span class="text-sm font-semibold text-on-surface-variant shrink-0">Added by</span>
                            <span class="text-sm font-medium text-right truncate">{{ $diagram->addedBy->name }}</span>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- Move through the chapter's diagrams -->
            @if($previous || $next)
                <div class="glass-panel rounded-xl p-6 glass-panel-hover">
                    <h3 class="text-xl font-semibold mb-4 border-b border-surface-variant/50 pb-2">More in this chapter</h3>
                    <div class="flex flex-col gap-3">
                        @if($previous)
                            <a href="{{ route('student.chapters.diagrams.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'diagramId' => $previous->uuid]) }}"
                                class="flex items-center gap-2 text-sm text-on-surface hover:text-primary transition-colors">
                                <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
                                <span class="truncate">{{ $previous->title }}</span>
                            </a>
                        @endif
                        @if($next)
                            <a href="{{ route('student.chapters.diagrams.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'diagramId' => $next->uuid]) }}"
                                class="flex items-center gap-2 text-sm text-on-surface hover:text-primary transition-colors justify-end text-right">
                                <span class="truncate">{{ $next->title }}</span>
                                <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
