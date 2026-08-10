@extends('layouts.student')

@section('title', 'Learning Summary Detail')

@push('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .glass-hover:hover {
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
</style>
@endpush

@section('content')
<div class="w-full flex flex-col gap-6 pt-4">
    <!-- Header Section -->
    <header class="flex flex-col gap-4 w-full">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', ['id' => $courseId]) }}" class="hover:text-primary transition-colors">Course {{ $courseId }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Chapter {{ $chapterId }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.summaries', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Summaries</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface font-semibold">Summary Details</span>
        </nav>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="bg-primary/10 text-primary px-2 py-0.5 rounded text-xs uppercase tracking-wider">Chapter {{ $chapterId }}</span>
                </div>
                <h1 class="text-3xl font-bold text-on-surface">Information Architecture Principles</h1>
            </div>
            <!-- Quick Actions -->
            <div class="flex gap-2">
                <button class="flex items-center justify-center p-2 rounded-lg bg-surface-container hover:bg-primary/10 hover:text-primary text-on-surface-variant transition-colors group">
                    <span class="material-symbols-outlined group-hover:scale-110 transition-transform">download</span>
                </button>
                <button onclick="toggleSaveIcon(this)" class="flex items-center justify-center p-2 rounded-lg bg-surface-container hover:bg-primary/10 hover:text-primary text-on-surface-variant transition-colors group" title="Save summary">
                    <span class="material-symbols-outlined group-hover:scale-110 transition-transform">bookmark_border</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Two Column Layout for Content and Sidebar -->
    <div class="flex flex-col lg:flex-row gap-6 relative">
        <!-- Left Column: Reading Area -->
        <article class="flex-1 flex flex-col gap-6">
            <!-- Key Takeaways Card -->
            <section class="glass-panel rounded-xl p-6 glass-hover">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-full bg-secondary-container/20 flex items-center justify-center text-secondary-container">
                        <span class="material-symbols-outlined">lightbulb</span>
                    </div>
                    <h2 class="text-xl font-semibold text-on-surface">Key Takeaways</h2>
                </div>
                <ul class="space-y-3 text-base text-on-surface-variant">
                    <li class="flex gap-3">
                        <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">check_circle</span>
                        <span><strong>Information Architecture (IA)</strong> is the structural design of shared information environments.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">check_circle</span>
                        <span>The primary goal of IA is to help users find information and complete tasks easily.</span>
                    </li>
                </ul>
            </section>

            <!-- Summary Text Content -->
            <section class="glass-panel rounded-xl p-8 bg-surface-container-lowest">
                <h2 class="text-2xl font-semibold text-on-surface mb-6 border-b border-outline-variant/30 pb-4">Detailed Summary</h2>
                <div class="prose prose-slate max-w-none space-y-6">
                    <h3 class="text-xl font-semibold text-on-surface mt-8 mb-4">The Role of IA in User Experience</h3>
                    <p class="text-base text-on-surface-variant leading-relaxed">
                        Information Architecture forms the foundation upon which User Experience (UX) is built. While UI design focuses on the visual presentation and interaction, IA dictates the underlying structure. Without a solid IA, even the most visually appealing interfaces will fail if users cannot navigate to their desired content intuitively.
                    </p>
                    <div class="my-8 p-4 bg-surface-container-low rounded-lg border-l-4 border-primary">
                        <p class="text-sm text-on-surface italic">
                            "Good information architecture is invisible; bad information architecture is immediately obvious and frustrating."
                        </p>
                    </div>
                </div>
            </section>
        </article>

        <!-- Right Column: Sidebar Actions & Navigation -->
        <aside class="w-full lg:w-[320px] flex flex-col gap-4 sticky top-[100px] self-start">
            <!-- Mastery Check -->
            <div class="glass-panel rounded-xl p-5 border-t-4 border-t-tertiary shadow-sm">
                <h3 class="text-sm font-semibold text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-tertiary">school</span>
                    Mastery Status
                </h3>
                <p class="text-sm text-on-surface-variant mb-4">How well do you understand this summary?</p>
                <div class="flex gap-2">
                    <button class="flex-1 py-2 px-3 rounded-lg bg-tertiary/10 text-tertiary text-sm font-semibold border border-tertiary/20 hover:bg-tertiary hover:text-white transition-colors flex justify-center items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">done</span> Mastered
                    </button>
                </div>
            </div>
            
            <!-- Next Steps -->
            <div class="glass-panel rounded-xl p-5 bg-gradient-to-br from-primary/5 to-transparent">
                <h3 class="text-sm font-semibold text-on-surface mb-4">Next Steps</h3>
                <div class="space-y-3">
                    <a class="flex items-center justify-between p-3 rounded-lg bg-white shadow-sm border border-outline-variant/20 hover:border-primary/50 hover:shadow-md transition-all group" href="#">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-primary/10 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-[18px]">quiz</span>
                            </div>
                            <span class="text-sm font-semibold text-on-surface group-hover:text-primary transition-colors">Practice Quiz</span>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
