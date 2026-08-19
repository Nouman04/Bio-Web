@extends('public.layouts.app')

@section('title', $chapter->title . ' | Lumina LMS')

@include('public.catalog._styles')

@section('content')
<main class="max-w-container-max mx-auto px-md md:px-lg py-lg md:py-xl min-h-[calc(100vh-160px)]">
<!-- Breadcrumbs & Heading Header -->
<header class="mb-xl relative z-10">
@include('public.catalog._breadcrumb', ['crumbs' => [
    'Courses' => route('public.courses'),
    $course->title => route('public.course.chapters', $course),
    'Chapter ' . $chapter->chapter_number => route('public.course.chapter.show', [$course, $chapter]),
]])
<h1 class="font-display-lg text-display-lg text-on-surface">{{ $chapter->title }}</h1>
@if($chapter->excerpt)
<p class="font-body-lg text-body-lg text-on-surface-variant mt-base max-w-3xl">{{ $chapter->excerpt }}</p>
@endif
</header>
<!-- Bento Grid Layout -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-lg relative z-10">
<!-- Study Notes Section (Spans 8 cols on desktop) -->
<section class="md:col-span-8 glass-panel rounded-xl p-md md:p-lg flex flex-col h-full">
<header class="flex items-center justify-between mb-lg">
<div class="flex items-center gap-sm">
<div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container">
<span class="material-symbols-outlined fill">menu_book</span>
</div>
<h2 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface">Study Notes</h2>
</div>
<span class="font-label-sm text-label-sm text-on-surface-variant">{{ $counts['notes'] }} {{ Str::plural('note', $counts['notes']) }}</span>
</header>
<div class="flex-grow space-y-md">
@forelse($topics as $topic)
<a class="block group" href="{{ route('public.course.chapter.notes', [$course, $chapter]) }}">
<div class="glass-panel-dim rounded-lg p-sm flex items-start gap-md transition-all duration-300 hover:shadow-primary-glow hover:border-primary/20 scale-100 active:scale-95">
<div class="w-16 h-16 rounded bg-surface-container-low flex-shrink-0 flex items-center justify-center text-primary">
<span class="material-symbols-outlined">description</span>
</div>
<div class="flex-grow">
<h3 class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors">{{ $chapter->chapter_number }}.{{ $loop->iteration }} {{ $topic->title }}</h3>
<p class="font-body-md text-body-md text-on-surface-variant line-clamp-2 mt-xs">{{ $topic->excerpt ?: 'Open the notes for this topic.' }}</p>
</div>
<span class="material-symbols-outlined text-outline-variant group-hover:text-primary transform group-hover:translate-x-1 transition-all mt-2">arrow_forward</span>
</div>
</a>
@empty
<div class="glass-panel-dim rounded-lg p-md text-center">
<p class="font-body-md text-body-md text-on-surface-variant">No topics have been published for this chapter yet.</p>
</div>
@endforelse
</div>
<div class="mt-lg text-center pt-md border-t border-glass-stroke">
<a href="{{ route('public.course.chapter.notes', [$course, $chapter]) }}"
    class="font-label-md text-label-md text-primary hover:text-primary-container px-lg py-sm rounded-full border border-primary/20 hover:bg-primary/5 transition-all duration-200 inline-flex items-center gap-xs scale-100 active:scale-95">
                        View All Notes
                        <span class="material-symbols-outlined text-[18px]">expand_more</span>
</a>
</div>
</section>
<!-- Flashcards & Assessments Side Column (Spans 4 cols on desktop) -->
<div class="md:col-span-4 flex flex-col gap-lg">
<!-- Flashcards Section -->
<section class="glass-panel rounded-xl p-md flex flex-col min-h-[300px]">
<header class="flex items-center justify-between mb-md">
<div class="flex items-center gap-sm">
<div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container">
<span class="material-symbols-outlined fill text-[20px]">style</span>
</div>
<h2 class="font-headline-md text-headline-md text-on-surface">Flashcards</h2>
</div>
<span class="font-label-sm text-label-sm text-on-surface-variant">{{ $counts['flashcards'] }}</span>
</header>
<div class="flex-grow space-y-sm">
@forelse($chapter->flashcards()->withCount('assessments')->latest('id')->take(3)->get() as $deck)
<a class="group block" href="{{ route('public.course.chapter.flashcards.show', [$course, $chapter, $deck]) }}">
<div class="bg-surface-bright rounded-lg p-sm border border-glass-stroke flex justify-between items-center hover:border-primary/30 hover:shadow-sm transition-all">
<div>
<h4 class="font-label-md text-label-md text-on-surface group-hover:text-primary transition-colors">{{ $deck->title }}</h4>
<p class="text-xs text-on-surface-variant mt-1">{{ $deck->assessments_count }} {{ Str::plural('Card', $deck->assessments_count) }}</p>
</div>
<span class="material-symbols-outlined text-outline-variant group-hover:text-primary text-[20px]">play_circle</span>
</div>
</a>
@empty
<p class="font-body-md text-body-md text-on-surface-variant">No sets yet.</p>
@endforelse
</div>
<a href="{{ route('public.course.chapter.flashcards', [$course, $chapter]) }}"
    class="mt-md font-label-md text-label-md text-secondary hover:text-on-surface w-full py-2 text-center transition-colors block">
                        View All Sets
                    </a>
</section>
<!-- Practice Assessments Section -->
<section class="glass-panel rounded-xl p-md flex flex-col min-h-[300px]">
<header class="flex items-center justify-between mb-md">
<div class="flex items-center gap-sm">
<div class="w-8 h-8 rounded-full bg-tertiary-container/20 flex items-center justify-center text-tertiary">
<span class="material-symbols-outlined fill text-[20px]">quiz</span>
</div>
<h2 class="font-headline-md text-headline-md text-on-surface">MCQ Quizzes</h2>
</div>
<span class="font-label-sm text-label-sm text-on-surface-variant">{{ $counts['mcqs'] }}</span>
</header>
<div class="flex-grow space-y-sm">
@forelse($mcqPreview as $quiz)
<a class="group block" href="{{ route('public.course.chapter.mcqs.show', [$course, $chapter, $quiz]) }}">
<div class="bg-surface-bright rounded-lg p-sm border border-glass-stroke flex items-start gap-3 hover:border-primary/30 hover:shadow-sm transition-all">
<div class="mt-1">
<span class="material-symbols-outlined text-primary text-[20px]">radio_button_checked</span>
</div>
<div>
<h4 class="font-label-md text-label-md text-on-surface group-hover:text-primary transition-colors">{{ $quiz->title }}</h4>
<p class="text-xs text-on-surface-variant mt-1">{{ $quiz->questions_count }} {{ Str::plural('Question', $quiz->questions_count) }}@if($quiz->duration) • {{ $quiz->duration }} Mins @endif</p>
</div>
</div>
</a>
@empty
<p class="font-body-md text-body-md text-on-surface-variant">No quizzes yet.</p>
@endforelse
</div>
<a href="{{ route('public.course.chapter.mcqs', [$course, $chapter]) }}"
    class="mt-md font-label-md text-label-md text-secondary hover:text-on-surface w-full py-2 text-center transition-colors block">View All Quizzes</a>
</section>
<!-- Theory Practice Section -->
<section class="glass-panel rounded-xl p-md flex flex-col min-h-[300px]">
<header class="flex items-center justify-between mb-md">
<div class="flex items-center gap-sm">
<div class="w-8 h-8 rounded-full bg-tertiary-container/20 flex items-center justify-center text-tertiary">
<span class="material-symbols-outlined fill text-[20px]">edit_document</span>
</div>
<h2 class="font-headline-md text-headline-md text-on-surface">Theory Practice</h2>
</div>
<span class="font-label-sm text-label-sm text-on-surface-variant">{{ $counts['theory'] }}</span>
</header>
<div class="flex-grow space-y-sm">
@forelse($theoryPreview as $quiz)
<a class="group block" href="{{ route('public.course.chapter.theory.show', [$course, $chapter, $quiz]) }}">
<div class="bg-surface-bright rounded-lg p-sm border border-glass-stroke flex items-start gap-3 hover:border-primary/30 hover:shadow-sm transition-all">
<div class="mt-1">
<span class="material-symbols-outlined text-secondary text-[20px]">edit_document</span>
</div>
<div>
<h4 class="font-label-md text-label-md text-on-surface group-hover:text-primary transition-colors">{{ $quiz->title }}</h4>
<p class="text-xs text-on-surface-variant mt-1">{{ $quiz->questions_count }} {{ Str::plural('Question', $quiz->questions_count) }} • Theory</p>
</div>
</div>
</a>
@empty
<p class="font-body-md text-body-md text-on-surface-variant">No theory practice yet.</p>
@endforelse
</div>
<a href="{{ route('public.course.chapter.theory', [$course, $chapter]) }}"
    class="mt-md font-label-md text-label-md text-secondary hover:text-on-surface w-full py-2 text-center transition-colors block">View All Theory</a>
</section>
</div>
</div>
</main>
@endsection
