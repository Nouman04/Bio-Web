@extends('public.layouts.app')

@section('title', 'Study Notes — ' . $chapter->title . ' | Lumina LMS')

@include('public.catalog._styles')

@section('content')
<main class="max-w-container-max mx-auto px-md md:px-lg py-lg md:py-xl min-h-[calc(100vh-160px)]">
<header class="mb-xl relative z-10">
@include('public.catalog._breadcrumb', ['crumbs' => [
    'Courses' => route('public.courses'),
    $course->title => route('public.course.chapters', $course),
    'Chapter ' . $chapter->chapter_number => route('public.course.chapter.show', [$course, $chapter]),
    'Study Notes' => '#',
]])
<div class="flex items-center gap-sm mt-2">
<div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container">
<span class="material-symbols-outlined fill">menu_book</span>
</div>
<h1 class="font-display-lg text-display-lg text-on-surface">Study Notes</h1>
</div>
<p class="font-body-lg text-body-lg text-on-surface-variant mt-base max-w-3xl">Every note written for {{ $chapter->title }}.</p>
</header>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-md relative z-10">
@forelse($notes as $note)
<a class="group block" href="{{ route('public.course.chapter.notes.show', [$course, $chapter, $note]) }}">
<article class="glass-panel rounded-xl p-md flex flex-col gap-sm h-full hover:-translate-y-1 hover:shadow-primary-glow transition-all duration-300">
<div class="flex justify-between items-start">
<span class="font-label-sm text-label-sm text-primary bg-primary-fixed/20 px-2 py-1 rounded-md">{{ $note->type === 'summary' ? 'Summary Note' : 'Exam Note' }}</span>
<span class="material-symbols-outlined text-outline">description</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors">{{ $note->title }}</h3>
<p class="font-body-md text-body-md text-on-surface-variant line-clamp-3">{{ $note->excerpt }}</p>
<div class="mt-auto pt-4 border-t border-glass-stroke flex items-center justify-between">
<span class="font-label-sm text-label-sm text-on-surface-variant">{{ $note->topic?->title ?: 'General' }}</span>
<span class="material-symbols-outlined text-outline-variant group-hover:text-primary transform group-hover:translate-x-1 transition-all">arrow_forward</span>
</div>
</article>
</a>
@empty
<div class="md:col-span-2 lg:col-span-3 glass-panel rounded-xl p-lg text-center">
<span class="material-symbols-outlined text-primary text-[40px] mb-2">description</span>
<h2 class="font-headline-md text-headline-md text-on-surface mb-2">No notes yet</h2>
<p class="font-body-md text-body-md text-on-surface-variant">Notes for this chapter will appear here.</p>
</div>
@endforelse
</section>
</main>
@endsection
