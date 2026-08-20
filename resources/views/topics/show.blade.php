@extends('layouts.app')

@section('title', $topic->title)
@section('meta-description', 'Topic details.')

@section('page-title', $topic->title)
@section('page-subtitle', $chapter->title)

@section('content')
    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses') }}">Courses</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters', $course) }}">{{ $course->title }}</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters.dashboard', [$course, $chapter]) }}">{{ $chapter->title }}</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('topics', [$course, $chapter]) }}">Topics</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold truncate max-w-xs">{{ $topic->title }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 flex flex-col gap-6">
            <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <div class="p-6 border-b border-outline-variant/30 dark:border-slate-700">
                    <h3 class="text-base font-bold text-on-surface dark:text-white">Content</h3>
                </div>
                <div class="p-6 prose max-w-none text-sm text-on-surface dark:text-slate-200 leading-relaxed [&_h2]:text-base [&_h2]:font-bold [&_h2]:mt-5 [&_h2]:mb-2 [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mb-1 [&_a]:text-primary [&_img]:rounded-xl">
                    {!! $topic->content ?: '<p class="text-outline">No content.</p>' !!}
                </div>
            </div>

            @if($topic->attachments->isNotEmpty())
                <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                    <div class="p-6 border-b border-outline-variant/30 dark:border-slate-700 flex items-center justify-between">
                        <h3 class="text-base font-bold text-on-surface dark:text-white">Attachments</h3>
                        <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">{{ $topic->attachments->count() }}</span>
                    </div>
                    <ul class="p-6 flex flex-col gap-2">
                        @foreach($topic->attachments as $attachment)
                            <li>
                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" rel="noopener"
                                    class="flex items-center gap-3 px-4 py-3 rounded-xl bg-surface-container-low dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700 hover:border-primary/40 transition-colors group">
                                    <i class="fa-solid fa-paperclip text-on-surface-variant group-hover:text-primary transition-colors"></i>
                                    <span class="text-sm text-on-surface dark:text-slate-200 truncate">{{ basename($attachment->file_path) }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-xs text-outline-variant ml-auto"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @include('partials.detail-questions', ['questions' => $questions])
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">
            @include('partials.detail-meta', ['rows' => [
                'Course' => ['url' => route('courses.chapters', $course), 'label' => $course->title],
                'Chapter' => ['url' => route('courses.chapters.dashboard', [$course, $chapter]), 'label' => $chapter->title],
                'Attachments' => $topic->attachments->count(),
                'Created' => $topic->created_at?->format('M j, Y'),
                'Last updated' => $topic->updated_at?->diffForHumans(),
            ]])

            <div class="flex flex-col gap-3">
                <a href="{{ route('topics', [$course, $chapter, 'title' => $topic->title, 'open' => $topic->uuid]) }}"
                    class="w-full px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md hover:shadow-lg transition-all inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-pen text-xs"></i>
                    Edit topic
                </a>
                <a href="{{ route('topics.assign', [$course, $chapter, $topic]) }}"
                    class="w-full px-6 py-2.5 rounded-full border border-outline-variant/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 text-sm font-semibold hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors inline-flex items-center justify-center gap-2">
                    <i class="fa-regular fa-circle-question text-xs"></i>
                    Assign questions
                </a>
            </div>
        </div>
    </div>
@endsection
