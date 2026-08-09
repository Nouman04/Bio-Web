@extends('layouts.student')

@section('title', 'Study Note')

@push('styles')
<style>
    .soft-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
    
    .hover-lift:hover {
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }

    .primary-gradient {
        background: linear-gradient(135deg, var(--tw-colors-primary), #6063ee);
    }
</style>
@endpush

@section('content')
<div class="w-full grid grid-cols-1 xl:grid-cols-12 gap-6 pt-4">
    <!-- Central Reading Area -->
    <article class="xl:col-span-8 2xl:col-span-9 flex flex-col gap-6">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', ['id' => $courseId]) }}" class="hover:text-primary transition-colors">Course {{ $courseId }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Chapter {{ $chapterId }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.notes', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Study Notes</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface">Note Details</span>
        </nav>

        <!-- Header Section -->
        <header class="flex flex-col gap-4 pb-4 border-b border-on-surface/5">
            <div class="flex justify-between items-start gap-4 flex-wrap">
                <div>
                    <span class="inline-block px-2 py-1 bg-secondary-container/20 text-secondary text-xs rounded mb-2">Chapter {{ $chapterId }}</span>
                    <h1 class="text-3xl text-on-surface font-bold tracking-tight">Introduction to Data Structures &amp; Algorithms</h1>
                    <p class="text-sm text-on-surface-variant mt-2">Last edited 2 days ago</p>
                </div>
            </div>
            
            <!-- Action Bar -->
            <div class="flex flex-wrap items-center gap-3 pt-4">
                <button class="primary-gradient text-on-primary text-sm py-2 px-4 rounded-full shadow-sm hover:shadow-md transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                    Edit Note
                </button>
                <button class="bg-surface border border-outline-variant text-on-surface text-sm py-2 px-4 rounded-full hover:bg-surface-container-low transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                    Download PDF
                </button>
            </div>
        </header>

        <!-- Rich Text Content -->
        <div class="soft-card p-6 md:p-10 rounded-xl hover-lift space-y-6 text-base text-on-surface leading-relaxed">
            <p>Understanding data structures is fundamental to writing efficient software. They are essentially specialized formats for organizing, processing, retrieving, and storing data. This section dives into the foundational concepts, focusing specifically on memory allocation and contiguous structures.</p>
            
            <h2 class="text-2xl font-semibold text-on-surface mt-8 mb-4">1. The Array Concept</h2>
            <p>An array is perhaps the simplest and most widely used data structure. It represents a collection of items stored at contiguous memory locations. The idea is to store multiple items of the same type together.</p>
            
            <ul class="list-disc pl-6 space-y-2 text-on-surface-variant">
                <li><strong>Contiguous Memory:</strong> Elements are stored side-by-side in RAM.</li>
                <li><strong>O(1) Access:</strong> Because the size of each element is known, calculating the address of the <code class="bg-surface-container-high px-1 py-0.5 rounded text-sm text-primary">ith</code> element is a simple multiplication and addition operation.</li>
                <li><strong>Fixed Size (typically):</strong> In many languages, the size of an array must be declared upon initialization.</li>
            </ul>
        </div>
    </article>

    <!-- Sidebar (Right) -->
    <aside class="xl:col-span-4 2xl:col-span-3 flex flex-col gap-6">
        <!-- Quick Navigation -->
        <div class="soft-card p-6 rounded-xl hover-lift">
            <h3 class="text-xl font-semibold text-on-surface mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">list</span>
                Quick Navigation
            </h3>
            <ul class="space-y-3 text-sm">
                <li>
                    <a class="text-on-surface-variant hover:text-primary transition-colors flex items-center gap-2 border-l-2 border-primary pl-2" href="#">
                        1. The Array Concept
                    </a>
                </li>
            </ul>
        </div>
    </aside>
</div>
@endsection
