@extends('layouts.student')

@section('title', 'Study Notes – ' . $chapter->title)
@section('meta-description', 'Browse and review all study notes for this chapter')

@push('styles')
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
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 pt-4">
    <div>
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-3 flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="text-outline-variant">&rsaquo;</span>
            <a href="{{ route('student.courses.show', $course) }}" class="hover:text-primary transition-colors">{{ $course->title }}</a>
            <span class="text-outline-variant">&rsaquo;</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">{{ $chapter->title }}</a>
            <span class="text-outline-variant">&rsaquo;</span>
            <span class="text-on-surface font-semibold">Study Notes</span>
        </nav>
        <h1 class="text-on-background" style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;">Study Notes</h1>
        <p class="text-on-surface-variant text-sm mt-1">
            @if($search || $type)
                <span class="font-semibold text-primary">{{ $notes->total() }}</span>
                {{ Str::plural('note', $notes->total()) }} matched.
            @else
                There {{ $notes->total() === 1 ? 'is' : 'are' }}
                <span class="font-semibold text-primary">{{ $notes->total() }}</span>
                {{ Str::plural('note', $notes->total()) }} in this chapter.
            @endif
        </p>
    </div>

    {{-- Search and kind, both handled on the server. --}}
    <x-chapter-filter placeholder="Search notes"
        :search="$search"
        :active="$search || $type"
        :clear="route('student.chapters.notes', ['courseId' => $courseId, 'chapterId' => $chapterId])">

        <x-chapter-filter.select name="type">
            <option value="">All kinds</option>
            @foreach(\App\Models\Note::TYPE_LABELS as $value => $label)
                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
            @endforeach
        </x-chapter-filter.select>
    </x-chapter-filter>
</div>

{{-- Notes Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @forelse($notes as $note)
        @php
            // Read state comes from the progress tables, not from the note.
            $read = $state[$note['id']]['completed'] ?? false;
            $accents = [
                'exam_notes' => ['bg-secondary-container/20 text-secondary border-secondary/20', 'bg-primary/5'],
                'summary' => ['bg-tertiary-container/20 text-tertiary border-tertiary/20', 'bg-tertiary/5'],
                'flashcards' => ['bg-primary-container/20 text-primary border-primary/20', 'bg-secondary/5'],
            ];
            [$badge, $blob] = $accents[$note['type']] ?? $accents['exam_notes'];
        @endphp

        <div class="glass-card rounded-2xl p-6 flex flex-col h-full relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 {{ $blob }} rounded-bl-full -z-10 transition-transform group-hover:scale-110"></div>

            <div class="flex justify-between items-start mb-4 gap-2">
                <span class="{{ $badge }} text-xs font-semibold px-3 py-1 rounded-full border">{{ $note['type_label'] }}</span>
                <div class="flex items-center gap-2 shrink-0">
                    @if($read)
                        <span class="inline-flex items-center gap-1 text-tertiary text-xs font-semibold" title="You have read this">
                            <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1;">check_circle</span>
                            Read
                        </span>
                    @endif
                    <x-save-button type="note" :uuid="$note['uuid']" class="text-outline hover:text-primary" />
                </div>
            </div>

            <h3 class="text-on-surface font-semibold text-base mb-1 line-clamp-2">{{ $note['title'] }}</h3>
            <p class="text-on-surface-variant text-xs mb-4 uppercase tracking-wide font-medium">
                Chapter {{ $chapter->chapter_number }}@if($note['topic']): {{ $note['topic'] }}@endif
            </p>

            <div class="bg-surface-bright border border-outline-variant/20 rounded-xl p-4 mb-6 flex-1 shadow-inner">
                <p class="text-on-surface-variant text-xs line-clamp-4">{{ $note['excerpt'] }}</p>
            </div>

            <div class="flex justify-between items-center pt-2 border-t border-outline-variant/20">
                <span class="text-on-surface-variant text-xs flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:16px;">schedule</span>
                    {{ $note['updated_human'] ?? '—' }}
                </span>
                <a href="{{ route('student.chapters.notes.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'noteId' => $note['uuid']]) }}"
                    class="text-primary text-xs font-semibold flex items-center gap-1 group-hover:gap-2 transition-all">
                    View Note <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full glass-card rounded-2xl py-20 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 rounded-full bg-primary/5 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary text-4xl">
                    {{ $search || $type ? 'search_off' : 'sticky_note_2' }}
                </span>
            </div>
            <h3 class="text-on-surface font-semibold text-lg mb-2">
                {{ $search || $type ? 'Nothing matched' : 'No study notes yet' }}
            </h3>
            <p class="text-on-surface-variant text-sm max-w-md mb-6">
                @if($search || $type)
                    No note in this chapter matches those filters.
                @else
                    Nothing has been published for this chapter yet. Check back soon.
                @endif
            </p>
            @if($search || $type)
                <a href="{{ route('student.chapters.notes', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                    class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full inline-flex items-center gap-2">
                    Clear filters <span class="material-symbols-outlined text-sm">restart_alt</span>
                </a>
            @else
                <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                    class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full inline-flex items-center gap-2">
                    Back to the chapter <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            @endif
        </div>
    @endforelse
</div>

@if($notes->hasPages())
    <div class="flex justify-center pt-8 pb-4">
        {{ $notes->links() }}
    </div>
@endif

@endsection
