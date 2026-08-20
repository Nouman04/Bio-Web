@extends('layouts.app')

@section('title', $note->title)
@section('meta-description', 'Study note details.')

@section('page-title', $note->title)
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
        <a class="hover:text-primary transition-colors" href="{{ route('notes', [$course, $chapter]) }}">Study Notes</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold truncate max-w-xs">{{ $note->title }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 flex flex-col gap-6">
            <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <div class="p-6 border-b border-outline-variant/30 dark:border-slate-700">
                    <h3 class="text-base font-bold text-on-surface dark:text-white">Content</h3>
                </div>
                <div class="p-6 prose max-w-none text-sm text-on-surface dark:text-slate-200 leading-relaxed [&_h2]:text-base [&_h2]:font-bold [&_h2]:mt-5 [&_h2]:mb-2 [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mb-1 [&_a]:text-primary [&_img]:rounded-xl">
                    {!! $note->content ?: '<p class="text-outline">No content.</p>' !!}
                </div>
            </div>

            @if($note->attachments->isNotEmpty())
                <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                    <div class="p-6 border-b border-outline-variant/30 dark:border-slate-700 flex items-center justify-between">
                        <h3 class="text-base font-bold text-on-surface dark:text-white">Attachments</h3>
                        <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">{{ $note->attachments->count() }}</span>
                    </div>
                    <ul class="p-6 flex flex-col gap-2">
                        @foreach($note->attachments as $attachment)
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
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">
            @include('partials.detail-meta', ['rows' => [
                'Course' => ['url' => route('courses.chapters', $course), 'label' => $course->title],
                'Chapter' => ['url' => route('courses.chapters.dashboard', [$course, $chapter]), 'label' => $chapter->title],
                'Type' => ['chip' => $note->type === 'summary' ? 'Summary note' : 'Exam note'],
                'Topic' => $note->topic?->title,
                'From summary' => $note->summary?->title,
                'Attachments' => $note->attachments->count(),
                'Created' => $note->created_at?->format('M j, Y'),
                'Last updated' => $note->updated_at?->diffForHumans(),
            ]])

            <a href="{{ route('notes', [$course, $chapter, 'title' => $note->title, 'open' => $note->uuid]) }}"
                class="w-full px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md hover:shadow-lg transition-all inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-pen text-xs"></i>
                Edit note
            </a>
        </div>
    </div>
@endsection
