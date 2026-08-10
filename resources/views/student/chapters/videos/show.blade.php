@extends('layouts.student')

@section('title', 'Video Lesson')

@push('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
    .hover-shadow:hover {
        box-shadow: 0px 10px 30px rgba(70, 72, 212, 0.08);
        transform: translateY(-2px);
    }
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@section('content')
<div class="w-full max-w-[1600px] mx-auto flex-1 flex flex-col xl:flex-row gap-8 pt-4">
    <!-- Primary Learning Area -->
    <div class="flex-1 flex flex-col gap-6">
        <!-- Breadcrumbs -->
        <nav class="flex text-xs text-on-surface-variant items-center gap-2 overflow-x-auto whitespace-nowrap pb-2 scrollbar-hide">
            <a class="hover:text-primary transition-colors" href="{{ route('student.courses') }}">My Courses</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a class="hover:text-primary transition-colors" href="{{ route('student.courses.show', ['id' => $courseId]) }}">Course {{ $courseId }}</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a class="hover:text-primary transition-colors" href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}">Chapter {{ $chapterId }}</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a class="hover:text-primary transition-colors" href="{{ route('student.chapters.videos', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}">Videos</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-on-surface font-medium">Introduction to Neural Networks</span>
        </nav>

        <!-- Video Player Area -->
        <div class="w-full aspect-video bg-black rounded-xl overflow-hidden relative shadow-md group">
            <div class="absolute inset-0 bg-cover bg-center opacity-80 group-hover:opacity-60 transition-opacity duration-500" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBolCo2H6-_iR_cYuHoazt2xHwJDoDlAlkZYgVrGYh1h0jnIRuG_nXCim_h1uSceBeu_BzjxJNyNn66eSIgpR4-y-VWAE6h2JQJPRsHh86nx8ph8hSQMxASMc9APwopcEGX_i71oEkROYqMBOo_7NedHQIIgu5X27yF-u90PKRUnXRHlCGvs6Eis75E_LZ9uxxpvWicgeBlHnRbESThwHGnL5XCwtKExwnC342xl8Qjv-S-lt4ONZc_')"></div>
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <button class="w-20 h-20 rounded-full bg-primary/90 text-white flex items-center justify-center backdrop-blur-md shadow-lg transform group-hover:scale-110 transition-transform duration-300 pointer-events-auto cursor-pointer border border-white/20">
                    <span class="material-symbols-outlined text-4xl ml-2" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                </button>
            </div>
            <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/80 to-transparent flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div class="w-full h-1.5 bg-white/30 rounded-full overflow-hidden cursor-pointer">
                    <div class="h-full bg-primary w-1/3 relative">
                        <div class="absolute right-0 top-1/2 -translate-y-1/2 w-3 h-3 bg-white rounded-full shadow"></div>
                    </div>
                </div>
                <div class="flex justify-between items-center text-white mt-2">
                    <div class="flex items-center gap-4">
                        <button class="hover:text-primary transition-colors"><span class="material-symbols-outlined">pause</span></button>
                        <button class="hover:text-primary transition-colors"><span class="material-symbols-outlined">volume_up</span></button>
                        <span class="text-sm font-medium">12:45 / 45:30</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <button class="hover:text-primary transition-colors"><span class="material-symbols-outlined">closed_caption</span></button>
                        <button class="hover:text-primary transition-colors"><span class="material-symbols-outlined">settings</span></button>
                        <button class="hover:text-primary transition-colors"><span class="material-symbols-outlined">fullscreen</span></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lesson Info -->
        <div class="flex flex-col gap-4 bg-surface-container-lowest p-6 rounded-xl glass-panel relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary to-primary-container"></div>
            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-on-surface mb-2">Introduction to Neural Networks</h1>
                    <p class="text-sm text-on-surface-variant max-w-3xl">In this foundational lesson, we explore the biological inspiration behind artificial neural networks, deconstruct the architecture of a perceptron, and introduce the concept of activation functions and backpropagation.</p>
                </div>
                <button onclick="toggleSaveIcon(this)" class="flex items-center justify-center gap-2 bg-primary/10 text-primary font-semibold text-sm px-6 py-2 rounded-full hover:bg-primary hover:text-white transition-colors shadow-sm whitespace-nowrap">
                    <span class="material-symbols-outlined">bookmark_border</span>
                    <span data-save-label="Save Lesson">Save Lesson</span>
                </button>
            </div>
            <div class="h-px bg-outline-variant/30 w-full my-2"></div>
            <div class="flex items-center gap-4">
                <img class="w-10 h-10 rounded-full object-cover border-2 border-surface-container-highest shadow-sm" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB58RdVU6WnpVB74Ci3p7cEud9I-cdQWrn8hMCp6RwTe9DScw4_5GWhGR3wlc-xfAW0HUS43XFd0X_jUS0RvgaWy22hovzZh5xxFCOmWy_ruKma8LN_gwlOzecMwDyeIq9DWQ-eoWg-t0kpr0YarmofUtCyR5ByIfeD83QftTAfcHSxXMW3swCzYGoSiZfF8-hGbowzyRbUolx6oOJ9ly2ZQN-O8o5AbQfhsNME0IW4GN-4vZMuY25k" alt="Instructor"/>
                <div>
                    <div class="text-sm font-semibold text-on-surface">Dr. Alan Turing</div>
                    <div class="text-xs text-on-surface-variant">Lead Instructor</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar / Secondary Content -->
    <aside class="w-full xl:w-[400px] flex flex-col gap-6">
        <!-- Chapter Playlist -->
        <div class="bg-surface-container-lowest rounded-xl glass-panel overflow-hidden flex flex-col">
            <div class="p-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-low">
                <h3 class="text-sm font-semibold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">format_list_bulleted</span>
                    Chapter Playlist
                </h3>
                <span class="text-xs text-on-surface-variant bg-surface-container py-1 px-2 rounded-md">2/8 Lessons</span>
            </div>
            <div class="flex flex-col max-h-[400px] overflow-y-auto p-2 scrollbar-hide space-y-1">
                <!-- Completed -->
                <a class="flex gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors group items-start" href="#">
                    <div class="mt-1 text-tertiary">
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-on-surface-variant line-through group-hover:text-primary transition-colors">History of AI Paradigms</h4>
                        <p class="text-xs text-on-surface-variant/70 mt-1">18:20 • Completed</p>
                    </div>
                </a>
                <!-- Active -->
                <div class="flex gap-3 p-3 rounded-lg bg-primary/10 border-l-4 border-primary items-start">
                    <div class="mt-1 text-primary relative flex h-5 w-5 items-center justify-center">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-20"></span>
                        <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">play_circle</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-primary">Introduction to Neural Networks</h4>
                        <p class="text-xs text-primary/80 mt-1">45:30 • Currently Watching</p>
                    </div>
                </div>
                <!-- Up Next -->
                <a class="flex gap-3 p-3 rounded-lg hover:bg-surface-container-low transition-colors group items-start" href="#">
                    <div class="mt-1 text-outline-variant">
                        <span class="material-symbols-outlined text-[20px]">lock</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-on-surface group-hover:text-primary transition-colors">Forward Propagation Mechanics</h4>
                        <p class="text-xs text-on-surface-variant mt-1">32:15 • Up Next</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Resources Section -->
        <div class="bg-surface-container-lowest rounded-xl glass-panel p-6 flex flex-col gap-4">
            <h3 class="text-sm font-semibold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary-container">topic</span>
                Lesson Resources
            </h3>
            <div class="grid grid-cols-1 gap-3">
                <a class="flex items-center p-3 rounded-lg border border-outline-variant/20 hover:border-primary/50 hover:bg-surface-container-low transition-all group" href="#">
                    <div class="w-10 h-10 rounded-md bg-error/10 text-error flex items-center justify-center mr-3">
                        <span class="material-symbols-outlined">quiz</span>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-on-surface group-hover:text-primary transition-colors">Chapter Quiz</div>
                        <div class="text-xs text-on-surface-variant">Test your knowledge (10 mins)</div>
                    </div>
                    <span class="material-symbols-outlined text-outline-variant group-hover:text-primary transition-colors">arrow_forward_ios</span>
                </a>
            </div>
        </div>
    </aside>
</div>
@endsection
