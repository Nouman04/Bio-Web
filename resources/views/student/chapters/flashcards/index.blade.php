@extends('layouts.student')

@section('title', 'Flashcards – ' . $chapter->title)
@section('meta-description', 'Practice flashcard sets for this chapter')

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
        box-shadow: 0 10px 30px rgba(70,72,212,0.08);
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')

{{-- Breadcrumb + Header --}}
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10 pt-4">
    <div>
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-3 flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', $course) }}" class="hover:text-primary transition-colors">{{ $course->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">{{ $chapter->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface font-semibold">Flashcards</span>
        </nav>
        <h1 class="text-on-background" style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;">Chapter Flashcards</h1>
        <p class="text-on-surface-variant text-sm mt-1">
            @if($search)
                {{ $decks->total() }} {{ Str::plural('set', $decks->total()) }} matched.
            @else
                Master key concepts for this chapter with interactive study sets.
            @endif
        </p>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant" style="font-size:18px;">search</span>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search sets"
                class="bg-surface-container-lowest border border-outline-variant text-on-surface text-sm rounded-lg py-2 pl-10 pr-4 focus:ring-2 focus:ring-primary transition-all w-full sm:w-56">
        </div>
        <button type="submit" class="bg-primary-container text-on-primary-container text-sm font-semibold py-2 px-4 rounded-lg flex items-center gap-2 hover:bg-primary hover:text-on-primary transition-colors">
            <span class="material-symbols-outlined" style="font-size:18px;">filter_list</span> Search
        </button>
        @if($search)
            <a href="{{ route('student.chapters.flashcards', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                class="text-on-surface-variant hover:text-primary text-sm flex items-center gap-1 transition-colors">
                <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span> Clear
            </a>
        @endif
    </form>
</div>

{{-- Flashcard Sets Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($decks as $deck)
        @php
            // Written out in full rather than interpolated, so the classes
            // survive a Tailwind build that scans source for literal names.
            $accents = [
                ['bg-primary-container/20 text-primary border-primary/20', 'bg-primary/5 group-hover:bg-primary/10'],
                ['bg-secondary-container/20 text-secondary border-secondary/20', 'bg-secondary/5 group-hover:bg-secondary/10'],
                ['bg-tertiary-container/20 text-tertiary border-tertiary/20', 'bg-tertiary/5 group-hover:bg-tertiary/10'],
            ];
            [$badge, $blob] = $accents[$loop->index % count($accents)];
            $cards = $deck->assessments_count;
        @endphp

        <div class="glass-card rounded-xl p-6 flex flex-col h-full relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-32 h-32 {{ $blob }} rounded-full blur-2xl transition-colors"></div>

            <div class="flex justify-between items-start mb-4 relative z-10 gap-2">
                <div class="{{ $badge }} text-xs font-bold px-3 py-1 rounded-full border">
                    CH{{ $chapter->chapter_number }}.{{ $decks->firstItem() + $loop->index }}
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    {{-- What the deck was built from, when it came from something. --}}
                    @if($deck->flashcardable)
                        <span class="text-on-surface-variant text-xs truncate max-w-[8rem]" title="{{ $deck->source_label }}">
                            from {{ Str::headline(class_basename($deck->flashcardable_type)) }}
                        </span>
                    @endif
                    <x-save-button :record="$deck" class="text-outline-variant hover:text-primary" />
                </div>
            </div>

            <h3 class="text-on-surface font-semibold text-base mb-2 relative z-10">{{ $deck->title ?: 'Untitled set' }}</h3>
            <p class="text-on-surface-variant text-sm mb-6 flex-1 relative z-10">
                {{ $deck->source_label ? Str::limit($deck->source_label, 110) : 'A study set for this chapter.' }}
            </p>

            <div class="flex items-center justify-between mt-auto relative z-10 gap-3">
                <div class="flex items-center gap-2 text-on-surface-variant">
                    <span class="material-symbols-outlined text-xl">style</span>
                    <span class="text-sm font-semibold">{{ $cards }} {{ Str::plural('Card', $cards) }}</span>
                </div>
                @if($cards > 0)
                    <a href="{{ route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => $deck->uuid]) }}"
                        class="bg-transparent border border-primary text-primary hover:bg-primary hover:text-on-primary px-4 py-2 rounded-full text-xs font-semibold transition-colors inline-flex items-center gap-2">
                        Practice Now <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                @else
                    <span class="border border-outline-variant text-on-surface-variant px-4 py-2 rounded-full text-xs font-semibold inline-flex items-center gap-2">
                        Empty set
                    </span>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-full glass-card rounded-xl py-20 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 rounded-full bg-primary/5 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary text-4xl">{{ $search ? 'search_off' : 'style' }}</span>
            </div>
            <h3 class="text-on-surface font-semibold text-lg mb-2">
                {{ $search ? 'Nothing matched' : 'No flashcard sets yet' }}
            </h3>
            <p class="text-on-surface-variant text-sm max-w-md mb-6">
                @if($search)
                    No set in this chapter matches that search.
                @else
                    Nothing has been published for this chapter yet. Check back soon.
                @endif
            </p>
            <a href="{{ $search
                    ? route('student.chapters.flashcards', ['courseId' => $courseId, 'chapterId' => $chapterId])
                    : route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full inline-flex items-center gap-2">
                {{ $search ? 'Clear search' : 'Back to the chapter' }}
                <span class="material-symbols-outlined text-sm">{{ $search ? 'restart_alt' : 'arrow_forward' }}</span>
            </a>
        </div>
    @endforelse
</div>

@if($decks->hasPages())
    <div class="mt-10 flex justify-center">
        {{ $decks->links() }}
    </div>
@endif

@endsection
