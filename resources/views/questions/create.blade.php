@extends('layouts.app')

@section('title', 'Add Questions')
@section('meta-description', 'Add one or more questions to the bank.')

@section('page-title', 'Add Questions')
@section('page-subtitle', 'Add as many as you like in one go.')

@section('content')
    @php
        // Through course › chapter the bank is chapter-bound; from the sidenav
        // it is not, and no chapter is offered at all.
        $bankRoute = $chain
            ? route('courses.chapters.questions', [$chain['course'], $chain['chapter']])
            : route('questions');
        $storeRoute = $chain
            ? route('courses.chapters.questions.store', [$chain['course'], $chain['chapter']])
            : route('questions.store');
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
            <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters.dashboard', [$chain['course'], $chain['chapter']]) }}">{{ $chain['chapter']->title }}</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
        @endif
        <a class="hover:text-primary transition-colors" href="{{ $bankRoute }}">Question Bank</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Add Questions</span>
    </div>

    <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
        <form id="add-question-form" data-ajax-form data-question-form action="{{ $storeRoute }}" method="POST" class="p-6 md:p-8 flex flex-col gap-5">
            @csrf

            @if($chain)
                {{-- Fixed by the chain, so it is shown rather than chosen --}}
                <div class="flex flex-col gap-1.5 max-w-md">
                    <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</span>
                    <div class="w-full bg-surface-container-low/60 dark:bg-slate-900/60 border border-outline-variant/50 dark:border-slate-700 rounded-xl py-2.5 px-4 text-sm text-on-surface dark:text-slate-200 inline-flex items-center gap-2">
                        <i class="fa-solid fa-lock text-[11px] text-outline"></i>
                        {{ $chain['chapter']->title }}
                        <span class="text-xs text-outline">— applies to every question below</span>
                    </div>
                </div>
            @endif

            <div id="question-rows" class="flex flex-col gap-3"></div>

            <button type="button" id="add-question-row"
                class="w-full px-4 py-3 rounded-xl border-2 border-dashed border-outline-variant/60 dark:border-slate-600 text-sm font-semibold text-on-surface-variant dark:text-slate-400 hover:border-primary hover:text-primary hover:bg-primary/5 transition-colors inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i>
                Add another question
            </button>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant/30 dark:border-slate-700">
                <a href="{{ $bankRoute }}" class="px-5 py-2.5 rounded-full text-sm font-semibold border border-outline-variant/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </a>
                <button type="submit" data-loading-text="Saving…" class="px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all inline-flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-xs"></i>
                    Save Questions
                </button>
            </div>
        </form>
    </div>

    {{-- One question row --}}
    <template id="question-row-template">
        <div class="question-row rounded-2xl border border-outline-variant/40 dark:border-slate-700 bg-surface-container-lowest/60 dark:bg-slate-900/40 p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-on-surface-variant/70 dark:text-slate-500">
                    Question <span class="question-row-number">1</span>
                </span>
                <button type="button" class="question-row-remove text-on-surface-variant hover:text-error transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <textarea data-name="question" rows="2" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none resize-y" placeholder="Type the question…"></textarea>

            {{-- Type and difficulty sit above the answer: they decide its shape --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <select data-name="question_categories_id" class="question-type w-full bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" data-type="{{ $category->type }}">{{ $category->type === 'mcqs' ? 'MCQ' : 'Theory' }}</option>
                    @endforeach
                </select>
                <select data-name="difficulty_level" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    <option value="">No difficulty</option>
                    @foreach($difficulties as $level)
                        <option value="{{ $level }}">{{ $level }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Theory: a written answer --}}
            <div class="answer-theory flex flex-col gap-1.5">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-on-surface-variant/70 dark:text-slate-500">Answer <span class="font-normal normal-case tracking-normal text-outline">(Optional)</span></span>
                <textarea data-name="answer" rows="2" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none resize-y" placeholder="Answer…"></textarea>
            </div>

            {{-- MCQ: options, with the correct one selected --}}
            <div class="answer-mcq hidden flex-col gap-2">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-on-surface-variant/70 dark:text-slate-500">Options <span class="font-normal normal-case tracking-normal text-outline">— tick the correct answer</span></span>
                <ul class="option-list flex flex-col gap-2"></ul>
                <button type="button" class="option-add self-start text-xs font-semibold text-primary hover:text-primary/80 transition-colors inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add option
                </button>
            </div>
        </div>
    </template>

    {{-- One option row, cloned into an MCQ's option list --}}
    <template id="option-row-template">
        <li class="option-row flex items-center gap-2">
            <input type="radio" data-role="correct" class="w-4 h-4 text-primary border-outline-variant focus:ring-primary shrink-0" title="Correct answer">
            <input type="text" data-role="option" class="flex-1 bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none" placeholder="Option text…">
            <button type="button" class="option-remove w-7 h-7 flex items-center justify-center rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors shrink-0">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </li>
    </template>
@endsection

@push('scripts')
    <script src="{{ asset('js/question-form.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('add-question-form');

            // Stay on the page after saving so several batches can be entered
            // in a row; the toast reports what was added.
            form?.addEventListener('ajax:success', () => {
                form.reset();
                QuestionForm.resetRows();
            });
        });
    </script>
@endpush
