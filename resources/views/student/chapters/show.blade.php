@extends('layouts.student')

@section('title', 'Chapter Dashboard – ' . $chapter->title)
@section('meta-description', 'Study video, notes, flashcards and assessments for ' . $chapter->title)

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .glass-panel:hover {
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
    }
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .resource-tab { transition: color 0.2s, border-color 0.2s; }
    .resource-tab.active { color: #4648d4; border-bottom-color: #4648d4; }
</style>
@endpush

@php
    $percent = (float) $progress['progress'];
    $state = $percent >= 100 && $progress['total_weight'] > 0
        ? ['Completed', 'bg-tertiary/10 text-tertiary']
        : ($percent > 0 ? ['In Progress', 'bg-primary/10 text-primary'] : ['Not started', 'bg-surface-container-highest text-on-surface-variant']);
    $link = fn ($name, $extra = []) => route($name, ['courseId' => $courseId, 'chapterId' => $chapterId] + $extra);
@endphp

@section('content')

<div class="grid grid-cols-12 gap-6 pt-4">

    <!-- ═══════════════════════════════════ LEFT COLUMN (8 cols) ═══════════════════════════════════ -->
    <div class="col-span-12 lg:col-span-8 flex flex-col gap-6">

        <!-- Back breadcrumb -->
        <div>
            <a href="{{ route('student.chapters', ['courseId' => $courseId]) }}"
               class="inline-flex items-center gap-1 text-primary hover:opacity-80 text-xs font-semibold mb-1 transition-opacity">
                <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
                Back to Chapter List
            </a>
        </div>

        <!-- ── Video Player ── -->
        <section class="glass-panel rounded-xl overflow-hidden shadow-sm flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b border-outline-variant/10 flex justify-between items-center gap-4 bg-surface-container-lowest">
                <div class="min-w-0">
                    <h2 class="text-on-surface truncate" style="font-size:24px;line-height:32px;font-weight:600;">{{ $course->title }}</h2>
                    <p class="text-on-surface-variant text-sm">Chapter {{ $chapter->chapter_number }}: {{ $chapter->title }}</p>
                </div>
                <span class="{{ $state[1] }} text-xs font-semibold px-3 py-1 rounded-full whitespace-nowrap">{{ $state[0] }}</span>
            </div>

            {{-- The real lesson, wrapped so watching it counts towards progress.
                 progress.js reports at 90% watched; see ProgressService. --}}
            @if($featured && $featured->video_url)
                <x-module-progress :record="$featured" class="p-4">
                    @if($featured->is_external)
                        <div class="w-full aspect-video bg-inverse-surface flex flex-col items-center justify-center gap-3 rounded-lg">
                            <span class="material-symbols-outlined text-white/80 text-5xl">play_circle</span>
                            <a href="{{ $featured->video_url }}" target="_blank" rel="noopener"
                                class="bg-primary text-on-primary text-xs font-semibold py-2 px-5 rounded-full">
                                Watch “{{ $featured->title }}”
                            </a>
                            <p class="text-white/60 text-xs">Hosted externally — mark it complete when you are done.</p>
                        </div>
                    @else
                        <video controls preload="metadata" class="w-full aspect-video bg-inverse-surface rounded-lg"
                            src="{{ $featured->video_url }}"></video>
                        <p class="text-on-surface-variant text-xs mt-2">{{ $featured->title }}</p>
                    @endif
                </x-module-progress>
            @else
                <div class="w-full aspect-video bg-inverse-surface flex flex-col items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-white/40 text-5xl">videocam_off</span>
                    <p class="text-white/60 text-sm">No video lesson in this chapter yet.</p>
                </div>
            @endif
        </section>

        <!-- ── Interactive Resources ── -->
        <section class="glass-panel rounded-xl p-6 shadow-sm">
            <h3 class="text-on-surface mb-4" style="font-size:20px;line-height:28px;font-weight:600;">Interactive Resources</h3>

            <!-- Tabs -->
            <div class="flex flex-col sm:flex-row sm:items-center border-b border-outline-variant/20 mb-6 gap-2 sm:gap-0">
                <div class="flex overflow-x-auto hide-scrollbar" id="resourceTabs">
                    @foreach([
                        'studyNotes' => ['Study Notes', $notes->count()],
                        'flashcards' => ['Flashcards', $flashcards->count()],
                        'diagrams' => ['Diagrams', $diagrams->count()],
                        'summaries' => ['Summaries', $summaries->count()],
                        'videos' => ['Videos', $videos->count()],
                        'guides' => ['Guides', $guides->count()],
                    ] as $key => [$label, $count])
                        <button onclick="switchResourceTab(this,'{{ $key }}')"
                                class="resource-tab {{ $loop->first ? 'active' : 'text-on-surface-variant hover:text-on-surface border-transparent' }} font-semibold text-sm border-b-2 pb-2 px-4 whitespace-nowrap shrink-0 transition-colors">
                            {{ $label }}
                            <span class="text-xs font-normal opacity-70">({{ $count }})</span>
                        </button>
                    @endforeach
                </div>
                <span id="viewAllLink" class="sm:ml-auto sm:pl-4 flex items-center justify-end pb-2 shrink-0">
                    <a id="viewAllAnchor" href="{{ $link('student.chapters.notes') }}"
                       class="text-primary text-xs font-semibold hover:underline flex items-center gap-1 whitespace-nowrap">
                        View All <span class="material-symbols-outlined" style="font-size:14px;">open_in_new</span>
                    </a>
                </span>
            </div>

            <!-- Study Notes Panel -->
            <div id="panel-studyNotes" class="space-y-3">
                @forelse($notes as $note)
                    <a href="{{ $link('student.chapters.notes.show', ['noteId' => $note->uuid]) }}"
                        class="block p-4 bg-surface-container-lowest rounded-lg border border-surface-container-highest hover:border-primary/30 transition-colors group">
                        <div class="flex justify-between items-start mb-2 gap-3">
                            <h4 class="text-on-surface text-sm font-semibold group-hover:text-primary transition-colors">{{ $note->title }}</h4>
                            <span class="material-symbols-outlined text-outline-variant group-hover:text-primary shrink-0" style="font-size:18px;">open_in_new</span>
                        </div>
                        <p class="text-on-surface-variant text-xs line-clamp-2">{{ $note->excerpt ?: 'No content yet.' }}</p>
                    </a>
                @empty
                    <p class="text-on-surface-variant text-sm py-6 text-center">No study notes in this chapter yet.</p>
                @endforelse
            </div>

            <!-- Flashcards Panel -->
            <div id="panel-flashcards" class="hidden">
                @if($flashcards->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($flashcards as $deck)
                            <a href="{{ $link('student.chapters.flashcards.show', ['flashcardId' => $deck->uuid]) }}"
                                class="block border border-outline-variant/30 rounded-xl p-5 hover:border-primary/40 hover:shadow-sm transition-all bg-surface-container-lowest">
                                <h4 class="text-on-surface text-sm font-semibold mb-1">{{ $deck->title }}</h4>
                                <p class="text-on-surface-variant text-xs">{{ $deck->assessments_count }} {{ Str::plural('Card', $deck->assessments_count) }}</p>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-on-surface-variant text-sm py-6 text-center">No flashcard decks in this chapter yet.</p>
                @endif
            </div>

            <!-- Diagrams Panel -->
            <div id="panel-diagrams" class="hidden">
                @if($diagrams->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($diagrams as $diagram)
                            <a href="{{ $link('student.chapters.diagrams.show', ['diagramId' => $diagram->uuid]) }}"
                                class="block border border-outline-variant/30 rounded-xl overflow-hidden hover:border-primary/40 transition-all bg-surface-container-lowest">
                                <div class="h-28 bg-surface-dim flex items-center justify-center">
                                    <span class="material-symbols-outlined text-3xl text-outline-variant">account_tree</span>
                                </div>
                                <div class="p-3"><h4 class="text-on-surface text-xs font-semibold line-clamp-2">{{ $diagram->title }}</h4></div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-on-surface-variant text-sm py-6 text-center">No diagrams in this chapter yet.</p>
                @endif
            </div>

            <!-- Summaries Panel -->
            <div id="panel-summaries" class="hidden">
                <div class="space-y-3">
                    @forelse($summaries as $summary)
                        <a href="{{ $link('student.chapters.summaries.show', ['summaryId' => $summary->uuid]) }}"
                            class="flex items-center gap-4 p-4 bg-surface-container-lowest rounded-lg border border-outline-variant/20 hover:border-primary/30 transition-colors">
                            <span class="material-symbols-outlined text-primary text-xl">description</span>
                            <div class="min-w-0">
                                <h4 class="text-on-surface text-sm font-semibold truncate">{{ $summary->title }}</h4>
                                <p class="text-on-surface-variant text-xs">Updated {{ $summary->updated_at?->format('M Y') }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-on-surface-variant text-sm py-6 text-center">No summaries in this chapter yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Videos Panel -->
            <div id="panel-videos" class="hidden">
                @if($videos->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($videos as $video)
                            <a href="{{ $link('student.chapters.videos.show', ['videoId' => $video->uuid]) }}"
                                class="border border-outline-variant/30 rounded-xl overflow-hidden hover:border-primary/40 transition-all bg-surface-container-lowest flex items-center gap-3 p-3">
                                <div class="w-16 h-16 bg-surface-container rounded-md flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-outline-variant text-2xl">play_circle</span>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-on-surface text-sm font-semibold line-clamp-1">{{ $video->title }}</h4>
                                    <p class="text-on-surface-variant text-xs">{{ $video->is_external ? 'External' : 'Watch now' }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-on-surface-variant text-sm py-6 text-center">No video lessons in this chapter yet.</p>
                @endif
            </div>

            <!-- Guides Panel -->
            <div id="panel-guides" class="hidden">
                <div class="space-y-3">
                    @forelse($guides as $guide)
                        <a href="{{ $link('student.chapters.guides.show', ['guideId' => $guide->uuid]) }}"
                            class="flex items-center gap-4 p-4 bg-surface-container-lowest rounded-lg border border-outline-variant/20 hover:border-primary/30 transition-colors group">
                            <span class="material-symbols-outlined text-primary text-xl shrink-0">menu_book</span>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-on-surface text-sm font-semibold truncate group-hover:text-primary transition-colors">{{ $guide->title }}</h4>
                                <p class="text-on-surface-variant text-xs">{{ $guide->type_label }}</p>
                            </div>
                            <span class="material-symbols-outlined text-outline-variant group-hover:text-primary shrink-0" style="font-size:18px;">open_in_new</span>
                        </a>
                    @empty
                        <p class="text-on-surface-variant text-sm py-6 text-center">No guides in this chapter yet.</p>
                    @endforelse
                </div>
            </div>

        </section>
    </div>

    <!-- ═══════════════════════════════════ RIGHT COLUMN (4 cols) ═══════════════════════════════════ -->
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">

        <!-- ── Chapter Progress ── -->
        <section class="glass-panel rounded-xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-on-surface" style="font-size:20px;line-height:28px;font-weight:600;">Chapter Progress</h3>
                <span class="text-primary text-sm font-bold">{{ round($percent) }}%</span>
            </div>
            <div class="w-full h-2 bg-surface-container-highest rounded-full overflow-hidden">
                <div class="h-full bg-primary rounded-full transition-all duration-500"
                    data-progress-chapter style="width: {{ $percent }}%"></div>
            </div>
            <p class="text-on-surface-variant text-xs mt-2">
                @if($progress['total_weight'] > 0)
                    {{ $progress['completed_weight'] }} of {{ $progress['total_weight'] }} items completed
                @else
                    Nothing to track in this chapter yet
                @endif
            </p>
        </section>

        <!-- ── Chapter Assessment ── -->
        <section class="glass-panel rounded-xl p-6 shadow-sm border-t-4 border-t-tertiary-container relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-tertiary/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-5 relative z-10">
                <div class="w-10 h-10 rounded-lg bg-tertiary/10 flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined">quiz</span>
                </div>
                <h3 class="text-on-surface flex-1 min-w-0" style="font-size:20px;line-height:28px;font-weight:600;">Chapter Assessment</h3>
                {{-- Only the first few fit here; the rest are on the list page. --}}
                <a href="{{ $link('student.chapters.quizzes') }}"
                    class="text-tertiary text-xs font-semibold flex items-center gap-1 hover:gap-2 transition-all shrink-0">
                    View all
                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                </a>
            </div>
            <div class="space-y-4 relative z-10">
                @forelse($quizzes as $quiz)
                    <div class="bg-surface-container-lowest p-4 rounded-lg border border-surface-container-highest">
                        <h4 class="text-on-surface text-sm font-semibold mb-1">{{ $quiz->title }}</h4>
                        <p class="text-on-surface-variant text-xs mb-4">
                            {{ $quiz->questions_count }} {{ Str::plural('question', $quiz->questions_count) }}
                            @if($quiz->duration) • {{ $quiz->duration }} min @endif
                            @if($quiz->passing_score !== null) • pass at {{ $quiz->passing_score }} @endif
                        </p>
                        <a href="{{ $link('student.chapters.quizzes.show', ['quizId' => $quiz->uuid]) }}"
                            class="w-full bg-surface-container hover:bg-surface-variant text-on-surface text-xs font-semibold py-2 rounded-full transition-colors flex items-center justify-center gap-2">
                            Start Quiz
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </div>
                @empty
                    <p class="text-on-surface-variant text-sm py-2">No assessments set for this chapter yet.</p>
                @endforelse
            </div>
        </section>

        <!-- ── Course Outline Timeline ── -->
        <section class="glass-panel rounded-xl p-6 shadow-sm">
            <h3 class="text-on-surface mb-5" style="font-size:20px;line-height:28px;font-weight:600;">Course Outline</h3>
            <div class="relative pl-6 border-l-2 border-surface-container-highest space-y-6">

                @if($previous)
                    <div class="relative">
                        <div class="absolute -left-[29px] top-1 w-4 h-4 rounded-full bg-tertiary-container border-4 border-surface-container-lowest"></div>
                        <p class="text-tertiary text-xs font-semibold mb-1">Previous</p>
                        <h4 class="text-on-surface text-sm">Chapter {{ $previous->chapter_number }}: {{ $previous->title }}</h4>
                    </div>
                @endif

                <div class="relative">
                    <div class="absolute -left-[31px] top-1 w-5 h-5 rounded-full bg-primary border-4 border-surface-container-lowest ring-2 ring-primary/30"></div>
                    <p class="text-primary text-xs font-semibold mb-1">Current</p>
                    <h4 class="text-on-surface text-sm font-semibold">Chapter {{ $chapter->chapter_number }}: {{ $chapter->title }}</h4>
                </div>

                @if($next)
                    <div class="relative">
                        <div class="absolute -left-[29px] top-1 w-4 h-4 rounded-full bg-surface-container-highest border-4 border-surface-container-lowest"></div>
                        <p class="text-on-surface-variant text-xs font-semibold mb-1">Up Next</p>
                        <h4 class="text-on-surface-variant text-sm">Chapter {{ $next->chapter_number }}: {{ $next->title }}</h4>
                    </div>
                @endif

            </div>

            <!-- Navigate Chapters -->
            <div class="flex items-center justify-between mt-8 pt-5 border-t border-outline-variant/20">
                @if($previous)
                    <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $previous->uuid]) }}"
                       class="flex items-center gap-2 text-primary text-xs font-semibold hover:underline">
                        <span class="material-symbols-outlined text-sm">arrow_back</span> Previous
                    </a>
                @else
                    <span></span>
                @endif
                @if($next)
                    <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $next->uuid]) }}"
                       class="flex items-center gap-2 text-primary text-xs font-semibold hover:underline">
                        Next <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                @endif
            </div>
        </section>

    </div>
</div>

@endsection

@push('scripts')
<script>
    const panels = ['studyNotes', 'flashcards', 'diagrams', 'summaries', 'videos', 'guides'];

    const viewAllUrls = {
        studyNotes:  '{{ $link('student.chapters.notes') }}',
        flashcards:  '{{ $link('student.chapters.flashcards') }}',
        diagrams:    '{{ $link('student.chapters.diagrams') }}',
        summaries:   '{{ $link('student.chapters.summaries') }}',
        videos:      '{{ $link('student.chapters.videos') }}',
        guides:      '{{ $link('student.chapters.guides') }}',
    };

    function switchResourceTab(btn, id) {
        panels.forEach(p => {
            const el = document.getElementById('panel-' + p);
            if (el) el.classList.add('hidden');
        });
        document.querySelectorAll('.resource-tab').forEach(t => {
            t.classList.remove('active', 'text-primary');
            t.classList.add('text-on-surface-variant', 'border-transparent');
        });
        const panel = document.getElementById('panel-' + id);
        if (panel) panel.classList.remove('hidden');
        btn.classList.add('active');
        btn.classList.remove('text-on-surface-variant', 'border-transparent');

        const anchor = document.getElementById('viewAllAnchor');
        if (anchor && viewAllUrls[id]) anchor.href = viewAllUrls[id];
    }
</script>
@endpush
