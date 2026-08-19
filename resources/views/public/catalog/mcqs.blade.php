@extends('public.layouts.app')

@section('title', 'MCQ Quizzes — ' . $chapter->title . ' | Lumina LMS')

@include('public.catalog._styles')

@section('content')
<main class="max-w-container-max mx-auto px-md md:px-lg py-lg md:py-xl min-h-[calc(100vh-160px)]">
<header class="mb-xl relative z-10">
@include('public.catalog._breadcrumb', ['crumbs' => [
    'Courses' => route('public.courses'),
    $course->title => route('public.course.chapters', $course),
    'Chapter ' . $chapter->chapter_number => route('public.course.chapter.show', [$course, $chapter]),
    'MCQ Quizzes' => '#',
]])
<div class="flex items-center gap-sm mt-2">
<div class="w-10 h-10 rounded-full bg-tertiary-container/20 flex items-center justify-center text-tertiary">
<span class="material-symbols-outlined fill">quiz</span>
</div>
<h1 class="font-display-lg text-display-lg text-on-surface">MCQ Quizzes</h1>
</div>
<p class="font-body-lg text-body-lg text-on-surface-variant mt-base max-w-3xl">Practice built for {{ $chapter->title }}.</p>
</header>

<section class="flex flex-col gap-md relative z-10">
@forelse($quizzes as $quiz)
<a class="group block" href="{{ route('public.course.chapter.mcqs.show', [$course, $chapter, $quiz]) }}">
<article class="glass-panel rounded-xl p-md flex flex-col md:flex-row md:items-center gap-md hover:-translate-y-1 hover:shadow-primary-glow transition-all duration-300">
<div class="w-12 h-12 rounded-lg bg-surface-container-low flex items-center justify-center text-primary shrink-0">
<span class="material-symbols-outlined">radio_button_checked</span>
</div>
<div class="flex-grow min-w-0">
<h3 class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors">{{ $quiz->title }}</h3>
<p class="font-body-md text-body-md text-on-surface-variant line-clamp-2 mt-xs">{{ $quiz->excerpt ?: 'Work through the questions at your own pace.' }}</p>
</div>
<div class="flex items-center gap-md shrink-0">
<div class="flex flex-col items-center px-3">
<span class="font-headline-md text-headline-md text-primary">{{ $quiz->questions_count }}</span>
<span class="font-label-sm text-label-sm text-on-surface-variant">{{ Str::plural('Question', $quiz->questions_count) }}</span>
</div>
@if($quiz->duration)
<div class="flex flex-col items-center px-3 border-l border-glass-stroke">
<span class="font-headline-md text-headline-md text-on-surface">{{ $quiz->duration }}</span>
<span class="font-label-sm text-label-sm text-on-surface-variant">Minutes</span>
</div>
@endif
<span class="font-label-md text-label-md bg-primary text-on-primary px-6 py-2.5 rounded-full group-hover:bg-primary-container transition-colors shadow-md whitespace-nowrap">Start Quiz</span>
</div>
</article>
</a>
@empty
<div class="glass-panel rounded-xl p-lg text-center">
<span class="material-symbols-outlined text-primary text-[40px] mb-2">quiz</span>
<h2 class="font-headline-md text-headline-md text-on-surface mb-2">Nothing published yet</h2>
<p class="font-body-md text-body-md text-on-surface-variant">MCQ Quizzes for this chapter will appear here.</p>
</div>
@endforelse
</section>
</main>
@endsection
