@extends('layouts.student')

@section('title', 'Study Notes – Chapter ' . $chapterId)
@section('meta-description', 'Browse and review all study notes for this chapter')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .glass-card {
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.4);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        box-shadow: 0 10px 30px rgba(99,102,241,0.08);
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')

{{-- Breadcrumb + Header --}}
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 pt-4">
    <div>
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-3 flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="text-outline-variant">&rsaquo;</span>
            <a href="{{ route('student.courses.show', ['id' => $courseId]) }}" class="hover:text-primary transition-colors">Course {{ $courseId }}</a>
            <span class="text-outline-variant">&rsaquo;</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Chapter {{ $chapterId }}</a>
            <span class="text-outline-variant">&rsaquo;</span>
            <span class="text-on-surface font-semibold">Study Notes</span>
        </nav>
        <h1 class="text-on-background" style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;">Study Notes</h1>
        <p class="text-on-surface-variant text-sm mt-1">You have <span class="font-semibold text-primary">24</span> notes for this chapter.</p>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 items-center bg-surface-container-low p-2 rounded-xl border border-outline-variant/30">
        <div class="relative">
            <select class="appearance-none bg-surface border border-outline-variant/50 text-on-surface text-sm rounded-lg py-2 pl-4 pr-10 focus:ring-2 focus:ring-primary transition-all cursor-pointer">
                <option>All Courses</option>
                <option>Computer Science 101</option>
                <option>UI/UX Masterclass</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant text-sm">expand_more</span>
        </div>
        <div class="relative">
            <select class="appearance-none bg-surface border border-outline-variant/50 text-on-surface text-sm rounded-lg py-2 pl-4 pr-10 focus:ring-2 focus:ring-primary transition-all cursor-pointer">
                <option>All Chapters</option>
                <option selected>Chapter {{ $chapterId }}</option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant text-sm">expand_more</span>
        </div>
        <button class="bg-primary-container text-on-primary-container text-sm font-semibold py-2 px-4 rounded-lg flex items-center gap-2 hover:bg-primary hover:text-on-primary transition-colors">
            <span class="material-symbols-outlined text-sm">filter_list</span> Filter
        </button>
    </div>
</div>

{{-- Notes Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

    {{-- Note Card 1 --}}
    <div class="glass-card rounded-2xl p-6 flex flex-col h-full relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-bl-full -z-10 transition-transform group-hover:scale-110"></div>
        <div class="flex justify-between items-start mb-4">
            <span class="bg-secondary-container/20 text-secondary text-xs font-semibold px-3 py-1 rounded-full border border-secondary/20">CS101</span>
            <div class="flex items-center gap-1">
                <button onclick="toggleSaveIcon(this)" class="text-outline hover:text-primary transition-colors" title="Save note">
                    <span class="material-symbols-outlined">bookmark_border</span>
                </button>
                <button class="text-outline hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">more_vert</span>
                </button>
            </div>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-1 line-clamp-2">Introduction to Data Structures &amp; Algorithms</h3>
        <p class="text-on-surface-variant text-xs mb-4 uppercase tracking-wide font-medium">Chapter {{ $chapterId }}: Arrays &amp; Strings</p>
        <div class="bg-surface-bright border border-outline-variant/20 rounded-xl p-4 mb-6 flex-1 shadow-inner">
            <p class="text-on-surface-variant text-xs line-clamp-4">An array is a collection of items stored at contiguous memory locations. The idea is to declare multiple items of the same type together. This makes it easier to calculate the position of each element by simply adding an offset to a base value. Strings are essentially arrays of characters…</p>
        </div>
        <div class="flex justify-between items-center pt-2 border-t border-outline-variant/20">
            <span class="text-on-surface-variant text-xs flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:16px;">schedule</span> 2 days ago
            </span>
            <a href="{{ route('student.chapters.notes.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'noteId' => 1]) }}" class="text-primary text-xs font-semibold flex items-center gap-1 group-hover:gap-2 transition-all">
                View Note <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>

    {{-- Note Card 2 --}}
    <div class="glass-card rounded-2xl p-6 flex flex-col h-full relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-32 h-32 bg-tertiary/5 rounded-bl-full -z-10 transition-transform group-hover:scale-110"></div>
        <div class="flex justify-between items-start mb-4">
            <span class="bg-tertiary-container/20 text-tertiary text-xs font-semibold px-3 py-1 rounded-full border border-tertiary/20">ML-401</span>
            <div class="flex items-center gap-1">
                <button onclick="toggleSaveIcon(this)" class="text-outline hover:text-primary transition-colors" title="Save note">
                    <span class="material-symbols-outlined">bookmark_border</span>
                </button>
                <button class="text-outline hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">more_vert</span>
                </button>
            </div>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-1 line-clamp-2">Backpropagation &amp; Gradient Descent</h3>
        <p class="text-on-surface-variant text-xs mb-4 uppercase tracking-wide font-medium">Chapter {{ $chapterId }}: Neural Networks</p>
        <div class="bg-surface-bright border border-outline-variant/20 rounded-xl p-4 mb-6 flex-1 shadow-inner">
            <p class="text-on-surface-variant text-xs line-clamp-4">Backpropagation is the algorithm used to efficiently compute gradients in a neural network. It applies the chain rule of calculus iteratively from the output layer back to the input layer, adjusting weights to minimize the loss function…</p>
        </div>
        <div class="flex justify-between items-center pt-2 border-t border-outline-variant/20">
            <span class="text-on-surface-variant text-xs flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:16px;">schedule</span> Last week
            </span>
            <a href="{{ route('student.chapters.notes.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'noteId' => 1]) }}" class="text-primary text-xs font-semibold flex items-center gap-1 group-hover:gap-2 transition-all">
                View Note <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>

    {{-- Note Card 3 – Draft --}}
    <div class="glass-card rounded-2xl p-6 flex flex-col h-full relative overflow-hidden group border border-primary/20 shadow-[0_0_15px_rgba(70,72,212,0.07)]">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-primary/8 rounded-full blur-2xl -z-10"></div>
        <div class="flex justify-between items-start mb-4">
            <span class="bg-secondary-container/20 text-secondary text-xs font-semibold px-3 py-1 rounded-full border border-secondary/20">CS101</span>
            <div class="flex items-center gap-1">
                <button onclick="toggleSaveIcon(this)" class="text-outline hover:text-primary transition-colors" title="Save note">
                    <span class="material-symbols-outlined">bookmark_border</span>
                </button>
                <button class="text-outline hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">more_vert</span>
                </button>
            </div>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-1 line-clamp-2">Activation Functions: Sigmoid vs ReLU</h3>
        <p class="text-on-surface-variant text-xs mb-4 uppercase tracking-wide font-medium">Chapter {{ $chapterId }}: Algorithm Analysis</p>
        <div class="bg-surface-bright border border-outline-variant/20 rounded-xl p-4 mb-6 flex-1 shadow-inner relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary rounded-r"></div>
            <p class="text-on-surface-variant text-xs line-clamp-4 pl-3">Sigmoid squashes values to (0,1) — historically popular but prone to vanishing gradients. ReLU simply outputs max(0,x) and avoids this issue, making it the preferred choice for hidden layers in deep networks today…</p>
        </div>
        <div class="flex justify-between items-center pt-2 border-t border-outline-variant/20">
            <span class="text-on-surface-variant text-xs flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:16px;">edit</span> Draft
            </span>
            <a href="{{ route('student.chapters.notes.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'noteId' => 1]) }}" class="text-primary text-xs font-semibold flex items-center gap-1 group-hover:gap-2 transition-all">
                Continue Edit <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>

    {{-- Note Card 4 --}}
    <div class="glass-card rounded-2xl p-6 flex flex-col h-full relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-32 h-32 bg-secondary/5 rounded-bl-full -z-10 transition-transform group-hover:scale-110"></div>
        <div class="flex justify-between items-start mb-4">
            <span class="bg-primary-container/20 text-primary text-xs font-semibold px-3 py-1 rounded-full border border-primary/20">ML-401</span>
            <div class="flex items-center gap-1">
                <button onclick="toggleSaveIcon(this)" class="text-outline hover:text-primary transition-colors" title="Save note">
                    <span class="material-symbols-outlined">bookmark_border</span>
                </button>
                <button class="text-outline hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">more_vert</span>
                </button>
            </div>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-1 line-clamp-2">Convolutional Neural Networks Overview</h3>
        <p class="text-on-surface-variant text-xs mb-4 uppercase tracking-wide font-medium">Chapter {{ $chapterId }}: CNN Architecture</p>
        <div class="bg-surface-bright border border-outline-variant/20 rounded-xl p-4 mb-6 flex-1 shadow-inner">
            <p class="text-on-surface-variant text-xs line-clamp-4">CNNs use learned spatial hierarchies of features through convolution, pooling, and fully connected layers. Particularly effective for image recognition tasks due to parameter sharing and translation invariance properties…</p>
        </div>
        <div class="flex justify-between items-center pt-2 border-t border-outline-variant/20">
            <span class="text-on-surface-variant text-xs flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:16px;">schedule</span> 3 days ago
            </span>
            <a href="{{ route('student.chapters.notes.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'noteId' => 1]) }}" class="text-primary text-xs font-semibold flex items-center gap-1 group-hover:gap-2 transition-all">
                View Note <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>

    {{-- Note Card 5 --}}
    <div class="glass-card rounded-2xl p-6 flex flex-col h-full relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-bl-full -z-10 transition-transform group-hover:scale-110"></div>
        <div class="flex justify-between items-start mb-4">
            <span class="bg-tertiary-container/20 text-tertiary text-xs font-semibold px-3 py-1 rounded-full border border-tertiary/20">Design</span>
            <div class="flex items-center gap-1">
                <button onclick="toggleSaveIcon(this)" class="text-outline hover:text-primary transition-colors" title="Save note">
                    <span class="material-symbols-outlined">bookmark_border</span>
                </button>
                <button class="text-outline hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">more_vert</span>
                </button>
            </div>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-1 line-clamp-2">Loss Functions: Cross Entropy vs MSE</h3>
        <p class="text-on-surface-variant text-xs mb-4 uppercase tracking-wide font-medium">Chapter {{ $chapterId }}: Training Objectives</p>
        <div class="bg-surface-bright border border-outline-variant/20 rounded-xl p-4 mb-6 flex-1 shadow-inner">
            <p class="text-on-surface-variant text-xs line-clamp-4">Cross-entropy is used for classification tasks as it penalizes confident wrong predictions heavily. MSE works better for regression. Choosing the right loss function is a critical design decision in model architecture…</p>
        </div>
        <div class="flex justify-between items-center pt-2 border-t border-outline-variant/20">
            <span class="text-on-surface-variant text-xs flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:16px;">schedule</span> 5 days ago
            </span>
            <a href="{{ route('student.chapters.notes.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'noteId' => 1]) }}" class="text-primary text-xs font-semibold flex items-center gap-1 group-hover:gap-2 transition-all">
                View Note <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>

    {{-- Add New Note CTA --}}
    <div class="rounded-2xl border-2 border-dashed border-outline-variant/50 hover:border-primary/50 p-6 flex flex-col items-center justify-center gap-3 cursor-pointer transition-all group h-full min-h-[200px]">
        <div class="w-12 h-12 rounded-full bg-primary/8 group-hover:bg-primary/15 flex items-center justify-center transition-colors">
            <span class="material-symbols-outlined text-primary" style="font-size:24px;">add</span>
        </div>
        <p class="text-on-surface-variant group-hover:text-primary text-sm font-semibold transition-colors">Add New Note</p>
    </div>

</div>

@endsection
