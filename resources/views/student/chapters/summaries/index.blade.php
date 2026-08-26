@extends('layouts.student')

@section('title', 'Learning Summaries – ' . $chapter->title)
@section('meta-description', 'Review condensed takeaways and key concepts for this chapter')

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
        box-shadow: 0 10px 30px rgba(0, 19, 48, 0.08);
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')

{{-- Breadcrumb + Header --}}
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 pt-4">
    <div>
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-3 flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', $course) }}" class="hover:text-primary transition-colors">{{ $course->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">{{ $chapter->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface font-semibold">Summaries</span>
        </nav>
        <h1 class="text-on-background" style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;">Learning Summaries</h1>
        <p class="text-on-surface-variant text-base mt-1 max-w-2xl">
            @if($search || $topic)
                <span class="font-semibold text-primary">{{ $summaries->total() }}</span>
                {{ Str::plural('summary', $summaries->total()) }} matched in {{ $chapter->title }}.
            @else
                Review condensed takeaways and key concepts from {{ $chapter->title }} to reinforce your knowledge.
            @endif
        </p>
    </div>

        {{-- Search and topic, both handled on the server. --}}
        <x-chapter-filter placeholder="Search summaries"
            :search="$search"
            :active="$search || $topic"
            :clear="route('student.chapters.summaries', ['courseId' => $courseId, 'chapterId' => $chapterId])">

            @if($topics->isNotEmpty())
                <x-chapter-filter.select name="topic">
                    <option value="">All topics</option>
                    @foreach($topics as $option)
                        <option value="{{ $option->uuid }}" @selected($topic === $option->uuid)>{{ $option->title }}</option>
                    @endforeach
                </x-chapter-filter.select>
            @endif
        </x-chapter-filter>
</div>

{{-- Summary Cards Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($summaries as $summary)
        @php
            $read = $state[$summary['id']]['completed'] ?? false;
            // Written out in full rather than interpolated, so the classes
            // survive a Tailwind build that scans source for literal names.
            $accents = [
                'bg-tertiary-container/20 text-tertiary',
                'bg-primary-container/20 text-primary',
                'bg-secondary-container/20 text-secondary',
            ];
            $accent = $accents[$loop->index % count($accents)];
        @endphp

        <article class="glass-card rounded-2xl p-6 flex flex-col h-full group">
            <div class="flex justify-between items-start mb-4 gap-2">
                <div class="flex gap-2 flex-wrap min-w-0">
                    @if($summary['topic'])
                        <span class="px-2.5 py-1 {{ $accent }} rounded text-xs font-bold tracking-wider uppercase truncate max-w-[12rem]">{{ $summary['topic'] }}</span>
                    @endif
                    <span class="px-2.5 py-1 bg-surface-container-highest text-on-surface-variant rounded text-xs font-bold tracking-wider uppercase">Ch {{ $chapter->chapter_number }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @if($read)
                        <span class="inline-flex items-center gap-1 text-tertiary text-xs font-semibold" title="You have read this">
                            <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1;">check_circle</span>
                            Read
                        </span>
                    @endif
                    <x-save-button type="summary" :uuid="$summary['uuid']" class="text-on-surface-variant hover:text-primary" />
                </div>
            </div>

            <div class="flex flex-col flex-1 mb-6">
                <h3 class="text-on-surface font-semibold text-base mb-2 group-hover:text-primary transition-colors">{{ $summary['title'] }}</h3>
                <p class="text-on-surface-variant text-sm line-clamp-3 flex-1">{{ $summary['excerpt'] }}</p>
            </div>

            <div class="pt-4 border-t border-outline-variant/30 flex items-center justify-between mt-auto">
                <div class="flex items-center gap-2 text-on-surface-variant">
                    <span class="material-symbols-outlined" style="font-size:16px;">schedule</span>
                    <span class="text-xs font-medium">{{ $summary['reading_minutes'] }} min read</span>
                </div>
                <a href="{{ route('student.chapters.summaries.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'summaryId' => $summary['uuid']]) }}"
                    class="text-primary text-xs font-semibold flex items-center gap-1 hover:gap-2 transition-all">
                    Review <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                </a>
            </div>
        </article>
    @empty
        <div class="col-span-full glass-card rounded-2xl py-20 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 rounded-full bg-primary/5 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary text-4xl">
                    {{ $search || $topic ? 'search_off' : 'description' }}
                </span>
            </div>
            <h3 class="text-on-surface font-semibold text-lg mb-2">
                {{ $search || $topic ? 'Nothing matched' : 'No summaries yet' }}
            </h3>
            <p class="text-on-surface-variant text-sm max-w-md mb-6">
                @if($search || $topic)
                    No summary in this chapter matches those filters.
                @else
                    Nothing has been published for this chapter yet. Check back soon.
                @endif
            </p>
            <a href="{{ $search || $topic
                    ? route('student.chapters.summaries', ['courseId' => $courseId, 'chapterId' => $chapterId])
                    : route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full inline-flex items-center gap-2">
                {{ $search || $topic ? 'Clear filters' : 'Back to the chapter' }}
                <span class="material-symbols-outlined text-sm">{{ $search || $topic ? 'restart_alt' : 'arrow_forward' }}</span>
            </a>
        </div>
    @endforelse
</div>

@if($summaries->hasPages())
    <div class="mt-10 flex justify-center">
        {{ $summaries->links() }}
    </div>
@endif

@endsection
