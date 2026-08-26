@extends('layouts.app')

@section('title', 'Quizzes to Mark')
@section('page-title', 'Quizzes to Mark')
@section('page-subtitle', 'Written answers waiting on you')

@section('content')
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6 flex-wrap">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('quizzes') }}">Quizzes</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">To Mark</span>
    </div>

    <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl border border-outline-variant/20 dark:border-slate-700 shadow-sm overflow-hidden">
        @forelse($attempts as $attempt)
            <div class="flex items-center justify-between gap-4 p-5 border-b border-outline-variant/10 dark:border-slate-700 last:border-b-0 hover:bg-surface-container-low/50 dark:hover:bg-slate-700/30 transition-colors">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-10 h-10 rounded-full bg-secondary/10 text-secondary flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-on-surface dark:text-slate-100 font-semibold truncate">{{ $attempt->quiz?->title }}</h3>
                        <p class="text-on-surface-variant dark:text-slate-400 text-xs mt-0.5">
                            {{ $attempt->user?->name }} &middot; handed in {{ $attempt->submitted_at?->diffForHumans() }}
                        </p>
                    </div>
                </div>

                <a href="{{ route('quizzes.review.show', $attempt->uuid) }}"
                    class="bg-primary text-on-primary text-xs font-semibold py-2 px-5 rounded-full hover:bg-primary/90 transition-colors shrink-0 inline-flex items-center gap-2">
                    Mark
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        @empty
            <div class="py-20 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 rounded-full bg-tertiary/10 text-tertiary flex items-center justify-center mb-4">
                    <i class="fa-solid fa-check text-2xl"></i>
                </div>
                <h3 class="text-on-surface dark:text-slate-100 font-semibold mb-1">Nothing to mark</h3>
                <p class="text-on-surface-variant dark:text-slate-400 text-sm">
                    Written answers appear here as soon as a student hands one in.
                </p>
            </div>
        @endforelse
    </div>
@endsection
