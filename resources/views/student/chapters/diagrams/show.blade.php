@extends('layouts.student')

@section('title', 'Diagram Detail')

@push('styles')
<style>
    .glass-panel {
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .glass-panel-hover:hover {
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
</style>
@endpush

@section('content')
<div class="w-full flex-1 pt-4">
    <!-- Header Section -->
    <div class="mb-8">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', ['id' => $courseId]) }}" class="hover:text-primary transition-colors">Course {{ $courseId }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Chapter {{ $chapterId }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.diagrams', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Diagrams</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface font-semibold">Diagram Detail</span>
        </nav>
        <!-- Page Title -->
        <h2 class="text-3xl font-bold text-on-surface">Diagram Detail: Cell Mitosis Cycle</h2>
    </div>

    <!-- Two-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left Column (Main) -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            <!-- Diagram Preview -->
            <div class="glass-panel rounded-xl p-2 glass-panel-hover">
                <div class="bg-surface-container-low rounded-lg overflow-hidden flex items-center justify-center relative group min-h-[400px]">
                    <img class="w-full h-auto max-h-[600px] object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuANcJEkLR7qhqmvXHz0bpKcUpW2sRw4daNUZhr4BCnJJei-5PC5hUB00sJGJTxeYJxv0HchrH-HpDvby6wOLBaERrU1fc9YVmke30v8WH5wU__wOjxU9VrLvTAE6z9eH1rFPJmiPZ-j7AQPHggzlz0aZLjEXu5kMbIHm3uJ4XnC5jGQzQP_MGqpNM2LlDihMowvObdt634GBocCqxMYEo-nste1Kk_EbUDVAm05tOhmcRPgihXmxnI5" alt="Diagram" />
                    <!-- Overlay actions -->
                    <div class="absolute inset-0 bg-inverse-surface/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-4">
                        <button class="bg-surface-container-lowest text-on-surface p-3 rounded-full shadow-lg hover:text-primary hover:scale-105 transition-all">
                            <span class="material-symbols-outlined">zoom_in</span>
                        </button>
                        <button class="bg-surface-container-lowest text-on-surface p-3 rounded-full shadow-lg hover:text-primary hover:scale-105 transition-all">
                            <span class="material-symbols-outlined">download</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Description Section -->
            <div class="glass-panel rounded-xl p-6 glass-panel-hover">
                <h3 class="text-xl font-semibold mb-4 border-b border-surface-variant/50 pb-2">Description</h3>
                <div class="prose prose-sm max-w-none text-on-surface-variant space-y-4">
                    <p>This detailed diagram illustrates the complete cycle of cell mitosis, covering Interphase, Prophase, Metaphase, Anaphase, and Telophase. It is designed for introductory biology modules to help students visualize cellular division.</p>
                </div>
            </div>
        </div>

        <!-- Right Column (Sidebar) -->
        <div class="lg:col-span-4 flex flex-col gap-6">
            <!-- Asset Info Card -->
            <div class="glass-panel rounded-xl p-6 glass-panel-hover">
                <div class="flex items-center gap-2 mb-4 border-b border-surface-variant/50 pb-2">
                    <span class="material-symbols-outlined text-on-surface-variant text-xl">info</span>
                    <h3 class="text-xl font-semibold">Asset Info</h3>
                </div>
                <ul class="space-y-3">
                    <li class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-on-surface-variant">File Type</span>
                        <span class="text-sm font-medium bg-surface-container-high px-2 py-0.5 rounded">SVG Vector</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-on-surface-variant">Dimensions</span>
                        <span class="text-sm font-medium">1920 x 1080 px</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
