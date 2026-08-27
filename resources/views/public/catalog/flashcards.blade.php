@extends('public.layouts.app')

@section('title', 'Flashcards — ' . $chapter->title . ' | Your Biology')

@include('public.catalog._styles')

@section('content')
<main class="max-w-container-max mx-auto px-md md:px-lg py-lg md:py-xl min-h-[calc(100vh-160px)]">
<header class="mb-xl relative z-10">
@include('public.catalog._breadcrumb', ['crumbs' => [
    'Courses' => route('public.courses'),
    $course->title => route('public.course.chapters', $course),
    'Chapter ' . $chapter->chapter_number => route('public.course.chapter.show', [$course, $chapter]),
    'Flashcards' => '#',
]])
<div class="flex items-center gap-sm mt-2">
<div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container">
<span class="material-symbols-outlined fill">style</span>
</div>
<h1 class="font-display-lg text-display-lg text-on-surface">Study Sets</h1>
</div>
<p class="font-body-lg text-body-lg text-on-surface-variant mt-base max-w-3xl">Flip through the decks built for {{ $chapter->title }}.</p>
</header>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-md relative z-10">
@forelse($decks as $deck)
<a class="group block" href="{{ route('public.course.chapter.flashcards.show', [$course, $chapter, $deck]) }}">
<article class="glass-panel rounded-xl p-md flex flex-col gap-sm h-full hover:-translate-y-1 hover:shadow-primary-glow transition-all duration-300">
<div class="flex justify-between items-start">
<div class="w-12 h-12 rounded-lg bg-secondary-container/40 flex items-center justify-center text-primary">
<span class="material-symbols-outlined fill">style</span>
</div>
<span class="font-label-sm text-label-sm text-on-surface-variant bg-surface-container-high px-2 py-1 rounded-md">{{ $deck->assessments_count }} {{ Str::plural('Card', $deck->assessments_count) }}</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors mt-2">{{ $deck->title }}</h3>
<div class="mt-auto pt-4 border-t border-glass-stroke flex items-center justify-between">
<span class="font-label-sm text-label-sm text-on-surface-variant">Tap to study</span>
<span class="material-symbols-outlined text-outline-variant group-hover:text-primary text-[20px]">play_circle</span>
</div>
</article>
</a>
@empty
<div class="md:col-span-2 lg:col-span-3 glass-panel rounded-xl p-lg text-center">
<span class="material-symbols-outlined text-primary text-[40px] mb-2">style</span>
<h2 class="font-headline-md text-headline-md text-on-surface mb-2">No study sets yet</h2>
<p class="font-body-md text-body-md text-on-surface-variant">Decks for this chapter will appear here.</p>
</div>
@endforelse
</section>
</main>
@endsection
