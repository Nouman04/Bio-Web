@extends('layouts.app')

@section('title', $student->name)
@section('meta-description', 'Course progress for ' . $student->name)

@section('page-title', $student->name)
@section('page-subtitle', 'What they are subscribed to, and how far through it they are.')

@section('content')

    {{-- Ambient Background Glow --}}
    <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-primary/5 to-transparent pointer-events-none -z-10"></div>

    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('students') }}">Students</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">{{ $student->name }}</span>
    </div>

    {{-- Who this is --}}
    <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-3xl p-6 border border-outline-variant/30 dark:border-slate-700 shadow-sm mb-6 flex flex-col sm:flex-row sm:items-center gap-5">
        <span class="w-16 h-16 shrink-0 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xl font-bold uppercase">
            {{ Str::of($student->name)->explode(' ')->take(2)->map(fn ($part) => Str::substr($part, 0, 1))->implode('') ?: '?' }}
        </span>
        <div class="flex-1 min-w-0">
            <h2 class="text-xl font-bold text-on-surface dark:text-white truncate">{{ $student->name }}</h2>
            <p class="text-sm text-on-surface-variant dark:text-slate-400 truncate">
                <a href="mailto:{{ $student->email }}" class="hover:text-primary transition-colors">{{ $student->email }}</a>
                &middot; joined {{ $student->created_at?->format('d M Y') ?? '—' }}
            </p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <div class="text-center px-5 py-3 rounded-2xl bg-primary/5">
                <p class="text-2xl font-bold text-on-surface dark:text-white leading-tight">{{ $courses->count() }}</p>
                <p class="text-[11px] text-on-surface-variant dark:text-slate-400">{{ Str::plural('Course', $courses->count()) }}</p>
            </div>
            <a href="{{ route('students.pending', $student->uuid) }}"
                class="text-center px-5 py-3 rounded-2xl {{ $pending->isNotEmpty() ? 'bg-error/10 hover:bg-error/20' : 'bg-surface-container' }} transition-colors">
                <p class="text-2xl font-bold {{ $pending->isNotEmpty() ? 'text-error' : 'text-on-surface dark:text-white' }} leading-tight">{{ $pending->count() }}</p>
                <p class="text-[11px] text-on-surface-variant dark:text-slate-400">Pending {{ Str::plural('quiz', $pending->count()) }}</p>
            </a>
        </div>
    </div>

    {{-- Course progress --}}
    <h3 class="text-sm font-bold text-on-surface dark:text-white uppercase tracking-wide mb-3">Courses assigned</h3>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
        @forelse($courses as $row)
            @php
                $course = $row['course'];
                $percent = round($row['progress']['progress']);
            @endphp

            <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="min-w-0">
                        <a href="{{ route('courses.chapters', $course) }}"
                            class="font-semibold text-on-surface dark:text-white hover:text-primary transition-colors truncate block">
                            {{ $course->title }}
                        </a>
                        <span class="text-xs text-on-surface-variant dark:text-slate-400">
                            {{ $course->category?->title ?: 'Uncategorised' }}
                            &middot; {{ $course->chapters_count }} {{ Str::plural('chapter', $course->chapters_count) }}
                        </span>
                    </div>
                    <span class="text-lg font-bold text-primary shrink-0">{{ $percent }}%</span>
                </div>

                <div class="w-full h-2 rounded-full bg-surface-container-high dark:bg-slate-700 overflow-hidden">
                    <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $percent }}%"></div>
                </div>

                <div class="flex items-center justify-between mt-3 text-xs text-on-surface-variant dark:text-slate-400">
                    <span>
                        {{ $row['progress']['completed_weight'] }} of {{ $row['progress']['total_weight'] }}
                        {{ Str::plural('item', $row['progress']['total_weight']) }} done
                    </span>
                    @if($row['subscribed_at'])
                        <span>Subscribed {{ $row['subscribed_at']->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="lg:col-span-2 bg-surface-container-lowest dark:bg-slate-800 rounded-2xl py-16 border border-outline-variant/30 dark:border-slate-700 text-center">
                <i class="fa-solid fa-graduation-cap text-3xl text-on-surface-variant/40 mb-3"></i>
                <p class="text-sm text-on-surface-variant dark:text-slate-400">
                    No live subscription to any of your courses.
                </p>
            </div>
        @endforelse
    </div>

    {{-- What is waiting on a marker --}}
    <div class="flex items-center justify-between gap-3 mb-3">
        <h3 class="text-sm font-bold text-on-surface dark:text-white uppercase tracking-wide">Waiting to be marked</h3>
        @if($pending->isNotEmpty())
            <a href="{{ route('students.pending', $student->uuid) }}" class="text-xs font-semibold text-primary hover:underline">
                Open the marking list
            </a>
        @endif
    </div>

    <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl border border-outline-variant/30 dark:border-slate-700 shadow-sm divide-y divide-outline-variant/20 dark:divide-slate-700">
        @forelse($pending as $attempt)
            <div class="p-4 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-semibold text-on-surface dark:text-white text-sm truncate">{{ $attempt->quiz?->title ?? 'Quiz' }}</p>
                    <p class="text-xs text-on-surface-variant dark:text-slate-400">
                        Handed in {{ $attempt->submitted_at?->diffForHumans() ?? 'recently' }}
                    </p>
                </div>
                <a href="{{ route('quizzes.review.show', $attempt->uuid) }}"
                    class="shrink-0 px-4 py-2 rounded-full bg-primary text-on-primary text-xs font-semibold hover:bg-primary/90 transition-colors">
                    Mark it
                </a>
            </div>
        @empty
            <div class="p-10 text-center">
                <i class="fa-solid fa-circle-check text-2xl text-tertiary/50 mb-2"></i>
                <p class="text-sm text-on-surface-variant dark:text-slate-400">Nothing of theirs is waiting on you.</p>
            </div>
        @endforelse
    </div>

@endsection
