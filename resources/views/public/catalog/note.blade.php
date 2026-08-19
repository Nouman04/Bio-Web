@extends('public.layouts.app')

@section('title', $note->title . ' | Lumina LMS')

@include('public.catalog._styles')

@section('content')
<main class="max-w-container-max mx-auto px-md md:px-lg py-lg md:py-xl min-h-[calc(100vh-160px)]">
<header class="mb-lg relative z-10">
@include('public.catalog._breadcrumb', ['crumbs' => [
    'Courses' => route('public.courses'),
    $course->title => route('public.course.chapters', $course),
    'Chapter ' . $chapter->chapter_number => route('public.course.chapter.show', [$course, $chapter]),
    'Study Notes' => route('public.course.chapter.notes', [$course, $chapter]),
    $note->title => '#',
]])
<span class="font-label-sm text-label-sm text-primary bg-primary-fixed/20 px-2 py-1 rounded-md">{{ $note->type === 'summary' ? 'Summary Note' : 'Exam Note' }}</span>
<h1 class="font-display-lg text-display-lg text-on-surface mt-3">{{ $note->title }}</h1>
<p class="font-body-md text-body-md text-on-surface-variant mt-2">
    {{ $chapter->title }}@if($note->topic) · {{ $note->topic->title }}@endif
    @if($note->summary) · from the summary “{{ $note->summary->title }}” @endif
</p>
</header>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-lg relative z-10">
<!-- The note itself -->
<article class="lg:col-span-8 glass-panel rounded-xl p-md md:p-lg">
<div class="prose max-w-none font-body-md text-body-md text-on-surface leading-relaxed [&_h1]:font-headline-lg [&_h2]:font-headline-md [&_h2]:text-headline-md [&_h2]:mt-6 [&_h2]:mb-2 [&_h3]:font-headline-md [&_p]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mb-1 [&_img]:rounded-lg [&_a]:text-primary">
    {!! $note->content !!}
</div>
</article>

<!-- Other notes in this chapter -->
<aside class="lg:col-span-4">
<div class="glass-panel rounded-xl p-md sticky top-[96px]">
<h2 class="font-headline-md text-headline-md text-on-surface mb-md">More in this chapter</h2>
<div class="flex flex-col gap-sm">
@foreach($siblings as $sibling)
<a class="group block" href="{{ route('public.course.chapter.notes.show', [$course, $chapter, $sibling]) }}">
<div class="bg-surface-bright rounded-lg p-sm border border-glass-stroke flex items-center gap-3 transition-all {{ $sibling->id === $note->id ? 'border-primary/40 bg-primary/5' : 'hover:border-primary/30 hover:shadow-sm' }}">
<span class="material-symbols-outlined text-[20px] {{ $sibling->id === $note->id ? 'text-primary' : 'text-outline-variant group-hover:text-primary' }}">description</span>
<h4 class="font-label-md text-label-md {{ $sibling->id === $note->id ? 'text-primary' : 'text-on-surface group-hover:text-primary' }} transition-colors line-clamp-2">{{ $sibling->title }}</h4>
</div>
</a>
@endforeach
</div>
<a href="{{ route('public.course.chapter.notes', [$course, $chapter]) }}"
    class="mt-md font-label-md text-label-md text-secondary hover:text-on-surface w-full py-2 text-center transition-colors block">Back to all notes</a>
</div>
</aside>
</div>
</main>
@endsection
