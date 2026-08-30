@extends('layouts.app')

@section('title', 'Pending quizzes – ' . $student->name)
@section('meta-description', 'Papers from ' . $student->name . ' waiting to be marked.')

@section('page-title', 'Pending Quizzes')
@section('page-subtitle', $student->name . ' — papers waiting on you to mark them.')

@section('content')

    {{-- Ambient Background Glow --}}
    <div class="absolute top-0 left-0 w-full h-96 bg-primary/5 pointer-events-none -z-10"></div>

    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('students') }}">Students</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('students.show', $student->uuid) }}">{{ $student->name }}</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Pending Quizzes</span>
    </div>

    <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-outline-variant/20 dark:border-slate-700 bg-surface-container-low/40 dark:bg-slate-900/40 flex items-center justify-between gap-3">
            <h3 class="font-bold text-on-surface dark:text-white">
                {{ $attempts->count() }} {{ Str::plural('paper', $attempts->count()) }} waiting
            </h3>
            <span class="text-xs text-on-surface-variant dark:text-slate-400">Oldest first</span>
        </div>

        <div class="divide-y divide-outline-variant/20 dark:divide-slate-700">
            @forelse($attempts as $attempt)
                <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-semibold text-on-surface dark:text-white truncate">{{ $attempt->quiz?->title ?? 'Quiz' }}</p>
                        <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-1">
                            Handed in {{ $attempt->submitted_at?->format('d M Y, H:i') ?? '—' }}
                            @if($attempt->submitted_at)
                                &middot; {{ $attempt->submitted_at->diffForHumans() }}
                            @endif
                            @if($attempt->total_marks)
                                &middot; out of {{ rtrim(rtrim(number_format((float) $attempt->total_marks, 2, '.', ''), '0'), '.') }} marks
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <span class="px-3 py-1 rounded-full bg-error/10 text-error text-xs font-bold">Awaiting marks</span>
                        {{-- The marking itself lives on the review page, which
                             checks ownership again before saving anything. --}}
                        <a href="{{ route('quizzes.review.show', $attempt->uuid) }}"
                            class="px-5 py-2 rounded-full bg-primary text-on-primary text-xs font-semibold hover:bg-primary/90 transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                            Mark quiz
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-16 text-center">
                    <i class="fa-solid fa-circle-check text-3xl text-tertiary/50 mb-3"></i>
                    <h4 class="font-semibold text-on-surface dark:text-white mb-1">Nothing to mark</h4>
                    <p class="text-sm text-on-surface-variant dark:text-slate-400 mb-6">
                        {{ $student->name }} has no papers waiting on you.
                    </p>
                    <a href="{{ route('students.show', $student->uuid) }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-on-primary text-sm font-semibold hover:bg-primary/90 transition-colors">
                        Back to their dashboard
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            @endforelse
        </div>
    </div>

@endsection
