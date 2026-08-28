@extends('layouts.student')

@section('title', $summary->title . ' – Summary')

@push('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .glass-hover:hover {
        box-shadow: 0px 10px 30px rgba(0, 19, 48, 0.08);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
</style>
@endpush

@section('content')
<div class="w-full flex flex-col gap-6 pt-4">
    <!-- Header Section -->
    <header class="flex flex-col gap-4 w-full">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', $course) }}" class="hover:text-primary transition-colors">{{ $course->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">{{ $chapter->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.summaries', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Summaries</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface font-semibold truncate max-w-[16rem]">{{ $summary->title }}</span>
        </nav>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="bg-primary/10 text-primary px-2 py-0.5 rounded text-xs uppercase tracking-wider">{{ $chapter->title }}</span>
                    @if($summary->topic)
                        <span class="bg-secondary-container/20 text-secondary px-2 py-0.5 rounded text-xs uppercase tracking-wider">{{ $summary->topic->title }}</span>
                    @endif
                    <span class="text-on-surface-variant text-xs flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:14px;">schedule</span>
                        {{ $summary->reading_minutes }} min read
                    </span>
                    <span class="text-on-surface-variant text-xs">Updated {{ $summary->updated_at?->diffForHumans() }}</span>
                </div>
                <h1 class="text-3xl font-bold text-on-surface">{{ $summary->title ?: 'Untitled summary' }}</h1>
            </div>

            {{-- Only offered when there is genuinely a file behind it. --}}
            @if($summary->attachments->isNotEmpty())
                <div class="flex gap-2 shrink-0">
                    @foreach($summary->attachments as $attachment)
                        <a href="{{ asset('storage/' . $attachment->file_path) }}" download
                            class="flex items-center gap-2 px-3 py-2 rounded-lg bg-surface-container hover:bg-primary/10 hover:text-primary text-on-surface-variant transition-colors group"
                            title="{{ basename($attachment->file_path) }}">
                            <span class="material-symbols-outlined group-hover:scale-110 transition-transform">download</span>
                            <span class="text-sm font-semibold">Attachment</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </header>

    <!-- Two Column Layout for Content and Sidebar -->
    <div class="flex flex-col lg:flex-row gap-6 relative">
        <!-- Left Column: Reading Area -->
        <article class="flex-1 flex flex-col gap-6">
            <section class="glass-panel rounded-xl p-8 bg-surface-container-lowest">
                <h2 class="text-2xl font-semibold text-on-surface mb-6 border-b border-outline-variant/30 pb-4">Detailed Summary</h2>

                {{-- Wrapped so time spent reading counts towards progress, and
                     so the manual tick is available. See ProgressService. --}}
                <x-module-progress :record="$summary">
                    @if(filled($summary->content))
                        <div class="prose prose-slate max-w-none text-base text-on-surface-variant leading-relaxed
                            [&_h1]:text-2xl [&_h1]:font-semibold [&_h1]:text-on-surface [&_h1]:mt-8 [&_h1]:mb-4
                            [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:text-on-surface [&_h2]:mt-8 [&_h2]:mb-4
                            [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:text-on-surface [&_h3]:mt-6 [&_h3]:mb-3
                            [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mb-1
                            [&_blockquote]:my-8 [&_blockquote]:p-4 [&_blockquote]:bg-surface-container-low
                            [&_blockquote]:rounded-lg [&_blockquote]:border-l-4 [&_blockquote]:border-primary
                            [&_blockquote]:italic [&_img]:rounded-lg [&_a]:text-primary">
                            {!! $summary->content !!}
                        </div>
                    @else
                        <p class="text-on-surface-variant text-sm">This summary has no content yet.</p>
                    @endif
                </x-module-progress>
            </section>

            {{-- Move through the chapter's summaries without going back to the list. --}}
            @if($previous || $next)
                <nav class="flex items-center justify-between gap-4">
                    @if($previous)
                        <a href="{{ route('student.chapters.summaries.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'summaryId' => $previous->uuid]) }}"
                            class="glass-panel rounded-xl p-4 flex items-center gap-3 flex-1 min-w-0 hover:border-primary/40 transition-colors group">
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
                        <a href="{{ route('student.chapters.summaries.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'summaryId' => $next->uuid]) }}"
                            class="glass-panel rounded-xl p-4 flex items-center gap-3 flex-1 min-w-0 justify-end text-right hover:border-primary/40 transition-colors group">
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

        <!-- Right Column: Sidebar Actions & Navigation -->
        <aside class="w-full lg:w-[320px] flex flex-col gap-4 lg:sticky lg:top-[100px] self-start">

            {{-- Decks built from this summary, if any were made. --}}
            @if($summary->flashcards->isNotEmpty())
                <div class="glass-panel rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-on-surface mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary">style</span>
                        Decks from this summary
                    </h3>
                    <div class="space-y-2">
                        @foreach($summary->flashcards as $deck)
                            <a href="{{ route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => $deck->uuid]) }}"
                                class="flex items-center justify-between p-3 rounded-lg bg-surface-container-lowest border border-outline-variant/20 hover:border-primary/50 transition-all group gap-2">
                                <span class="text-sm font-semibold text-on-surface group-hover:text-primary transition-colors truncate">{{ $deck->title }}</span>
                                <span class="material-symbols-outlined text-on-surface-variant text-[16px] group-hover:translate-x-1 transition-transform shrink-0">arrow_forward</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Next Steps -->
            <div class="glass-panel rounded-xl p-5 bg-gradient-to-br from-primary/5 to-transparent">
                <h3 class="text-sm font-semibold text-on-surface mb-4">Next Steps</h3>
                <div class="space-y-3">
                    @if($quiz)
                        <a href="{{ route('student.chapters.quizzes.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'quizId' => $quiz->uuid]) }}"
                            class="flex items-center justify-between p-3 rounded-lg bg-white shadow-sm border border-outline-variant/20 hover:border-primary/50 hover:shadow-md transition-all group gap-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                    <span class="material-symbols-outlined text-[18px]">quiz</span>
                                </div>
                                <div class="min-w-0">
                                    <span class="block text-sm font-semibold text-on-surface group-hover:text-primary transition-colors truncate">{{ $quiz->title }}</span>
                                    <span class="block text-on-surface-variant text-xs">{{ $quiz->questions_count }} {{ Str::plural('question', $quiz->questions_count) }}</span>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-on-surface-variant text-[16px] group-hover:translate-x-1 transition-transform shrink-0">arrow_forward</span>
                        </a>
                    @endif

                    <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                        class="flex items-center justify-between p-3 rounded-lg bg-white shadow-sm border border-outline-variant/20 hover:border-primary/50 hover:shadow-md transition-all group gap-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                                <span class="material-symbols-outlined text-[18px]">dashboard</span>
                            </div>
                            <span class="text-sm font-semibold text-on-surface group-hover:text-primary transition-colors">Back to the chapter</span>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant text-[16px] group-hover:translate-x-1 transition-transform shrink-0">arrow_forward</span>
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
