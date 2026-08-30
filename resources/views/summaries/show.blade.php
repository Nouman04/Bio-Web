@extends('layouts.app')

@section('title', $summary->title)
@section('meta-description', 'Summary details.')

@section('page-title', $summary->title)
@section('page-subtitle', $summary->chapter?->title ?? 'Summary')

@section('content')
    {{-- Breadcrumbs.

         A summary belongs to a chapter, which belongs to a course, and the
         trail said none of that — it went straight from Home to the title, so
         there was no way back to the chapter it was written for. The course and
         chapter links only appear when the summary has them. --}}
    @php
        $crumbChapter = $summary->chapter;
        $crumbCourse = $crumbChapter?->course;
    @endphp
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6 flex-wrap">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses') }}">Courses</a>
        @if($crumbCourse)
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters', $crumbCourse) }}">{{ $crumbCourse->title }}</a>
        @endif
        @if($crumbChapter && $crumbCourse)
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters.dashboard', [$crumbCourse, $crumbChapter]) }}">{{ $crumbChapter->title }}</a>
        @endif
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors"
            href="{{ route('summaries', $crumbChapter ? ['chapter' => $crumbChapter->uuid] : []) }}">Summaries</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold truncate max-w-xs">{{ $summary->title }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- The summary itself --}}
        <div class="lg:col-span-8 flex flex-col gap-6">
            <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <div class="p-6 border-b border-outline-variant/30 dark:border-slate-700">
                    <h3 class="text-base font-bold text-on-surface dark:text-white">Content</h3>
                </div>
                <div class="p-6 prose max-w-none text-sm text-on-surface dark:text-slate-200 leading-relaxed [&_h2]:text-base [&_h2]:font-bold [&_h2]:mt-5 [&_h2]:mb-2 [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mb-1 [&_a]:text-primary [&_img]:rounded-xl">
                    {!! $summary->content ?: '<p class="text-outline">No content.</p>' !!}
                </div>
            </div>

            @include('partials.detail-questions', ['questions' => $questions])
        </div>

        {{-- Facts + actions --}}
        <div class="lg:col-span-4 flex flex-col gap-6">
            @include('partials.detail-meta', ['rows' => [
                'Chapter' => $summary->chapter?->title,
                'Topic' => $summary->topic?->title,
                'Slug' => $summary->slug,
                'Added by' => $summary->addedBy?->name,
                'Created' => $summary->created_at?->format('M j, Y'),
                'Last updated' => $summary->updated_at?->diffForHumans(),
            ]])

            <a href="{{ route('summaries', ['title' => $summary->title, 'open' => $summary->uuid]) }}"
                class="w-full px-6 py-2.5 rounded-full bg-primary text-white text-sm font-semibold shadow-md hover:shadow-lg transition-all inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-pen text-xs"></i>
                Edit summary
            </a>
        </div>
    </div>
@endsection
