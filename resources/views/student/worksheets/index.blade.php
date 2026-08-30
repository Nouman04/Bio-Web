@extends('layouts.student')

@section('title', 'Worksheets')
@section('page-title', 'Worksheets')
@section('page-subtitle', 'Papers built from the question bank.')

@section('content')

<div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
    <a class="hover:text-primary transition-colors" href="{{ route('student.dashboard') }}">Home</a>
    <i class="fa-solid fa-chevron-right text-[10px]"></i>
    <span class="text-primary dark:text-primary-fixed-dim font-semibold">Worksheets</span>
</div>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <div class="relative">
            <span class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm"></span>
            <input name="search" value="{{ $filters['search'] }}" type="search" placeholder="Search worksheets"
                class="w-full sm:w-56 pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
        </div>
        <select name="course"
            class="bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none">
            <option value="">All courses</option>
            @foreach($courses as $course)
                <option value="{{ $course->uuid }}" @selected($filters['course'] === $course->uuid)>{{ $course->title }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-primary/10 text-primary text-sm font-semibold rounded-lg hover:bg-primary/20 transition-colors">
            Filter
        </button>
        @if($filters['search'] || $filters['course'])
            <a href="{{ route('student.worksheets') }}" class="text-sm text-on-surface-variant hover:text-primary transition-colors">Clear</a>
        @endif
    </form>

    <a href="{{ route('student.worksheets.create') }}"
        class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all shrink-0">
        <i class="fa-solid fa-plus text-xs"></i>
        Create Worksheet
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse($worksheets as $worksheet)
        <article class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl p-5 border border-outline-variant/30 dark:border-slate-700 shadow-sm flex flex-col">
            <div class="flex items-start justify-between gap-3 mb-3">
                <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-file-lines"></i>
                </span>
                <span class="text-xs font-semibold text-on-surface-variant bg-surface-container-high px-2.5 py-1 rounded-full whitespace-nowrap">
                    {{ $worksheet->assessments_count }} {{ Str::plural('question', $worksheet->assessments_count) }}
                </span>
            </div>

            <a href="{{ route('student.worksheets.show', $worksheet->uuid) }}"
                class="font-semibold text-on-surface dark:text-white hover:text-primary transition-colors">
                {{ $worksheet->title }}
            </a>
            <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-1 mb-4">
                {{ $worksheet->course?->title ?: 'No course' }}
                &middot; {{ $worksheet->creator?->name ?: 'Unknown' }}
                &middot; {{ $worksheet->created_at?->diffForHumans() }}
            </p>

            <div class="flex items-center gap-2 mt-auto pt-3 border-t border-outline-variant/20 dark:border-slate-700">
                <a href="{{ route('student.worksheets.paper', $worksheet->uuid) }}"
                    class="flex-1 text-center text-xs font-semibold py-2 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                    <i class="fa-solid fa-file-pdf text-[11px]"></i> Question paper
                </a>
                <a href="{{ route('student.worksheets.mark-scheme', $worksheet->uuid) }}"
                    class="flex-1 text-center text-xs font-semibold py-2 rounded-lg bg-tertiary/10 text-tertiary hover:bg-tertiary/20 transition-colors">
                    <i class="fa-solid fa-list-check text-[11px]"></i> Mark scheme
                </a>
            </div>
        </article>
    @empty
        <div class="md:col-span-2 xl:col-span-3 bg-surface-container-lowest dark:bg-slate-800 rounded-2xl py-20 border border-outline-variant/30 dark:border-slate-700 text-center">
            <div class="w-16 h-16 mx-auto rounded-full bg-primary/5 flex items-center justify-center mb-4">
                <i class="fa-solid fa-file-lines text-2xl text-primary"></i>
            </div>
            <h3 class="font-semibold text-on-surface dark:text-white mb-1">
                {{ $filters['search'] || $filters['course'] ? 'Nothing matched' : 'No worksheets yet' }}
            </h3>
            <p class="text-sm text-on-surface-variant dark:text-slate-400 mb-6">
                {{ $filters['search'] || $filters['course']
                    ? 'No worksheet matches those filters.'
                    : 'Build one from the question bank and it will appear here.' }}
            </p>
            <a href="{{ route('student.worksheets.create') }}"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-primary text-on-primary text-sm font-semibold">
                <i class="fa-solid fa-plus text-xs"></i> Create Worksheet
            </a>
        </div>
    @endforelse
</div>

@if($worksheets->hasPages())
    <div class="mt-8 flex justify-center">{{ $worksheets->links() }}</div>
@endif

@endsection
