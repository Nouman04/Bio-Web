@extends('layouts.student')

@section('title', 'Dashboard')
@section('meta-description', 'Your courses, your progress and what needs your attention.')

@push('styles')
<style>
    .glass-panel {
        background-color: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .glass-panel-hover:hover {
        box-shadow: 0px 10px 30px rgba(0, 19, 48, 0.08);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    /* The brand button. Flat primary rather than the two-stop gradient it was;
       theme.css flips it for the dark palette. */
    .btn-primary-gradient {
        background: rgb(var(--c-primary));
        box-shadow: 0px 4px 15px rgba(0, 19, 48, 0.2);
    }
    .btn-primary-gradient:hover {
        background: rgb(var(--c-primary-container));
        box-shadow: 0px 6px 20px rgba(0, 19, 48, 0.3);
        transform: translateY(-1px);
    }
    /* The ring animates from empty to wherever the student actually is. */
    .progress-ring circle.value {
        transition: stroke-dashoffset 1.1s cubic-bezier(0.22, 1, 0.36, 1);
    }
</style>
@endpush

@section('content')

@php
    $firstName = \Illuminate\Support\Str::of($student->name)->explode(' ')->first();
    $ring = 2 * M_PI * 40;
@endphp

{{-- Hero --}}
<section class="mb-6 sm:mb-8 pt-2 sm:pt-4">
    <h2 class="text-on-background mb-2" style="font-size:clamp(28px, 6vw, 48px);line-height:clamp(34px, 7vw, 56px);letter-spacing:-0.02em;font-weight:700;">
        Welcome back, {{ $firstName }}!
    </h2>
    <p class="text-on-surface-variant" style="font-size:clamp(15px, 3vw, 18px);line-height:clamp(22px, 4vw, 28px);">
        @if($resume)
            Ready to pick up where you left off?
        @elseif($courses->isNotEmpty())
            Your courses are ready when you are.
        @else
            Browse the catalog to get started.
        @endif
    </p>
</section>

{{-- The numbers --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['school', 'Courses', $stats['courses'], 'primary'],
        ['menu_book', 'Chapters', $stats['chapters'], 'secondary'],
        ['workspace_premium', 'Quizzes passed', $stats['quizzes_passed'], 'tertiary'],
        ['bookmark', 'Saved items', $stats['saved'], 'primary'],
    ] as [$icon, $label, $value, $tone])
        <div class="glass-panel rounded-xl p-4 flex items-center gap-3">
            <span class="w-10 h-10 shrink-0 rounded-lg bg-{{ $tone }}/10 text-{{ $tone }} flex items-center justify-center">
                <span class="material-symbols-outlined" style="font-size:20px;">{{ $icon }}</span>
            </span>
            <div class="min-w-0">
                <p class="text-on-background font-bold leading-tight" style="font-size:22px;">{{ $value }}</p>
                <p class="text-on-surface-variant text-xs truncate">{{ $label }}</p>
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 md:grid-cols-12 gap-6">

    <div class="md:col-span-8 flex flex-col gap-6">

        {{-- Carry on from the last thing they opened --}}
        <div class="glass-panel glass-panel-hover rounded-xl p-6 relative overflow-hidden flex flex-col justify-between min-h-[240px]">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>

            @if($resume)
                <div class="relative z-10 flex flex-col h-full">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-tertiary-container/10 text-tertiary mb-4 border border-tertiary/20 text-xs font-semibold">
                            <span class="material-symbols-outlined" style="font-size:14px;">{{ $resume['icon'] }}</span>
                            {{ $resume['completed'] ? 'Last completed' : 'Continue where you left off' }}
                        </div>

                        <h3 class="text-on-background mb-2" style="font-size:clamp(22px, 4.5vw, 32px);line-height:clamp(28px, 5.5vw, 40px);letter-spacing:-0.01em;font-weight:600;">
                            {{ $resume['title'] }}
                        </h3>
                        <p class="text-on-surface-variant mb-6 text-sm">
                            {{ $resume['course'] }} &middot; {{ $resume['chapter'] }} &middot; {{ $resume['label'] }}
                            @if($resume['touched'])
                                &middot; {{ $resume['touched']->diffForHumans() }}
                            @endif
                        </p>
                    </div>

                    <div class="mt-auto">
                        <div class="flex justify-between items-end mb-2">
                            <span class="text-outline text-xs font-medium">{{ $resume['course'] }}</span>
                            <span class="text-primary text-sm font-semibold">{{ round($resume['course_progress']['progress']) }}%</span>
                        </div>
                        <div class="w-full bg-surface-variant rounded-full h-2 mb-6 overflow-hidden">
                            <div class="bg-primary h-2 rounded-full transition-all duration-1000" style="width: {{ round($resume['course_progress']['progress']) }}%"></div>
                        </div>
                        <a href="{{ $resume['url'] }}"
                            class="btn-primary-gradient text-on-primary text-sm font-semibold px-6 py-3 rounded-full inline-flex items-center gap-2 transition-all w-fit">
                            {{ $resume['completed'] ? 'Open it again' : 'Resume learning' }}
                            <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="relative z-10 flex flex-col items-start justify-center h-full py-6">
                    <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined" style="font-size:28px;">rocket_launch</span>
                    </div>
                    <h3 class="text-on-background mb-2" style="font-size:24px;line-height:32px;font-weight:600;">Nothing started yet</h3>
                    <p class="text-on-surface-variant text-sm mb-6 max-w-md">
                        {{ $courses->isNotEmpty()
                            ? 'Open a chapter and the dashboard will keep your place from then on.'
                            : 'Subscribe to a course and everything you study will be tracked here.' }}
                    </p>
                    <a href="{{ $courses->isNotEmpty() ? route('student.courses') : route('student.catalog') }}"
                        class="btn-primary-gradient text-on-primary text-sm font-semibold px-6 py-3 rounded-full inline-flex items-center gap-2 transition-all">
                        {{ $courses->isNotEmpty() ? 'Go to my courses' : 'Browse the catalog' }}
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                    </a>
                </div>
            @endif
        </div>

        {{-- Every course they are subscribed to --}}
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-on-background" style="font-size:20px;line-height:28px;font-weight:600;">My courses</h3>
            @if($courses->isNotEmpty())
                <a href="{{ route('student.courses') }}" class="text-primary hover:underline text-xs font-semibold">View all</a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 -mt-2">
            @forelse($courses->take(4) as $row)
                @php
                    $course = $row['course'];
                    $percent = round($row['progress']['progress']);

                    // Written out in full so the classes survive a Tailwind build.
                    [$stateLabel, $stateClass] = match (true) {
                        $percent >= 100 => ['Complete', 'text-tertiary bg-tertiary-container/20'],
                        $percent >= 75 => ['Near completion', 'text-tertiary bg-tertiary-container/20'],
                        $percent > 0 => ['In progress', 'text-secondary bg-secondary-container/20'],
                        default => ['Not started', 'text-on-surface-variant bg-surface-container-high'],
                    };
                @endphp

                <a href="{{ route('student.chapters', ['courseId' => $course->uuid]) }}"
                    class="glass-panel glass-panel-hover rounded-xl p-5 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-4 gap-2">
                        <div class="w-10 h-10 rounded-lg bg-surface-container-high flex items-center justify-center text-on-surface-variant shrink-0">
                            <span class="material-symbols-outlined">school</span>
                        </div>
                        <span class="text-xs font-medium {{ $stateClass }} px-2 py-1 rounded whitespace-nowrap">{{ $stateLabel }}</span>
                    </div>

                    <h4 class="text-on-background mb-1" style="font-size:18px;line-height:26px;font-weight:600;">{{ $course->title }}</h4>
                    <p class="text-on-surface-variant text-xs mb-4">
                        {{ $course->category?->title ?: 'Course' }}
                        &middot; {{ $course->chapters_count }} {{ Str::plural('chapter', $course->chapters_count) }}
                        &middot; {{ $row['progress']['completed_weight'] }}/{{ $row['progress']['total_weight'] }} done
                    </p>

                    <div class="mt-auto">
                        <div class="flex justify-between items-end mb-1.5">
                            <span class="text-outline text-[11px] font-medium">Progress</span>
                            <span class="text-primary text-xs font-semibold">{{ $percent }}%</span>
                        </div>
                        <div class="w-full bg-surface-variant rounded-full h-1.5 overflow-hidden">
                            <div class="bg-primary h-1.5 rounded-full transition-all duration-1000" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="md:col-span-2 glass-panel rounded-xl py-14 flex flex-col items-center justify-center text-center">
                    <span class="material-symbols-outlined text-primary text-4xl mb-3">school</span>
                    <h4 class="text-on-background font-semibold mb-1">No courses yet</h4>
                    <p class="text-on-surface-variant text-sm mb-5 max-w-sm">
                        Subscribe to a course and it will appear here with your progress.
                    </p>
                    <a href="{{ route('student.catalog') }}"
                        class="btn-primary-gradient text-on-primary text-sm font-semibold px-6 py-2.5 rounded-full inline-flex items-center gap-2">
                        Browse the catalog
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Side column --}}
    <div class="md:col-span-4 flex flex-col gap-6">

        {{-- Overall progress across every subscribed course --}}
        <div class="glass-panel rounded-xl p-6 flex flex-col items-center relative overflow-hidden">
            <h3 class="text-on-background w-full text-left mb-6" style="font-size:20px;line-height:28px;font-weight:600;">Overall progress</h3>

            <div class="relative w-40 h-40 flex items-center justify-center mb-4">
                <svg class="progress-ring w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                    <circle class="progress-ring-track" cx="50" cy="50" fill="transparent" r="40" stroke-width="8"></circle>
                    <circle class="value" cx="50" cy="50" fill="transparent" r="40"
                        stroke="#001330"
                        stroke-dasharray="{{ round($ring, 1) }}"
                        stroke-dashoffset="{{ round($ring * (1 - min(100, $overall['progress']) / 100), 1) }}"
                        stroke-linecap="round" stroke-width="8"></circle>
                </svg>
                <div class="absolute flex flex-col items-center">
                    <span class="text-on-background" style="font-size:44px;line-height:52px;letter-spacing:-0.02em;font-weight:700;">{{ round($overall['progress']) }}</span>
                    <span class="text-outline -mt-2 text-xs font-medium">%</span>
                </div>
            </div>

            <p class="text-on-surface-variant text-center text-sm max-w-[85%]">
                @if($overall['total_weight'] > 0)
                    You have completed
                    <strong class="text-on-background font-semibold">{{ $overall['completed_weight'] }} of {{ $overall['total_weight'] }}</strong>
                    items across {{ $stats['courses'] }} {{ Str::plural('course', $stats['courses']) }}.
                @else
                    Nothing is being tracked yet.
                @endif
            </p>
        </div>

        {{-- What is waiting on them --}}
        <div class="glass-panel rounded-xl p-6 flex-1">
            <div class="flex justify-between items-center mb-5 gap-3">
                <h3 class="text-on-background" style="font-size:20px;line-height:28px;font-weight:600;">Needs your attention</h3>
                <a class="text-primary hover:underline text-xs font-semibold" href="{{ route('student.quizzes') }}">View all</a>
            </div>

            <div class="space-y-2">
                @forelse($attention as $item)
                    @php
                        // Written out in full so the classes survive a Tailwind build.
                        [$bg, $fg] = match ($item['tone']) {
                            'error' => ['bg-error-container/40', 'text-error'],
                            'tertiary' => ['bg-tertiary-container/20', 'text-tertiary'],
                            default => ['bg-secondary-container/20', 'text-secondary'],
                        };
                    @endphp

                    <a href="{{ $item['url'] }}"
                        class="flex gap-3 items-start group p-2 -mx-2 rounded-lg hover:bg-surface-container/50 transition-colors">
                        <div class="w-10 h-10 rounded-full {{ $bg }} flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined {{ $fg }}" style="font-size:20px;">{{ $item['icon'] }}</span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-on-background group-hover:text-primary transition-colors text-sm font-semibold truncate">{{ $item['title'] }}</h4>
                            <p class="text-on-surface-variant text-xs">{{ $item['note'] }}</p>
                        </div>
                    </a>
                @empty
                    <div class="py-8 text-center">
                        <span class="material-symbols-outlined text-tertiary/60 text-3xl mb-2">check_circle</span>
                        <p class="text-on-surface-variant text-sm">Nothing needs you right now.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Straight to the things they use most --}}
        <div class="glass-panel rounded-xl p-6">
            <h3 class="text-on-background mb-4" style="font-size:20px;line-height:28px;font-weight:600;">Jump to</h3>
            <div class="grid grid-cols-2 gap-3">
                @foreach([
                    ['My courses', 'school', route('student.courses')],
                    ['My quizzes', 'quiz', route('student.quizzes')],
                    ['Saved', 'bookmark', route('student.resources')],
                    ['Catalog', 'explore', route('student.catalog')],
                ] as [$label, $icon, $url])
                    <a href="{{ $url }}"
                        class="flex flex-col items-center gap-2 p-3 rounded-xl bg-surface-container-lowest/60 hover:bg-primary/5 border border-outline-variant/30 hover:border-primary/30 transition-colors text-center">
                        <span class="material-symbols-outlined text-primary" style="font-size:22px;">{{ $icon }}</span>
                        <span class="text-on-surface text-xs font-semibold">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
