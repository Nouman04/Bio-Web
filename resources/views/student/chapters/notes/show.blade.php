@extends('layouts.student')

@section('title', $note->title . ' – Study Note')

@push('styles')
<style>
    .soft-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .hover-lift {
        transition: all 0.3s ease;
    }
    .hover-lift:hover {
        box-shadow: 0px 10px 30px rgba(0, 19, 48, 0.08);
    }
    /* Anchored headings should not hide under the sticky header when jumped to. */
    .note-body :is(h1, h2, h3) {
        scroll-margin-top: 110px;
    }
</style>
@endpush

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 pt-4">
    <!-- Central Reading Area -->
    <article class="xl:col-span-8 2xl:col-span-9 flex flex-col gap-6">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', $course) }}" class="hover:text-primary transition-colors">{{ $course->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">{{ $chapter->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.notes', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Study Notes</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface truncate max-w-[14rem]">{{ $note->title }}</span>
        </nav>

        <!-- Header Section -->
        <header class="flex flex-col gap-4 pb-4 border-b border-on-surface/5">
            <div class="flex justify-between items-start gap-4 flex-wrap">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="inline-block px-2 py-1 bg-secondary-container/20 text-secondary text-xs rounded">{{ $chapter->title }}</span>
                        <span class="inline-block px-2 py-1 bg-primary-container/20 text-primary text-xs rounded">{{ $note->type_label }}</span>
                        @if($note->topic)
                            <span class="inline-block px-2 py-1 bg-surface-container-highest text-on-surface-variant text-xs rounded">{{ $note->topic->title }}</span>
                        @endif
                    </div>
                    <h1 class="text-3xl text-on-surface font-bold tracking-tight">{{ $note->title ?: 'Untitled note' }}</h1>
                    <p class="text-sm text-on-surface-variant mt-2">Last edited {{ $note->updated_at?->diffForHumans() }}</p>
                </div>
            </div>

            {{-- Only offered when there is genuinely a file behind it. --}}
            @if($note->attachments->isNotEmpty())
                <div class="flex flex-wrap items-center gap-3 pt-4">
                    @foreach($note->attachments as $attachment)
                        <a href="{{ asset('storage/' . $attachment->file_path) }}" download
                            class="bg-surface border border-outline-variant text-on-surface text-sm py-2 px-4 rounded-full hover:bg-surface-container-low transition-colors flex items-center gap-2"
                            title="{{ basename($attachment->file_path) }}">
                            <span class="material-symbols-outlined text-[18px]">download</span>
                            {{ Str::limit(basename($attachment->file_path), 28) }}
                        </a>
                    @endforeach
                </div>
            @endif
        </header>

        {{-- Wrapped so time spent reading counts towards progress, and so the
             manual tick is available. See ProgressService. --}}
        <div class="soft-card p-6 md:p-10 rounded-xl hover-lift">
            <x-module-progress :record="$note">
                @if(filled($content))
                    <div class="note-body space-y-6 text-base text-on-surface leading-relaxed
                        [&_h1]:text-2xl [&_h1]:font-semibold [&_h1]:mt-8 [&_h1]:mb-4
                        [&_h2]:text-2xl [&_h2]:font-semibold [&_h2]:mt-8 [&_h2]:mb-4
                        [&_h3]:text-xl [&_h3]:font-semibold [&_h3]:mt-6 [&_h3]:mb-3
                        [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6
                        [&_li]:mb-2 [&_li]:text-on-surface-variant
                        [&_code]:bg-surface-container-high [&_code]:px-1 [&_code]:py-0.5 [&_code]:rounded [&_code]:text-sm [&_code]:text-primary
                        [&_blockquote]:border-l-4 [&_blockquote]:border-primary [&_blockquote]:pl-4 [&_blockquote]:italic
                        [&_img]:rounded-lg [&_a]:text-primary">
                        {!! $content !!}
                    </div>
                @else
                    <p class="text-on-surface-variant text-sm">This note has no content yet.</p>
                @endif
            </x-module-progress>
        </div>

        {{-- Move through the chapter's notes without going back to the list. --}}
        @if($previous || $next)
            <nav class="flex items-center justify-between gap-4">
                @if($previous)
                    <a href="{{ route('student.chapters.notes.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'noteId' => $previous->uuid]) }}"
                        class="soft-card rounded-xl p-4 flex items-center gap-3 flex-1 min-w-0 hover-lift group">
                        <span class="material-symbols-outlined text-primary group-hover:-translate-x-1 transition-transform">arrow_back</span>
                        <span class="min-w-0">
                            <span class="block text-on-surface-variant text-xs">Previous</span>
                            <span class="block text-on-surface text-sm font-semibold truncate">{{ $previous->title }}</span>
                        </span>
                    </a>
                @else
                    <span class="flex-1"></span>
                @endif

                @if($next)
                    <a href="{{ route('student.chapters.notes.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'noteId' => $next->uuid]) }}"
                        class="soft-card rounded-xl p-4 flex items-center gap-3 flex-1 min-w-0 justify-end text-right hover-lift group">
                        <span class="min-w-0">
                            <span class="block text-on-surface-variant text-xs">Next</span>
                            <span class="block text-on-surface text-sm font-semibold truncate">{{ $next->title }}</span>
                        </span>
                        <span class="material-symbols-outlined text-primary group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </a>
                @endif
            </nav>
        @endif
    </article>

    <!-- Sidebar (Right) -->
    <aside class="xl:col-span-4 2xl:col-span-3 flex flex-col gap-6">
        {{-- Built from the note's own headings, so every entry jumps somewhere
             real. Hidden entirely when the note has no headings. --}}
        @if(count($headings) > 0)
            <div class="soft-card p-6 rounded-xl hover-lift xl:sticky xl:top-[100px]">
                <h3 class="text-xl font-semibold text-on-surface mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">list</span>
                    Quick Navigation
                </h3>
                <ul class="space-y-3 text-sm">
                    @foreach($headings as $heading)
                        <li @class(['pl-3' => $heading['level'] === 3])>
                            <a class="text-on-surface-variant hover:text-primary transition-colors flex items-center gap-2 border-l-2 border-primary/30 hover:border-primary pl-2"
                                href="#{{ $heading['id'] }}">
                                {{ $heading['text'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($note->summary)
            <div class="soft-card p-6 rounded-xl hover-lift">
                <h3 class="text-sm font-semibold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">description</span>
                    Written from
                </h3>
                <a href="{{ route('student.chapters.summaries.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'summaryId' => $note->summary->uuid]) }}"
                    class="flex items-center justify-between p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/20 hover:border-primary/50 transition-all group gap-2">
                    <span class="text-sm font-semibold text-on-surface group-hover:text-primary transition-colors truncate">{{ $note->summary->title }}</span>
                    <span class="material-symbols-outlined text-on-surface-variant text-[16px] group-hover:translate-x-1 transition-transform shrink-0">arrow_forward</span>
                </a>
            </div>
        @endif

        @if($note->flashcards->isNotEmpty())
            <div class="soft-card p-6 rounded-xl hover-lift">
                <h3 class="text-sm font-semibold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">style</span>
                    Decks from this note
                </h3>
                <div class="space-y-2">
                    @foreach($note->flashcards as $deck)
                        <a href="{{ route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => $deck->uuid]) }}"
                            class="flex items-center justify-between p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/20 hover:border-primary/50 transition-all group gap-2">
                            <span class="text-sm font-semibold text-on-surface group-hover:text-primary transition-colors truncate">{{ $deck->title }}</span>
                            <span class="material-symbols-outlined text-on-surface-variant text-[16px] group-hover:translate-x-1 transition-transform shrink-0">arrow_forward</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </aside>
</div>
@endsection
