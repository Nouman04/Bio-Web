@extends('layouts.student')

@section('title', 'Student Dashboard')
@section('meta-description', 'EduAdmin – Student Learning Dashboard')

@push('styles')
<style>
    .glass-panel {
        background-color: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .glass-panel-hover:hover {
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    .ambient-shadow {
        box-shadow: 0px 10px 30px rgba(70, 72, 212, 0.08);
    }
    .btn-primary-gradient {
        background: linear-gradient(135deg, #4648d4, #6063ee);
        box-shadow: 0px 4px 15px rgba(70, 72, 212, 0.2);
    }
    .btn-primary-gradient:hover {
        background: linear-gradient(135deg, #6063ee, #4648d4);
        box-shadow: 0px 6px 20px rgba(70, 72, 212, 0.3);
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
{{-- Google Material Symbols (used by this page) --}}
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

<!-- Hero Section -->
<section class="mb-8 pt-4">
    <h2 class="font-display-lg text-display-lg text-on-background mb-2" style="font-size:48px;line-height:56px;letter-spacing:-0.02em;font-weight:700;">Welcome back, Alex!</h2>
    <p class="text-body-lg text-on-surface-variant" style="font-size:18px;line-height:28px;">Ready to pick up where you left off?</p>
</section>

<!-- Bento Grid Layout -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">

    <!-- Quick Resume & Progress (8 cols) -->
    <div class="md:col-span-8 flex flex-col gap-6">

        <!-- Quick Resume Card -->
        <div class="glass-panel glass-panel-hover rounded-xl p-6 relative overflow-hidden flex flex-col justify-between min-h-[280px]">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between gap-6 h-full">
                <div class="flex-1 flex flex-col justify-between">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-tertiary-container/10 text-tertiary mb-4 border border-tertiary/20 text-xs font-semibold">
                            <span class="material-symbols-outlined" style="font-size:14px;">play_circle</span>
                            Recently Accessed
                        </div>
                        <h3 class="text-on-background mb-2" style="font-size:32px;line-height:40px;letter-spacing:-0.01em;font-weight:600;">Advanced UI/UX Principles</h3>
                        <p class="text-on-surface-variant mb-6" style="font-size:16px;line-height:24px;">Module 4: Designing for Cognitive Load</p>
                    </div>
                    <div class="mt-auto">
                        <div class="flex justify-between items-end mb-2">
                            <span class="text-outline text-xs font-medium">Progress</span>
                            <span class="text-primary text-sm font-semibold">68%</span>
                        </div>
                        <div class="w-full bg-surface-variant rounded-full h-2 mb-6 overflow-hidden">
                            <div class="bg-primary h-2 rounded-full" style="width: 68%"></div>
                        </div>
                        <button class="btn-primary-gradient text-on-primary text-sm font-semibold px-6 py-3 rounded-full inline-flex items-center gap-2 transition-all w-fit">
                            Resume Learning
                            <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                        </button>
                    </div>
                </div>
                <!-- Visual -->
                <div class="hidden md:block w-[240px] h-[200px] rounded-lg overflow-hidden border border-white/40 shadow-sm relative shrink-0">
                    <img class="w-full h-full object-cover"
                         src="https://lh3.googleusercontent.com/aida-public/AB6AXuD-CA3J9OopxwxAOlIkHyWjlxWJ5nALM-elYTK2JLEdHrqHjQeD8JtOxMpeBBadsetD7tzecXOXMqoG1Hgnz-CZAEUn_6UUQzQ7elWijK588EKAXj9K4-RSdUlEWZFZd8ukQNpuUN1vAjrFFjxWHHDSVdYNNz1Kq8JG1RTIEA5XmRW4NM79yoOv1CkqdEph0amXQEvQ_yO3kE6hq09u0zehHAX1VQP8O_VODhu9Ke_LEBWekY1K8tLS"
                         alt="Course visual"/>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                    <div class="absolute bottom-3 left-3 right-3">
                        <span class="text-xs text-white/80 bg-black/40 backdrop-blur-md px-2 py-1 rounded">Video • 12 mins left</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Overview Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="glass-panel glass-panel-hover rounded-xl p-5 flex flex-col justify-between">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 rounded-lg bg-surface-container-high flex items-center justify-center text-on-surface-variant">
                        <span class="material-symbols-outlined">code</span>
                    </div>
                    <span class="text-xs font-medium text-secondary bg-secondary-container/20 px-2 py-1 rounded">In Progress</span>
                </div>
                <h4 class="text-on-background mb-1" style="font-size:20px;line-height:28px;font-weight:600;">Introduction to React</h4>
                <p class="text-primary mb-4 flex items-center gap-1 text-sm font-semibold">
                    <span class="material-symbols-outlined" style="font-size:14px;">arrow_right_alt</span> Next: State Management
                </p>
                <div class="w-full bg-surface-variant rounded-full h-1.5 mt-auto">
                    <div class="bg-primary-fixed-dim h-1.5 rounded-full" style="width: 35%"></div>
                </div>
            </div>

            <div class="glass-panel glass-panel-hover rounded-xl p-5 flex flex-col justify-between">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-10 h-10 rounded-lg bg-surface-container-high flex items-center justify-center text-on-surface-variant">
                        <span class="material-symbols-outlined">brush</span>
                    </div>
                    <span class="text-xs font-medium text-tertiary bg-tertiary-container/20 px-2 py-1 rounded">Near Completion</span>
                </div>
                <h4 class="text-on-background mb-1" style="font-size:20px;line-height:28px;font-weight:600;">Color Theory Mastery</h4>
                <p class="text-primary mb-4 flex items-center gap-1 text-sm font-semibold">
                    <span class="material-symbols-outlined" style="font-size:14px;">arrow_right_alt</span> Next: Final Project
                </p>
                <div class="w-full bg-surface-variant rounded-full h-1.5 mt-auto">
                    <div class="bg-tertiary-fixed-dim h-1.5 rounded-full" style="width: 85%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Side Column: Overall Progress & Activity (4 cols) -->
    <div class="md:col-span-4 flex flex-col gap-6">

        <!-- Overall Progress -->
        <div class="glass-panel rounded-xl p-6 text-center flex flex-col items-center justify-center relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4">
                <span class="material-symbols-outlined text-outline">more_horiz</span>
            </div>
            <h3 class="text-on-background w-full text-left mb-6" style="font-size:20px;line-height:28px;font-weight:600;">Overall Progress</h3>
            <div class="relative w-40 h-40 flex items-center justify-center mb-4">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" fill="transparent" r="40" stroke="#e0e3e5" stroke-width="8"></circle>
                    <circle class="transition-all duration-1000 ease-out" cx="50" cy="50" fill="transparent" r="40"
                            stroke="#4648d4" stroke-dasharray="251.2" stroke-dashoffset="105.5"
                            stroke-linecap="round" stroke-width="8"></circle>
                </svg>
                <div class="absolute flex flex-col items-center">
                    <span class="text-on-background" style="font-size:48px;line-height:56px;letter-spacing:-0.02em;font-weight:700;">58</span>
                    <span class="text-outline -mt-2 text-xs font-medium">%</span>
                </div>
            </div>
            <p class="text-on-surface-variant text-center max-w-[80%] text-sm">
                You've completed <strong class="text-on-background font-semibold">12 of 21</strong> required modules this semester. Keep it up!
            </p>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="glass-panel rounded-xl p-6 flex-1">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-on-background" style="font-size:20px;line-height:28px;font-weight:600;">Upcoming Deadlines</h3>
                <a class="text-primary hover:underline text-xs font-semibold" href="#">View All</a>
            </div>
            <div class="space-y-4">
                <div class="flex gap-3 items-start group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-surface-container/50 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-secondary-container/20 flex items-center justify-center shrink-0 mt-1">
                        <span class="material-symbols-outlined text-secondary" style="font-size:20px;">assignment_turned_in</span>
                    </div>
                    <div>
                        <h4 class="text-on-background group-hover:text-primary transition-colors text-sm font-semibold">UX Case Study Submission</h4>
                        <p class="text-on-surface-variant text-xs">Due Tomorrow, 11:59 PM</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-surface-container/50 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-error-container/50 flex items-center justify-center shrink-0 mt-1">
                        <span class="material-symbols-outlined text-error" style="font-size:20px;">warning</span>
                    </div>
                    <div>
                        <h4 class="text-on-background group-hover:text-primary transition-colors text-sm font-semibold">React Quiz 3</h4>
                        <p class="text-error text-xs">Overdue by 2 days</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start group cursor-pointer p-2 -mx-2 rounded-lg hover:bg-surface-container/50 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-tertiary-container/20 flex items-center justify-center shrink-0 mt-1">
                        <span class="material-symbols-outlined text-tertiary" style="font-size:20px;">video_library</span>
                    </div>
                    <div>
                        <h4 class="text-on-background group-hover:text-primary transition-colors text-sm font-semibold">New Lecture Available</h4>
                        <p class="text-on-surface-variant text-xs">Color Theory Mastery</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
