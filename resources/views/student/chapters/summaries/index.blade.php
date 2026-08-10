@extends('layouts.student')

@section('title', 'Learning Summaries – Chapter ' . $chapterId)
@section('meta-description', 'Review condensed takeaways and key concepts for this chapter')

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
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 pt-4">
    <div>
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-3 flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', ['id' => $courseId]) }}" class="hover:text-primary transition-colors">Course {{ $courseId }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Chapter {{ $chapterId }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface font-semibold">Summaries</span>
        </nav>
        <h1 class="text-on-background" style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;">Learning Summaries</h1>
        <p class="text-on-surface-variant text-base mt-1 max-w-2xl">Review condensed takeaways and key concepts from Chapter {{ $chapterId }} to reinforce your knowledge.</p>
    </div>

    {{-- View Toggle --}}
    <div class="flex flex-wrap gap-4 items-center shrink-0">
        <div class="flex items-center bg-surface-container-lowest border border-outline-variant rounded-lg p-1">
            <button class="px-4 py-1.5 bg-surface-container-low text-primary rounded text-sm font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:18px;">grid_view</span> Grid
            </button>
            <button class="px-4 py-1.5 text-on-surface-variant hover:text-on-surface rounded text-sm font-semibold flex items-center gap-2 transition-colors">
                <span class="material-symbols-outlined" style="font-size:18px;">list</span> List
            </button>
        </div>
    </div>
</div>

{{-- Summary Cards Grid (Bento Style) --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    {{-- Card 1 --}}
    <article class="glass-card rounded-2xl p-6 flex flex-col h-full group">
        <div class="flex justify-between items-start mb-4">
            <div class="flex gap-2">
                <span class="px-2.5 py-1 bg-tertiary-container/20 text-tertiary rounded text-xs font-bold tracking-wider uppercase">ML-401</span>
                <span class="px-2.5 py-1 bg-surface-container-highest text-on-surface-variant rounded text-xs font-bold tracking-wider uppercase">Ch {{ $chapterId }}</span>
            </div>
            <button onclick="toggleSaveIcon(this)" class="text-on-surface-variant hover:text-primary transition-colors" title="Save summary">
                <span class="material-symbols-outlined">bookmark_border</span>
            </button>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-2 group-hover:text-primary transition-colors">Neural Network Architectures</h3>
        <p class="text-on-surface-variant text-sm mb-6 line-clamp-3 flex-1">
            Overview of feedforward, convolutional, and recurrent architectures. Key takeaway: architecture choice depends on data modality — images → CNN, sequences → RNN/LSTM, general → MLP.
        </p>
        <div class="pt-4 border-t border-outline-variant/30 flex items-center justify-between mt-auto">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined" style="font-size:16px;">schedule</span>
                <span class="text-xs font-medium">8 min read</span>
            </div>
            <a href="{{ route('student.chapters.summaries.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'summaryId' => 1]) }}" class="text-primary text-xs font-semibold flex items-center gap-1 hover:gap-2 transition-all">
                Review <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>
    </article>

    {{-- Card 2 --}}
    <article class="glass-card rounded-2xl p-6 flex flex-col h-full group">
        <div class="flex justify-between items-start mb-4">
            <div class="flex gap-2">
                <span class="px-2.5 py-1 bg-primary-container/20 text-primary rounded text-xs font-bold tracking-wider uppercase">CS101</span>
                <span class="px-2.5 py-1 bg-surface-container-highest text-on-surface-variant rounded text-xs font-bold tracking-wider uppercase">Ch {{ $chapterId }}</span>
            </div>
            <button onclick="toggleSaveIcon(this)" class="is-saved text-primary transition-colors" title="Save summary">
                <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">bookmark</span>
            </button>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-2 group-hover:text-primary transition-colors">Activation Functions Deep Dive</h3>
        <p class="text-on-surface-variant text-sm mb-6 line-clamp-3 flex-1">
            Sigmoid saturates at extremes causing vanishing gradients. ReLU avoids this but can "die." Leaky ReLU and ELU are practical alternatives for deep architectures.
        </p>
        <div class="pt-4 border-t border-outline-variant/30 flex items-center justify-between mt-auto">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined" style="font-size:16px;">schedule</span>
                <span class="text-xs font-medium">5 min read</span>
            </div>
            <a href="{{ route('student.chapters.summaries.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'summaryId' => 1]) }}" class="text-primary text-xs font-semibold flex items-center gap-1 hover:gap-2 transition-all">
                Review <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>
    </article>

    {{-- Card 3 (highlighted) --}}
    <article class="glass-card rounded-2xl p-6 flex flex-col h-full group border-l-4 border-l-secondary-container">
        <div class="flex justify-between items-start mb-4">
            <div class="flex gap-2">
                <span class="px-2.5 py-1 bg-secondary-container/20 text-secondary rounded text-xs font-bold tracking-wider uppercase">ML-401</span>
                <span class="px-2.5 py-1 bg-surface-container-highest text-on-surface-variant rounded text-xs font-bold tracking-wider uppercase">Ch {{ $chapterId }}</span>
            </div>
            <button onclick="toggleSaveIcon(this)" class="text-on-surface-variant hover:text-primary transition-colors" title="Save summary">
                <span class="material-symbols-outlined">bookmark_border</span>
            </button>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-2 group-hover:text-primary transition-colors">Loss Functions Explained</h3>
        <p class="text-on-surface-variant text-sm mb-6 line-clamp-3 flex-1">
            Cross-entropy ideal for classification; penalises confident wrong answers heavily. MSE for regression. Huber loss blends both — robust to outliers while remaining differentiable everywhere.
        </p>
        <div class="pt-4 border-t border-outline-variant/30 flex items-center justify-between mt-auto">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined" style="font-size:16px;">schedule</span>
                <span class="text-xs font-medium">12 min read</span>
            </div>
            <a href="{{ route('student.chapters.summaries.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'summaryId' => 1]) }}" class="text-primary text-xs font-semibold flex items-center gap-1 hover:gap-2 transition-all">
                Review <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>
    </article>

    {{-- Card 4 – Wide / Trending --}}
    <article class="glass-card rounded-2xl p-6 flex flex-col md:flex-row gap-6 h-full group lg:col-span-2">
        <div class="flex-1 flex flex-col">
            <div class="flex justify-between items-start mb-4">
                <div class="flex gap-2">
                    <span class="px-2.5 py-1 bg-tertiary-container/20 text-tertiary rounded text-xs font-bold tracking-wider uppercase">ML-401</span>
                    <span class="px-2.5 py-1 bg-surface-container-highest text-on-surface-variant rounded text-xs font-bold tracking-wider uppercase">Ch {{ $chapterId }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 bg-error-container/50 text-on-error-container rounded text-xs font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:14px;">local_fire_department</span> Trending
                    </span>
                    <button onclick="toggleSaveIcon(this)" class="text-on-surface-variant hover:text-primary transition-colors" title="Save summary">
                        <span class="material-symbols-outlined">bookmark_border</span>
                    </button>
                </div>
            </div>
            <h3 class="text-on-surface font-semibold text-lg mb-2 group-hover:text-primary transition-colors">Backpropagation: The Full Picture</h3>
            <p class="text-on-surface-variant text-sm mb-6 flex-1">
                A comprehensive walkthrough of the chain rule applied layer-by-layer. Covers forward pass, loss computation, and backward pass weight updates. Essential reading before any model training. Includes annotated maths and worked examples.
            </p>
            <div class="pt-4 border-t border-outline-variant/30 flex items-center justify-between mt-auto">
                <div class="flex items-center gap-4 text-on-surface-variant">
                    <div class="flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:16px;">schedule</span>
                        <span class="text-xs font-medium">15 min read</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:16px;">attachment</span>
                        <span class="text-xs font-medium">3 Assets</span>
                    </div>
                </div>
                <a href="{{ route('student.chapters.summaries.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'summaryId' => 1]) }}" class="text-primary text-xs font-semibold flex items-center gap-1 hover:gap-2 transition-all">
                    Go to deep dive <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                </a>
            </div>
        </div>
        <div class="w-full md:w-1/3 rounded-xl overflow-hidden bg-surface-container-low relative min-h-[140px] hidden md:block">
            <div class="w-full h-full bg-gradient-to-br from-primary/20 to-tertiary-container/30 absolute inset-0 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary/50" style="font-size:64px;">auto_stories</span>
            </div>
        </div>
    </article>

    {{-- Card 5 --}}
    <article class="glass-card rounded-2xl p-6 flex flex-col h-full group">
        <div class="flex justify-between items-start mb-4">
            <div class="flex gap-2">
                <span class="px-2.5 py-1 bg-primary-container/20 text-primary rounded text-xs font-bold tracking-wider uppercase">Design</span>
                <span class="px-2.5 py-1 bg-surface-container-highest text-on-surface-variant rounded text-xs font-bold tracking-wider uppercase">Ch {{ $chapterId }}</span>
            </div>
            <button onclick="toggleSaveIcon(this)" class="text-on-surface-variant hover:text-primary transition-colors" title="Save summary">
                <span class="material-symbols-outlined">bookmark_border</span>
            </button>
        </div>
        <h3 class="text-on-surface font-semibold text-base mb-2 group-hover:text-primary transition-colors">Overfitting &amp; Regularisation</h3>
        <p class="text-on-surface-variant text-sm mb-6 line-clamp-3 flex-1">
            Dropout, L1/L2 penalties, and early stopping explained. The bias-variance tradeoff governs model generalisation — high variance → overfitting, high bias → underfitting.
        </p>
        <div class="pt-4 border-t border-outline-variant/30 flex items-center justify-between mt-auto">
            <div class="flex items-center gap-2 text-on-surface-variant">
                <span class="material-symbols-outlined" style="font-size:16px;">schedule</span>
                <span class="text-xs font-medium">6 min read</span>
            </div>
            <a href="{{ route('student.chapters.summaries.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'summaryId' => 1]) }}" class="text-primary text-xs font-semibold flex items-center gap-1 hover:gap-2 transition-all">
                Review <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>
    </article>

</div>

{{-- Pagination --}}
<div class="mt-10 flex justify-center">
    <div class="flex items-center gap-2">
        <button class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors disabled:opacity-40" disabled>
            <span class="material-symbols-outlined">chevron_left</span>
        </button>
        <button class="w-10 h-10 rounded-full flex items-center justify-center bg-primary text-on-primary text-sm font-semibold shadow-sm">1</button>
        <button class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface hover:bg-surface-container-high transition-colors text-sm">2</button>
        <button class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface hover:bg-surface-container-high transition-colors text-sm">3</button>
        <button class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors">
            <span class="material-symbols-outlined">chevron_right</span>
        </button>
    </div>
</div>

@endsection
