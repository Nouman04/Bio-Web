@extends('layouts.app')

@section('title', $quiz->title)
@section('meta-description', 'Quiz details.')

@section('page-title', $quiz->title)
@section('page-subtitle', $types[$quiz->type] ?? $quiz->type)

@section('content')
    @php
        // Reached from either entry; keep the chain when there is one.
        $chainIds = $chain ? [$chain['course'], $chain['chapter']] : [];
        $listRoute = $chain ? route('courses.chapters.quizzes', $chainIds) : route('quizzes');
        $editRoute = $chain
            ? route('courses.chapters.quizzes.edit', array_merge($chainIds, [$quiz]))
            : route('quizzes.edit', $quiz);
        $totalMarks = collect($questions)->sum(fn ($q) => (float) $q['marks']);
    @endphp

    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        @if($chain)
            <a class="hover:text-primary transition-colors" href="{{ route('courses') }}">Courses</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters', $chain['course']) }}">{{ $chain['course']->title }}</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters.dashboard', $chainIds) }}">{{ $chain['chapter']->title }}</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
        @endif
        <a class="hover:text-primary transition-colors" href="{{ $listRoute }}">Quizzes</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold truncate max-w-xs">{{ $quiz->title }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 flex flex-col gap-6">
            @if($quiz->description)
                <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                    <div class="p-6 border-b border-outline-variant/30 dark:border-slate-700">
                        <h3 class="text-base font-bold text-on-surface dark:text-white">Description</h3>
                    </div>
                    <div class="p-6 prose max-w-none text-sm text-on-surface dark:text-slate-200 leading-relaxed [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-6 [&_a]:text-primary">
                        {!! $quiz->description !!}
                    </div>
                </div>
            @endif

            @include('partials.detail-questions', [
                'questions' => $questions,
                'heading' => 'Questions',
                'showMarks' => true,
            ])
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">
            @include('partials.detail-meta', ['rows' => [
                'Status' => ['chip' => ucfirst($quiz->status), 'class' => match ($quiz->status) {
                    'published' => 'bg-tertiary-container/20 text-tertiary',
                    'closed' => 'bg-error/10 text-error',
                    default => 'bg-surface-container-low text-on-surface-variant dark:bg-slate-900 dark:text-slate-300',
                }],
                'Type' => $types[$quiz->type] ?? $quiz->type,
                'Chapters' => $quiz->chapters->pluck('title')->filter()->implode(', ') ?: 'No chapter',
                'Questions' => count($questions),
                'Total marks' => $totalMarks,
                'Pass mark' => $quiz->passing_score !== null
                    ? rtrim(rtrim(number_format((float) $quiz->passing_score, 2, '.', ''), '0'), '.') . ' of ' . $totalMarks
                    : null,
                'Duration' => $quiz->duration ? $quiz->duration . ' minutes' : null,
                'Shuffled' => $quiz->shuffle_questions ? 'Yes' : 'No',
                'Created' => $quiz->created_at?->format('M j, Y'),
                'Last updated' => $quiz->updated_at?->diffForHumans(),
            ]])

            <a href="{{ $editRoute }}"
                class="w-full px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md hover:shadow-lg transition-all inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-pen text-xs"></i>
                Edit quiz
            </a>
        </div>
    </div>
@endsection
