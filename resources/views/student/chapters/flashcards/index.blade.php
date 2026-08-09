@extends('layouts.student')

@section('title', 'Flashcards – Chapter ' . $chapterId)
@section('meta-description', 'Practice flashcard sets for this chapter')

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
        box-shadow: 0 10px 30px rgba(70,72,212,0.08);
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')

{{-- Breadcrumb + Header --}}
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10 pt-4">
    <div>
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-3 flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', ['id' => $courseId]) }}" class="hover:text-primary transition-colors">Course {{ $courseId }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Chapter {{ $chapterId }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface font-semibold">Flashcards</span>
        </nav>
        <h1 class="text-on-background" style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;">Chapter Flashcards</h1>
        <p class="text-on-surface-variant text-sm mt-1">Master key concepts for this chapter with interactive study sets.</p>
    </div>
</div>

{{-- Flashcard Sets Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    {{-- Set 1 --}}
    <div class="glass-card rounded-xl p-6 flex flex-col h-full relative overflow-hidden group">
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10 transition-colors"></div>
        <div class="flex justify-between items-start mb-4 relative z-10">
            <div class="bg-primary-container/20 text-primary text-xs font-bold px-3 py-1 rounded-full border border-primary/20">CH{{ $chapterId }}.1</div>
            <button class="text-outline-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined">more_vert</span>
            </button>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-2 relative z-10">Arrays &amp; Strings</h3>
        <p class="text-on-surface-variant text-sm mb-6 flex-1 relative z-10">Memory allocation, indexing, and common string manipulation algorithms.</p>
        <div class="flex items-center justify-between mt-auto relative z-10">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-xl">style</span>
                <span class="text-sm font-semibold">24 Cards</span>
            </div>
            <a href="{{ route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => 1]) }}" class="bg-transparent border border-primary text-primary hover:bg-primary hover:text-on-primary px-4 py-2 rounded-full text-xs font-semibold transition-colors flex items-center gap-2 inline-flex">
                Practice Now <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>

    {{-- Set 2 --}}
    <div class="glass-card rounded-xl p-6 flex flex-col h-full relative overflow-hidden group">
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-secondary/5 rounded-full blur-2xl group-hover:bg-secondary/10 transition-colors"></div>
        <div class="flex justify-between items-start mb-4 relative z-10">
            <div class="bg-secondary-container/20 text-secondary text-xs font-bold px-3 py-1 rounded-full border border-secondary/20">CH{{ $chapterId }}.2</div>
            <button class="text-outline-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined">more_vert</span>
            </button>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-2 relative z-10">Linked Lists</h3>
        <p class="text-on-surface-variant text-sm mb-6 flex-1 relative z-10">Singly, doubly, and circular linked lists. Insertion and deletion logic.</p>
        <div class="flex items-center justify-between mt-auto relative z-10">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-xl">style</span>
                <span class="text-sm font-semibold">18 Cards</span>
            </div>
            <a href="{{ route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => 1]) }}" class="bg-transparent border border-primary text-primary hover:bg-primary hover:text-on-primary px-4 py-2 rounded-full text-xs font-semibold transition-colors flex items-center gap-2 inline-flex">
                Practice Now <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>

    {{-- Set 3 --}}
    <div class="glass-card rounded-xl p-6 flex flex-col h-full relative overflow-hidden group">
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-tertiary/5 rounded-full blur-2xl group-hover:bg-tertiary/10 transition-colors"></div>
        <div class="flex justify-between items-start mb-4 relative z-10">
            <div class="bg-tertiary-container/20 text-tertiary text-xs font-bold px-3 py-1 rounded-full border border-tertiary/20">CH{{ $chapterId }}.3</div>
            <button class="text-outline-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined">more_vert</span>
            </button>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-2 relative z-10">Stacks &amp; Queues</h3>
        <p class="text-on-surface-variant text-sm mb-6 flex-1 relative z-10">LIFO and FIFO principles, implementation using arrays and lists.</p>
        <div class="flex items-center justify-between mt-auto relative z-10">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-xl">style</span>
                <span class="text-sm font-semibold">20 Cards</span>
            </div>
            <a href="{{ route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => 1]) }}" class="bg-transparent border border-primary text-primary hover:bg-primary hover:text-on-primary px-4 py-2 rounded-full text-xs font-semibold transition-colors flex items-center gap-2 inline-flex">
                Practice Now <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>

    {{-- Set 4 --}}
    <div class="glass-card rounded-xl p-6 flex flex-col h-full relative overflow-hidden group">
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10 transition-colors"></div>
        <div class="flex justify-between items-start mb-4 relative z-10">
            <div class="bg-primary-container/20 text-primary text-xs font-bold px-3 py-1 rounded-full border border-primary/20">CH{{ $chapterId }}.4</div>
            <button class="text-outline-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined">more_vert</span>
            </button>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-2 relative z-10">Neural Network Vocab</h3>
        <p class="text-on-surface-variant text-sm mb-6 flex-1 relative z-10">Key terms: neurons, weights, biases, epochs, batches, and learning rate schedules.</p>
        <div class="flex items-center justify-between mt-auto relative z-10">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-xl">style</span>
                <span class="text-sm font-semibold">36 Cards</span>
            </div>
            <a href="{{ route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => 1]) }}" class="bg-transparent border border-primary text-primary hover:bg-primary hover:text-on-primary px-4 py-2 rounded-full text-xs font-semibold transition-colors flex items-center gap-2 inline-flex">
                Practice Now <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>

    {{-- Set 5 --}}
    <div class="glass-card rounded-xl p-6 flex flex-col h-full relative overflow-hidden group">
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-secondary/5 rounded-full blur-2xl group-hover:bg-secondary/10 transition-colors"></div>
        <div class="flex justify-between items-start mb-4 relative z-10">
            <div class="bg-secondary-container/20 text-secondary text-xs font-bold px-3 py-1 rounded-full border border-secondary/20">CH{{ $chapterId }}.5</div>
            <button class="text-outline-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined">more_vert</span>
            </button>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-2 relative z-10">Math Formulas</h3>
        <p class="text-on-surface-variant text-sm mb-6 flex-1 relative z-10">Core equations: softmax, cross-entropy, MSE, gradient update rule.</p>
        <div class="flex items-center justify-between mt-auto relative z-10">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-xl">style</span>
                <span class="text-sm font-semibold">22 Cards</span>
            </div>
            <a href="{{ route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => 1]) }}" class="bg-transparent border border-primary text-primary hover:bg-primary hover:text-on-primary px-4 py-2 rounded-full text-xs font-semibold transition-colors flex items-center gap-2 inline-flex">
                Practice Now <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    </div>


</div>

@endsection
