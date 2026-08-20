@extends('public.layouts.app')

@section('title', $course->title . ' — Chapters | Lumina LMS')

@include('public.catalog._styles')

@section('content')
<main class="flex-grow w-full max-w-container-max mx-auto px-md md:px-lg py-lg md:py-xl flex flex-col gap-xl">
<!-- Header Section -->
<section class="flex flex-col gap-sm">
@include('public.catalog._breadcrumb', ['crumbs' => [
    'Courses' => route('public.courses'),
    $course->title => route('public.course.chapters', $course),
]])
<h1 class="font-display-lg text-display-lg text-on-background mt-2">{{ $course->title }} Chapters</h1>
@if($course->excerpt)
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-3xl">{{ $course->excerpt }}</p>
@endif
</section>
<!-- Chapters Grid -->
@if($chapters->isEmpty())
<section class="glass-panel rounded-xl p-lg text-center">
<span class="material-symbols-outlined text-primary text-[40px] mb-2">menu_book</span>
<h2 class="font-headline-md text-headline-md text-on-surface mb-2">No chapters published yet</h2>
<p class="font-body-md text-body-md text-on-surface-variant">This course's chapters will appear here once they are made public.</p>
</section>
@else
<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-md">
@foreach($chapters as $chapter)
@php $locked = $chapter->visibility !== 'public'; @endphp
<div class="glass-panel rounded-xl p-md flex flex-col gap-md shadow-primary-glow hover:-translate-y-1 transition-transform duration-300 {{ $locked ? 'opacity-90' : '' }}">
<div class="flex justify-between items-start">
<span class="font-label-sm text-label-sm px-2 py-1 rounded-md {{ $locked ? 'text-on-surface-variant bg-surface-container-high' : 'text-primary dark:text-inverse-primary bg-primary-fixed/20' }}">Chapter {{ $chapter->chapter_number }}</span>
<span class="material-symbols-outlined {{ $locked ? 'text-secondary' : 'text-outline' }}">{{ $locked ? 'lock' : ['biotech', 'science', 'genetics'][$loop->index % 3] }}</span>
</div>
<div>
<h2 class="font-headline-md text-headline-md text-on-surface mb-2">{{ $chapter->title }}</h2>
<p class="font-body-md text-body-md text-on-surface-variant line-clamp-2">
    {{ $locked
        ? 'Part of the premium syllabus. Subscribe to open this chapter.'
        : ($chapter->excerpt ?: 'Open the chapter to see its notes, flashcards and practice.') }}
</p>
</div>
<div class="mt-auto flex flex-col gap-sm pt-4 border-t border-glass-stroke">
@if($locked)
    <a href="{{ route('public.course.chapter.subscribe', [$course, $chapter]) }}"
        class="mt-2 w-full flex items-center justify-center gap-xs text-center font-label-md text-label-md bg-surface-container-low border border-outline-variant text-on-surface hover:bg-primary hover:text-on-primary hover:border-primary transition-colors py-2 rounded-full scale-95 active:transition-all duration-200">
        <span class="material-symbols-outlined text-[18px]">lock</span>
        Unlock Chapter
    </a>
@else
    <a href="{{ route('public.course.chapter.show', [$course, $chapter]) }}"
        class="mt-2 w-full block text-center font-label-md text-label-md bg-primary text-on-primary shadow-primary/30 hover:bg-primary-container transition-colors py-2 rounded-full scale-95 active:transition-all duration-200">Open Chapter</a>
@endif
</div>
</div>
@endforeach
</section>
@endif
</main>
@endsection
