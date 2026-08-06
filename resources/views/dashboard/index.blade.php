@extends('layouts.app')

@section('title', 'Dashboard')
@section('meta-description', 'EduAdmin Dashboard - Overview of your platform stats and analytics.')

@section('page-title', 'Dashboard Overview')
@section('page-subtitle', "Here's what's happening with your platform today.")

@section('content')

    {{-- ── Overview Stats Header ──────────────────────────────────────── --}}
    <div class="mb-6 flex justify-between items-end">
        <h3 class="text-xl font-semibold text-on-background dark:text-white">Overview Stats</h3>
        <span class="text-xs text-on-surface-variant dark:text-slate-400">
            {{ now()->format('l, d M Y') }}
        </span>
    </div>

    {{-- ── Stat Cards Grid ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">

        {{-- Card: Categories --}}
        <div class="stat-card p-6 rounded-3xl shadow-sm bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 relative overflow-hidden flex flex-col justify-between h-40">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-primary/10 rounded-full opacity-50"></div>
            <div class="flex justify-between items-start relative z-10">
                <div class="w-12 h-12 rounded-2xl icon-bg-indigo flex items-center justify-center text-white shadow-md">
                    <i class="fa-solid fa-shapes text-xl"></i>
                </div>
            </div>
            <div class="relative z-10 mt-auto">
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider mb-1">Total Categories</p>
                <div class="flex items-baseline gap-3">
                    <span class="text-3xl font-bold text-on-background dark:text-white">128</span>
                    <span class="text-xs font-medium text-tertiary dark:text-tertiary-fixed-dim bg-tertiary-container/20 dark:bg-tertiary-container/40 px-2 py-0.5 rounded-full">+12%</span>
                </div>
            </div>
        </div>

        {{-- Card: Courses --}}
        <div class="stat-card p-6 rounded-3xl shadow-sm bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 relative overflow-hidden flex flex-col justify-between h-40">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-secondary/10 rounded-full opacity-50"></div>
            <div class="flex justify-between items-start relative z-10">
                <div class="w-12 h-12 rounded-2xl icon-bg-orange flex items-center justify-center text-white shadow-md">
                    <i class="fa-solid fa-graduation-cap text-xl"></i>
                </div>
            </div>
            <div class="relative z-10 mt-auto">
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider mb-1">Total Courses</p>
                <div class="flex items-baseline gap-3">
                    <span class="text-3xl font-bold text-on-background dark:text-white">45</span>
                    <span class="text-xs font-medium text-tertiary dark:text-tertiary-fixed-dim bg-tertiary-container/20 dark:bg-tertiary-container/40 px-2 py-0.5 rounded-full">+3</span>
                </div>
            </div>
        </div>

        {{-- Card: Chapters --}}
        <div class="stat-card p-6 rounded-3xl shadow-sm bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 relative overflow-hidden flex flex-col justify-between h-40">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-tertiary/10 rounded-full opacity-50"></div>
            <div class="flex justify-between items-start relative z-10">
                <div class="w-12 h-12 rounded-2xl icon-bg-teal flex items-center justify-center text-white shadow-md">
                    <i class="fa-solid fa-book-open-reader text-xl"></i>
                </div>
            </div>
            <div class="relative z-10 mt-auto">
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider mb-1">Total Chapters</p>
                <div class="flex items-baseline gap-3">
                    <span class="text-3xl font-bold text-on-background dark:text-white">320</span>
                    <span class="text-xs font-medium text-tertiary dark:text-tertiary-fixed-dim bg-tertiary-container/20 dark:bg-tertiary-container/40 px-2 py-0.5 rounded-full">+24</span>
                </div>
            </div>
        </div>

        {{-- Card: Topics --}}
        <div class="stat-card p-6 rounded-3xl shadow-sm bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 relative overflow-hidden flex flex-col justify-between h-40">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-primary/10 rounded-full opacity-50"></div>
            <div class="flex justify-between items-start relative z-10">
                <div class="w-12 h-12 rounded-2xl icon-bg-blue flex items-center justify-center text-white shadow-md">
                    <i class="fa-regular fa-folder-open text-xl"></i>
                </div>
            </div>
            <div class="relative z-10 mt-auto">
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider mb-1">Total Topics</p>
                <div class="flex items-baseline gap-3">
                    <span class="text-3xl font-bold text-on-background dark:text-white">1,450</span>
                </div>
            </div>
        </div>

        {{-- Card: Questions --}}
        <div class="stat-card p-6 rounded-3xl shadow-sm bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 relative overflow-hidden flex flex-col justify-between h-40">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-error/10 rounded-full opacity-50"></div>
            <div class="flex justify-between items-start relative z-10">
                <div class="w-12 h-12 rounded-2xl icon-bg-rose flex items-center justify-center text-white shadow-md">
                    <i class="fa-regular fa-circle-question text-xl"></i>
                </div>
            </div>
            <div class="relative z-10 mt-auto">
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider mb-1">Total Questions</p>
                <div class="flex items-baseline gap-3">
                    <span class="text-3xl font-bold text-on-background dark:text-white">5,230</span>
                    <span class="text-xs font-medium text-tertiary dark:text-tertiary-fixed-dim bg-tertiary-container/20 dark:bg-tertiary-container/40 px-2 py-0.5 rounded-full">+156</span>
                </div>
            </div>
        </div>

        {{-- Card: Quizzes --}}
        <div class="stat-card p-6 rounded-3xl shadow-sm bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 relative overflow-hidden flex flex-col justify-between h-40">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-secondary/10 rounded-full opacity-50"></div>
            <div class="flex justify-between items-start relative z-10">
                <div class="w-12 h-12 rounded-2xl icon-bg-amber flex items-center justify-center text-white shadow-md">
                    <i class="fa-solid fa-list-check text-xl"></i>
                </div>
            </div>
            <div class="relative z-10 mt-auto">
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider mb-1">Total Quizzes</p>
                <div class="flex items-baseline gap-3">
                    <span class="text-3xl font-bold text-on-background dark:text-white">150</span>
                </div>
            </div>
        </div>

    </div>
    {{-- ── End Stat Cards ─────────────────────────────────────────────── --}}

    {{-- ── Charts Section ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

        {{-- Monthly Revenue Bar Chart --}}
        <div class="bg-surface-container-lowest dark:bg-slate-800 p-8 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h4 class="text-lg font-bold text-on-background dark:text-white">Monthly Revenue</h4>
                    <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-1">Revenue over the last 6 months</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-primary dark:bg-primary-fixed-dim"></span>
                    <span class="text-sm font-medium text-on-background dark:text-slate-300">Revenue</span>
                </div>
            </div>
            <div class="relative h-64 w-full flex items-end justify-between pb-6 pt-4">
                {{-- Y Axis Labels --}}
                <div class="absolute left-0 top-0 h-full flex flex-col justify-between text-xs text-on-surface-variant dark:text-slate-400 font-medium pb-6 w-8 text-right pr-2">
                    <span>$5k</span><span>$4k</span><span>$3k</span><span>$2k</span><span>$1k</span><span>$0</span>
                </div>
                {{-- Grid Lines --}}
                <div class="absolute left-8 right-0 top-0 h-full flex flex-col justify-between pb-6 z-0">
                    <div class="border-b border-outline-variant/20 dark:border-slate-700 w-full"></div>
                    <div class="border-b border-outline-variant/20 dark:border-slate-700 w-full"></div>
                    <div class="border-b border-outline-variant/20 dark:border-slate-700 w-full"></div>
                    <div class="border-b border-outline-variant/20 dark:border-slate-700 w-full"></div>
                    <div class="border-b border-outline-variant/20 dark:border-slate-700 w-full"></div>
                    <div class="border-b border-outline-variant/40 dark:border-slate-600 w-full"></div>
                </div>
                {{-- Bars --}}
                <div class="relative z-10 flex justify-between items-end w-full h-full pl-12 pr-4 space-x-2 sm:space-x-4">
                    @php
                        $months = [
                            ['label' => 'Jan', 'height' => '25%', 'active' => false],
                            ['label' => 'Feb', 'height' => '40%', 'active' => false],
                            ['label' => 'Mar', 'height' => '35%', 'active' => false],
                            ['label' => 'Apr', 'height' => '65%', 'active' => false],
                            ['label' => 'May', 'height' => '55%', 'active' => false],
                            ['label' => 'Jun', 'height' => '90%', 'active' => true],
                        ];
                    @endphp
                    @foreach($months as $month)
                    <div class="flex flex-col items-center flex-1 group">
                        <div class="w-full max-w-[32px] {{ $month['active'] ? 'bg-primary dark:bg-primary-fixed-dim shadow-md shadow-primary/20' : 'bg-primary-fixed dark:bg-primary/30 group-hover:bg-primary-fixed-dim' }} transition-all duration-300 rounded-full relative bar-chart-bar"
                             style="height: {{ $month['height'] }};"></div>
                        <span class="text-xs {{ $month['active'] ? 'text-primary dark:text-primary-fixed-dim font-bold' : 'text-on-surface-variant dark:text-slate-400' }} mt-3 font-medium">
                            {{ $month['label'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Student Subscriptions Line Chart --}}
        <div class="bg-surface-container-lowest dark:bg-slate-800 p-8 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700 relative overflow-hidden">
            <div class="absolute bottom-0 left-0 right-0 h-48 bg-gradient-to-t from-tertiary/10 dark:from-tertiary/20 to-transparent z-0 pointer-events-none"></div>
            <div class="flex justify-between items-start mb-8 relative z-10">
                <div>
                    <h4 class="text-lg font-bold text-on-background dark:text-white">Student Subscriptions</h4>
                    <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-1">Growth over the last 4 weeks</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-tertiary dark:bg-tertiary-fixed-dim"></span>
                    <span class="text-sm font-medium text-on-background dark:text-slate-300">Active</span>
                </div>
            </div>
            <div class="relative h-64 w-full flex items-end pb-6 pt-4 z-10">
                {{-- Y Axis --}}
                <div class="absolute left-0 top-0 h-full flex flex-col justify-between text-xs text-on-surface-variant dark:text-slate-400 font-medium pb-6 w-8 text-right pr-2">
                    <span>800</span><span>600</span><span>400</span><span>200</span><span>100</span><span>0</span>
                </div>
                {{-- Grid Lines --}}
                <div class="absolute left-8 right-0 top-0 h-full flex flex-col justify-between pb-6 z-0">
                    <div class="border-b border-outline-variant/20 dark:border-slate-700 w-full"></div>
                    <div class="border-b border-outline-variant/20 dark:border-slate-700 w-full"></div>
                    <div class="border-b border-outline-variant/20 dark:border-slate-700 w-full"></div>
                    <div class="border-b border-outline-variant/20 dark:border-slate-700 w-full"></div>
                    <div class="border-b border-outline-variant/20 dark:border-slate-700 w-full"></div>
                    <div class="border-b border-outline-variant/40 dark:border-slate-600 w-full"></div>
                </div>
                {{-- SVG Line Chart --}}
                <div class="absolute left-12 right-4 top-4 bottom-12 z-20">
                    <svg width="100%" height="100%" viewBox="0 0 100 100" preserveAspectRatio="none" class="overflow-visible">
                        <path class="line-chart-path drop-shadow-[0_8px_8px_rgba(0,108,73,0.4)] dark:drop-shadow-[0_8px_8px_rgba(78,222,163,0.4)]"
                              d="M 0 90 C 20 90, 20 80, 33 75 C 50 65, 55 50, 66 40 C 75 30, 80 10, 100 5"
                              fill="none" stroke="#4edea3" stroke-width="3" stroke-linecap="round"/>
                        <circle cx="0"   cy="90" r="3" fill="#1e293b" stroke="#4edea3" stroke-width="2" class="dark:fill-slate-800"/>
                        <circle cx="33"  cy="75" r="3" fill="#1e293b" stroke="#4edea3" stroke-width="2" class="dark:fill-slate-800"/>
                        <circle cx="66"  cy="40" r="3" fill="#1e293b" stroke="#4edea3" stroke-width="2" class="dark:fill-slate-800"/>
                        <circle cx="100" cy="5"  r="3" fill="#1e293b" stroke="#4edea3" stroke-width="2" class="dark:fill-slate-800"/>
                    </svg>
                </div>
                {{-- X Axis Labels --}}
                <div class="absolute bottom-0 left-12 right-4 flex justify-between text-xs font-medium">
                    <span class="text-on-surface-variant dark:text-slate-400 -translate-x-1/2">Week 1</span>
                    <span class="text-on-surface-variant dark:text-slate-400 translate-x-[15%]">Week 2</span>
                    <span class="text-on-surface-variant dark:text-slate-400 translate-x-[40%]">Week 3</span>
                    <span class="text-tertiary dark:text-tertiary-fixed-dim font-bold translate-x-1/2">Week 4</span>
                </div>
            </div>
        </div>

    </div>
    {{-- ── End Charts Section ──────────────────────────────────────────── --}}

@endsection
