@extends('layouts.student')

@section('title', 'Guides – ' . $chapter->title)
@section('meta-description', 'Theory and ATP guides for this chapter')

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
            <span class="text-on-surface font-semibold">Guides</span>
        </nav>
        <h1 class="text-on-background" style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;">Chapter Guides</h1>
        <p class="text-on-surface-variant text-base mt-1 max-w-2xl">
            @if($search || $type)
                <span class="font-semibold text-primary">{{ $guides->total() }}</span>
                {{ Str::plural('guide', $guides->total()) }} matched in {{ $chapter->title }}.
            @else
                Theory and ATP walkthroughs for {{ $chapter->title }}.
            @endif
        </p>
    </div>

    {{-- Search and kind, both handled on the server. --}}
    <x-chapter-filter placeholder="Search guides"
        :search="$search"
        :active="$search || $type"
        :clear="route('student.chapters.guides', ['courseId' => $courseId, 'chapterId' => $chapterId])">

        <x-chapter-filter.select name="type">
            <option value="">All kinds</option>
            @foreach(\App\Models\Guide::TYPE_LABELS as $value => $label)
                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
            @endforeach
        </x-chapter-filter.select>
    </x-chapter-filter>
</div>

{{-- Guides Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @forelse($guides as $guide)
        @php
            $read = $state[$guide['id']]['completed'] ?? false;
            // Written out in full so the classes survive a Tailwind build.
            $badge = $guide['type'] === 'atp_guides'
                ? 'bg-tertiary-container/20 text-tertiary border-tertiary/20'
                : 'bg-primary-container/20 text-primary border-primary/20';
        @endphp

        <article class="glass-card rounded-2xl p-6 flex flex-col h-full group">
            <div class="flex justify-between items-start mb-4 gap-2">
                <div class="flex gap-2 flex-wrap min-w-0">
                    <span class="{{ $badge }} text-xs font-bold px-3 py-1 rounded-full border">{{ $guide['type_label'] }}</span>
                    @if($guide['topic'])
                        <span class="px-2.5 py-1 bg-surface-container-highest text-on-surface-variant rounded text-xs font-bold tracking-wider uppercase truncate max-w-[10rem]">{{ $guide['topic'] }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    @if($read)
                        <span class="inline-flex items-center gap-1 text-tertiary text-xs font-semibold" title="You have read this">
                            <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1;">check_circle</span>
                        </span>
                    @endif
                    <x-save-button type="guide" :uuid="$guide['uuid']" class="text-outline hover:text-primary" />
                </div>
            </div>

            <h3 class="text-on-surface font-semibold text-base mb-2 group-hover:text-primary transition-colors">{{ $guide['title'] }}</h3>
            <p class="text-on-surface-variant text-sm mb-6 line-clamp-3 flex-1">{{ $guide['excerpt'] }}</p>

            <div class="pt-4 border-t border-outline-variant/30 flex items-center justify-between mt-auto gap-2">
                <span class="text-on-surface-variant text-xs truncate">{{ $guide['author'] ?? 'Unknown' }}</span>
                <a href="{{ route('student.chapters.guides.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'guideId' => $guide['uuid']]) }}"
                    class="text-primary text-xs font-semibold flex items-center gap-1 hover:gap-2 transition-all shrink-0">
                    Read <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                </a>
            </div>
        </article>
    @empty
        <div class="col-span-full glass-card rounded-2xl py-20 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 rounded-full bg-primary/5 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary text-4xl">{{ $search || $type ? 'search_off' : 'menu_book' }}</span>
            </div>
            <h3 class="text-on-surface font-semibold text-lg mb-2">
                {{ $search || $type ? 'Nothing matched' : 'No guides yet' }}
            </h3>
            <p class="text-on-surface-variant text-sm max-w-md mb-6">
                @if($search || $type)
                    No guide in this chapter matches those filters.
                @else
                    Nothing has been published for this chapter yet. Check back soon.
                @endif
            </p>
            <a href="{{ $search || $type
                    ? route('student.chapters.guides', ['courseId' => $courseId, 'chapterId' => $chapterId])
                    : route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full inline-flex items-center gap-2">
                {{ $search || $type ? 'Clear filters' : 'Back to the chapter' }}
                <span class="material-symbols-outlined text-sm">{{ $search || $type ? 'restart_alt' : 'arrow_forward' }}</span>
            </a>
        </div>
    @endforelse
</div>

@if($guides->hasPages())
    <div class="mt-10 flex justify-center">
        {{ $guides->links() }}
    </div>
@endif

@endsection
