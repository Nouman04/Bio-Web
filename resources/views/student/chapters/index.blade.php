@extends('layouts.student')

@section('title', $course->title . ' – Chapter List')
@section('meta-description', 'Track your chapter progress for ' . $course->title)

@push('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .chapter-row {
        transition: background-color 0.2s ease, transform 0.15s ease;
    }
    .chapter-row:hover {
        transform: translateX(4px);
        box-shadow: 0px 4px 12px rgba(0, 19, 48, 0.06);
    }
</style>
@endpush

@section('content')

<!-- Back Breadcrumb + Page Title -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pt-4">
    <div>
        <a href="{{ route('student.courses') }}"
           class="inline-flex items-center gap-1 text-primary hover:opacity-80 text-xs font-semibold mb-3 transition-opacity">
            <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
            Back to My Courses
        </a>
        <h2 class="text-on-background" style="font-size:28px;line-height:36px;font-weight:600;">
            Course Progress: {{ $course->title }}
        </h2>
        @if($course->category)
            <p class="text-on-surface-variant text-sm mt-1">{{ $course->category->title }}</p>
        @endif
    </div>

    {{-- Only offered when there is actually something locked to unlock. --}}
    @if(! $subscribed && $rows->contains(fn ($row) => $row['locked']))
        <a href="{{ route('public.subscribe.plans', $course) }}"
            class="inline-flex items-center gap-2 bg-primary text-on-primary text-sm font-semibold py-2.5 px-5 rounded-xl shadow-sm hover:opacity-90 transition-opacity self-start">
            <span class="material-symbols-outlined" style="font-size:18px;">lock_open</span>
            Unlock every chapter
        </a>
    @endif
</div>

<!-- Summary Widgets -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <!-- Total Chapters -->
    <div class="glass-panel rounded-xl p-6">
        <div class="flex justify-between items-start mb-4">
            <span class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider">Total Chapters</span>
            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined" style="font-size:20px;">book</span>
            </div>
        </div>
        <div class="text-on-background" style="font-size:48px;line-height:56px;letter-spacing:-0.02em;font-weight:700;">{{ $rows->count() }}</div>
    </div>

    <!-- Chapters Completed -->
    <div class="glass-panel rounded-xl p-6">
        <div class="flex justify-between items-start mb-4">
            <span class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider">Chapters Completed</span>
            <div class="w-8 h-8 rounded-full bg-tertiary/10 flex items-center justify-center text-tertiary">
                <span class="material-symbols-outlined" style="font-size:20px;">check_circle</span>
            </div>
        </div>
        <div class="flex items-end gap-3">
            <div class="text-on-background" style="font-size:48px;line-height:56px;letter-spacing:-0.02em;font-weight:700;">{{ $completedCount }}</div>
            <div class="text-on-surface-variant text-sm pb-3">/ {{ $rows->count() }}</div>
        </div>
    </div>

    <!-- Overall Progress -->
    @php $overall = (float) $courseProgress['progress']; @endphp
    <div class="glass-panel rounded-xl p-6">
        <div class="flex justify-between items-start mb-4">
            <span class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider">Overall Progress</span>
            <div class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined" style="font-size:20px;">trending_up</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-on-background" style="font-size:48px;line-height:56px;letter-spacing:-0.02em;font-weight:700;">{{ round($overall) }}%</div>
            <div class="flex-1 h-2 bg-surface-container-highest rounded-full overflow-hidden">
                <div class="h-full bg-primary rounded-full" style="width: {{ $overall }}%"></div>
            </div>
        </div>
        <p class="text-on-surface-variant text-xs mt-2">
            @if($courseProgress['total_weight'] > 0)
                {{ $courseProgress['completed_weight'] }} of {{ $courseProgress['total_weight'] }} items completed
            @else
                Nothing to track yet
            @endif
        </p>
    </div>

</div>

<!-- Chapter List Table -->
<div class="glass-panel rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-outline-variant/20 bg-surface-container-low/50">
                    <th class="py-4 px-6 text-on-surface-variant text-xs font-semibold uppercase tracking-wider w-24">#</th>
                    <th class="py-4 px-6 text-on-surface-variant text-xs font-semibold uppercase tracking-wider">Title &amp; Description</th>
                    <th class="py-4 px-6 text-on-surface-variant text-xs font-semibold uppercase tracking-wider w-40">Status</th>
                    <th class="py-4 px-6 text-on-surface-variant text-xs font-semibold uppercase tracking-wider w-48 hidden md:table-cell">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/10 text-sm">
                @forelse($rows as $index => $row)
                    @php
                        $chapter = $row['chapter'];
                        $locked = $row['locked'];
                        $done = $row['completed'];
                        $percent = $row['progress'];
                        $isCurrent = $chapter->id === $currentId;
                        // A locked chapter leads to the paywall; an open one opens.
                        $href = $locked
                            ? route('public.course.chapter.subscribe', [$course, $chapter])
                            : route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapter->uuid]);
                        $striped = $index % 2 === 1 ? 'bg-surface-container-low/20' : '';
                    @endphp

                    <tr class="chapter-row {{ $striped }} hover:bg-surface-container-lowest/60 cursor-pointer {{ $locked ? 'opacity-60' : '' }}"
                        onclick="window.location='{{ $href }}'">
                        <td class="py-4 px-6">
                            @if($locked)
                                <div class="w-8 h-8 rounded-full bg-surface-container-highest flex items-center justify-center text-on-surface-variant/50">
                                    <span class="material-symbols-outlined" style="font-size:16px;">lock</span>
                                </div>
                            @elseif($done)
                                <div class="w-8 h-8 rounded-full bg-tertiary/10 border border-tertiary/20 flex items-center justify-center text-tertiary font-semibold text-xs">
                                    <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1;">check_circle</span>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-primary/10 border border-primary/20 {{ $isCurrent ? 'ring-2 ring-primary/20' : '' }} flex items-center justify-center text-primary font-semibold text-xs">
                                    {{ $chapter->chapter_number }}
                                </div>
                            @endif
                        </td>

                        <td class="py-4 px-6">
                            <div class="{{ $locked ? 'text-on-background/70' : 'text-on-background' }} font-semibold text-sm mb-1 flex items-center gap-2">
                                {{ $chapter->title }}
                                @if($isCurrent && ! $done)
                                    <span class="text-xs font-medium text-primary bg-primary/10 px-2 py-0.5 rounded-full">Current</span>
                                @endif
                            </div>
                            <div class="{{ $locked ? 'text-on-surface-variant/70' : 'text-on-surface-variant' }} text-xs line-clamp-1">
                                {{ $chapter->excerpt ?: 'No description yet.' }}
                            </div>
                        </td>

                        <td class="py-4 px-6">
                            @if($locked)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container-highest text-on-surface-variant text-xs font-semibold border border-outline-variant/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-outline"></span>
                                    Locked
                                </span>
                            @elseif($done)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-tertiary/10 text-tertiary text-xs font-semibold border border-tertiary/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-tertiary"></span>
                                    Completed
                                </span>
                            @elseif($percent > 0)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-semibold border border-secondary/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
                                    In Progress
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container-highest text-on-surface-variant text-xs font-semibold border border-outline-variant/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-outline"></span>
                                    Not started
                                </span>
                            @endif
                        </td>

                        <td class="py-4 px-6 hidden md:table-cell">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 h-1.5 bg-surface-container-highest rounded-full overflow-hidden">
                                    <div class="h-full {{ $done ? 'bg-tertiary' : 'bg-secondary' }} rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                                <span class="{{ $percent > 0 ? 'text-on-surface-variant' : 'text-on-surface-variant/50' }} text-xs w-8">{{ round($percent) }}%</span>
                            </div>
                            @if($row['total_weight'] > 0)
                                <span class="text-on-surface-variant/70 text-[11px]">{{ $row['completed_weight'] }} / {{ $row['total_weight'] }} items</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-16 px-6 text-center">
                            <span class="material-symbols-outlined text-5xl text-outline mb-3 block">menu_book</span>
                            <div class="text-on-surface font-semibold mb-1">No chapters yet</div>
                            <p class="text-on-surface-variant text-sm">This course has not been filled in yet. Check back soon.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
