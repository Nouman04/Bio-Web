@extends('layouts.app')

@section('title', $chapter['title'] . ' — Dashboard')
@section('meta-description', 'Manage all resources for this chapter in one place.')

@section('page-title', 'Chapter Dashboard')
@section('page-subtitle', $chapter['title'])

@section('content')

    {{-- Ambient Background Glow --}}
    <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-primary/5 to-transparent pointer-events-none -z-10"></div>

    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6 flex-wrap">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses') }}">Courses</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters', $courseId) }}">{{ $courseTitle }}</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">{{ $chapter['title'] }}</span>
    </div>

    {{-- Header Card --}}
    <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-3xl p-6 border border-outline-variant/30 dark:border-slate-700 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary/10 text-primary font-semibold text-xs">{{ $chapter['num'] }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold {{ $chapter['status'] === 'Published' ? 'text-tertiary bg-tertiary-container/20' : 'text-on-surface-variant bg-slate-100 dark:bg-slate-700' }}">
                    {{ $chapter['status'] }}
                </span>
            </div>
            <h1 class="text-3xl font-bold text-on-surface dark:text-white tracking-tight mb-1">{{ $chapter['title'] }}</h1>
            <p class="text-on-surface-variant dark:text-slate-400 text-sm max-w-2xl">{{ $chapter['desc'] }}</p>
        </div>
    </div>

    {{-- Management Modules Grid --}}
    @php
        $modules = [
            ['label' => 'Students', 'stat' => '240 Enrolled', 'icon' => 'fa-solid fa-users', 'bg' => 'icon-bg-indigo', 'route' => route('students')],
            ['label' => 'Summaries', 'stat' => '4 Documents', 'icon' => 'fa-solid fa-list-check', 'bg' => 'icon-bg-amber', 'route' => route('summaries')],
            ['label' => 'Quizzes', 'stat' => '12 Active', 'icon' => 'fa-solid fa-clipboard-question', 'bg' => 'icon-bg-teal', 'route' => route('quizzes')],
            ['label' => 'Diagrams', 'stat' => '15 Assets', 'icon' => 'fa-regular fa-image', 'bg' => 'icon-bg-rose', 'route' => route('diagrams')],
            ['label' => 'Guides', 'stat' => '6 Manuals', 'icon' => 'fa-solid fa-book-open', 'bg' => 'icon-bg-orange', 'route' => route('guides', [$courseId, $chapter['id']])],
            ['label' => 'Study Notes', 'stat' => '22 Notes', 'icon' => 'fa-regular fa-note-sticky', 'bg' => 'icon-bg-blue', 'route' => route('notes', [$courseId, $chapter['id']])],
            ['label' => 'Questions', 'stat' => '150 Items', 'icon' => 'fa-regular fa-circle-question', 'bg' => 'icon-bg-rose', 'route' => route('courses.chapters.questions', [$courseId, $chapter['id']])],
            ['label' => 'Video Lessons', 'stat' => '8 Videos', 'icon' => 'fa-solid fa-circle-play', 'bg' => 'icon-bg-amber', 'route' => route('videos')],
            ['label' => 'Topics', 'stat' => 'Sections in this chapter', 'icon' => 'fa-solid fa-tags', 'bg' => 'icon-bg-blue', 'route' => route('topics', [$courseId, $chapter['id']])],
            ['label' => 'Flashcards', 'stat' => '4 Decks', 'icon' => 'fa-solid fa-layer-group', 'bg' => 'icon-bg-teal', 'route' => route('flashcards', [$courseId, $chapter['id']])],
        ];
    @endphp

    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Manage Content</h3>
        <span class="text-xs text-on-surface-variant dark:text-slate-500">{{ count($modules) }} modules</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach ($modules as $m)
            <a href="{{ $m['route'] }}"
                class="group flex flex-col gap-5 p-5 bg-surface-container-lowest dark:bg-slate-800 rounded-2xl border border-outline-variant/20 dark:border-slate-700 shadow-sm hover:shadow-lg hover:shadow-black/5 dark:hover:shadow-black/20 hover:border-outline-variant/40 dark:hover:border-slate-600 hover:-translate-y-0.5 transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-xl {{ $m['bg'] }} flex items-center justify-center text-white shadow-md">
                        <i class="{{ $m['icon'] }} text-lg"></i>
                    </div>
                    <i class="fa-solid fa-arrow-right text-outline-variant/60 dark:text-slate-600 group-hover:text-primary dark:group-hover:text-primary-fixed-dim group-hover:translate-x-1 transition-all duration-300 text-sm mt-2"></i>
                </div>
                <div>
                    <h4 class="text-base font-semibold text-on-surface dark:text-white tracking-tight">{{ $m['label'] }}</h4>
                    <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-1 font-medium">{{ $m['stat'] }}</p>
                </div>
            </a>
        @endforeach
    </div>

@endsection
