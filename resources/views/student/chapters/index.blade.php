@extends('layouts.student')

@section('title', 'Course Progress – Chapter List')
@section('meta-description', 'Track your chapter progress for Computer Science 101')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
        box-shadow: 0px 4px 12px rgba(70, 72, 212, 0.06);
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
            Course Progress: Computer Science 101
        </h2>
    </div>
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
        <div class="text-on-background" style="font-size:48px;line-height:56px;letter-spacing:-0.02em;font-weight:700;">12</div>
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
            <div class="text-on-background" style="font-size:48px;line-height:56px;letter-spacing:-0.02em;font-weight:700;">4</div>
            <div class="text-on-surface-variant text-sm pb-3">/ 12</div>
        </div>
    </div>

    <!-- Overall Progress -->
    <div class="glass-panel rounded-xl p-6">
        <div class="flex justify-between items-start mb-4">
            <span class="text-on-surface-variant text-xs font-semibold uppercase tracking-wider">Overall Progress</span>
            <div class="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined" style="font-size:20px;">trending_up</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-on-background" style="font-size:48px;line-height:56px;letter-spacing:-0.02em;font-weight:700;">33%</div>
            <div class="flex-1 h-2 bg-surface-container-highest rounded-full overflow-hidden">
                <div class="h-full bg-primary rounded-full" style="width: 33%"></div>
            </div>
        </div>
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

                {{-- Chapter 1 – Completed --}}
                <tr class="chapter-row hover:bg-surface-container-lowest/60 cursor-pointer"
                    onclick="window.location='{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => 1]) }}'">
                    <td class="py-4 px-6">
                        <div class="w-8 h-8 rounded-full bg-tertiary/10 border border-tertiary/20 flex items-center justify-center text-tertiary font-semibold text-xs">
                            <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1;">check_circle</span>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="text-on-background font-semibold text-sm mb-1">Introduction to Variables</div>
                        <div class="text-on-surface-variant text-xs line-clamp-1">Understanding the basics of memory allocation and data types in modern programming languages.</div>
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-tertiary/10 text-tertiary text-xs font-semibold border border-tertiary/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-tertiary"></span>
                            Completed
                        </span>
                    </td>
                    <td class="py-4 px-6 hidden md:table-cell">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-1.5 bg-surface-container-highest rounded-full overflow-hidden">
                                <div class="h-full bg-tertiary rounded-full w-full"></div>
                            </div>
                            <span class="text-on-surface-variant text-xs w-8">100%</span>
                        </div>
                    </td>
                </tr>

                {{-- Chapter 2 – Completed --}}
                <tr class="chapter-row bg-surface-container-low/20 hover:bg-surface-container-lowest/60 cursor-pointer"
                    onclick="window.location='{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => 2]) }}'">
                    <td class="py-4 px-6">
                        <div class="w-8 h-8 rounded-full bg-tertiary/10 border border-tertiary/20 flex items-center justify-center text-tertiary font-semibold text-xs">
                            <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1;">check_circle</span>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="text-on-background font-semibold text-sm mb-1">Control Structures &amp; Loops</div>
                        <div class="text-on-surface-variant text-xs line-clamp-1">Mastering if/else statements, for loops, and while loops to control program flow effectively.</div>
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-tertiary/10 text-tertiary text-xs font-semibold border border-tertiary/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-tertiary"></span>
                            Completed
                        </span>
                    </td>
                    <td class="py-4 px-6 hidden md:table-cell">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-1.5 bg-surface-container-highest rounded-full overflow-hidden">
                                <div class="h-full bg-tertiary rounded-full w-full"></div>
                            </div>
                            <span class="text-on-surface-variant text-xs w-8">100%</span>
                        </div>
                    </td>
                </tr>

                {{-- Chapter 3 – In Progress --}}
                <tr class="chapter-row hover:bg-surface-container-lowest/60 cursor-pointer"
                    onclick="window.location='{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => 3]) }}'">
                    <td class="py-4 px-6">
                        <div class="w-8 h-8 rounded-full bg-primary/10 border border-primary/20 ring-2 ring-primary/20 flex items-center justify-center text-primary font-semibold text-xs">3</div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="text-on-background font-semibold text-sm mb-1 flex items-center gap-2">
                            Functions and Scope
                            <span class="text-xs font-medium text-primary bg-primary/10 px-2 py-0.5 rounded-full">Current</span>
                        </div>
                        <div class="text-on-surface-variant text-xs line-clamp-1">Creating reusable blocks of code and understanding local vs global variable scope.</div>
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-semibold border border-secondary/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
                            In Progress
                        </span>
                    </td>
                    <td class="py-4 px-6 hidden md:table-cell">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-1.5 bg-surface-container-highest rounded-full overflow-hidden">
                                <div class="h-full bg-secondary rounded-full" style="width: 45%"></div>
                            </div>
                            <span class="text-on-surface-variant text-xs w-8">45%</span>
                        </div>
                    </td>
                </tr>

                {{-- Chapter 4 – Locked --}}
                <tr class="chapter-row bg-surface-container-low/20 opacity-60">
                    <td class="py-4 px-6">
                        <div class="w-8 h-8 rounded-full bg-surface-container-highest flex items-center justify-center text-on-surface-variant/50">
                            <span class="material-symbols-outlined" style="font-size:16px;">lock</span>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="text-on-background/70 font-semibold text-sm mb-1">Arrays and Data Structures</div>
                        <div class="text-on-surface-variant/70 text-xs line-clamp-1">Storing multiple values in lists and iterating over collections of data.</div>
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container-highest text-on-surface-variant text-xs font-semibold border border-outline-variant/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-outline"></span>
                            Locked
                        </span>
                    </td>
                    <td class="py-4 px-6 hidden md:table-cell">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-1.5 bg-surface-container-highest rounded-full overflow-hidden">
                                <div class="h-full bg-outline-variant rounded-full w-0"></div>
                            </div>
                            <span class="text-on-surface-variant/50 text-xs w-8">0%</span>
                        </div>
                    </td>
                </tr>

                {{-- Chapter 5 – Locked --}}
                <tr class="chapter-row opacity-60">
                    <td class="py-4 px-6">
                        <div class="w-8 h-8 rounded-full bg-surface-container-highest flex items-center justify-center text-on-surface-variant/50">
                            <span class="material-symbols-outlined" style="font-size:16px;">lock</span>
                        </div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="text-on-background/70 font-semibold text-sm mb-1">Object-Oriented Programming</div>
                        <div class="text-on-surface-variant/70 text-xs line-clamp-1">Classes, objects, inheritance, and encapsulation — the pillars of OOP design.</div>
                    </td>
                    <td class="py-4 px-6">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container-highest text-on-surface-variant text-xs font-semibold border border-outline-variant/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-outline"></span>
                            Locked
                        </span>
                    </td>
                    <td class="py-4 px-6 hidden md:table-cell">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-1.5 bg-surface-container-highest rounded-full overflow-hidden"></div>
                            <span class="text-on-surface-variant/50 text-xs w-8">0%</span>
                        </div>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>

@endsection
