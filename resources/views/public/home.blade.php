@extends('public.layouts.app')

@section('title', 'Your Biology — IGCSE Biology Made Simple | Your Biology')

@section('content')
<main class="relative w-full overflow-hidden">
<div class="hero-glow-1"></div>
<div class="hero-glow-2"></div>
<!-- Hero Section -->
<section class="max-w-container-max mx-auto px-md md:px-lg pt-2xl pb-xl md:pt-[160px] md:pb-[120px] flex flex-col lg:flex-row items-center gap-xl relative z-10">
<div class="flex-1 space-y-8 max-w-2xl text-center lg:text-left">
<div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/60 border border-white/80 shadow-sm backdrop-blur-md">
<span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
<span class="font-label-sm text-primary tracking-wide uppercase">New IGCSE 2024 Syllabus Available</span>
</div>
<h1 class="font-display-lg text-display-lg md:text-[64px] text-on-surface leading-[1.1] tracking-tight">
                    Master IGCSE Biology <br class="hidden lg:block">
<span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-[#8b5cf6]">with Confidence</span>
</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant text-lg md:text-xl max-w-xl mx-auto lg:mx-0">
                    Biology made simpler. Exams made easier. Learn it, understand it, and ace your exams with our expert-crafted, interactive resources designed for the modern student.
                </p>
<div class="pt-4 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
<a class="w-full sm:w-auto inline-flex items-center justify-center whitespace-nowrap bg-primary text-on-primary px-8 py-4 rounded-full font-label-md hover:bg-primary-container transition-all duration-300 transform active:scale-95 shadow-[0_8px_30px_rgb(70,72,212,0.3)] btn-glow text-lg" href="#">
                        Start Learning for Free
                    </a>
<a class="w-full sm:w-auto inline-flex items-center justify-center whitespace-nowrap gap-2 text-on-surface px-6 py-4 font-label-md hover:text-primary transition-colors text-lg" href="#courses">
<span class="material-symbols-outlined text-[20px]">arrow_downward</span>
                        Browse the courses
                    </a>
</div>
<div class="flex items-center justify-center lg:justify-start gap-4 pt-6 opacity-70">
<div class="flex -space-x-3">
@foreach(['a', 'b', 'c', 'd'] as $avatar)
<img src="{{ asset('images/avatars/' . $avatar . '.svg') }}" alt=""
    class="w-10 h-10 rounded-full border-2 border-white bg-white object-cover shadow-sm"
    width="40" height="40" loading="lazy">
@endforeach
<div class="w-10 h-10 rounded-full border-2 border-white bg-primary flex items-center justify-center text-xs font-bold text-on-primary shadow-sm">+2k</div>
</div>
<div class="text-sm font-label-sm text-on-surface-variant">Trusted by 2,000+ students</div>
</div>
</div>
<div class="flex-1 w-full relative max-w-3xl lg:max-w-none mx-auto mt-12 lg:mt-0">
<div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-transparent rounded-[2.5rem] blur-3xl transform rotate-3"></div>
<div class="glass-panel p-3 rounded-[2.5rem] relative transform hover:-translate-y-2 transition-transform duration-500 shadow-2xl">

{{-- A preview of the real library rather than a stock photograph. --}}
<div class="w-full rounded-[2rem] bg-white/85 backdrop-blur-sm p-6 sm:p-7 flex flex-col gap-5 min-h-[420px]">

@php($previewCourse = $preview['course'] ?? null)

    {{-- Course header --}}
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="text-xs font-label-sm uppercase tracking-wider text-primary/70 mb-1">Now studying</div>
            <div class="font-headline-md text-lg font-bold text-on-surface truncate">
                {{ $previewCourse->title ?? 'IGCSE Biology' }}
            </div>
        </div>
        <div class="shrink-0 w-11 h-11 rounded-2xl bg-primary flex items-center justify-center text-on-primary shadow-lg">
            <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">school</span>
        </div>
    </div>

    {{-- Chapters --}}
    <div class="flex flex-col gap-2.5">
        @forelse($preview['chapters'] as $index => $chapter)
            <div class="flex items-center gap-3 rounded-2xl bg-surface-container-low/70 border border-outline-variant/20 px-3.5 py-3">
                <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xs font-bold shrink-0">
                    {{ $chapter->chapter_number ?: $index + 1 }}
                </div>
                <div class="text-sm font-medium text-on-surface truncate flex-1">{{ $chapter->title }}</div>
                <span class="material-symbols-outlined text-[20px] {{ $index === 0 ? 'text-green-600' : 'text-outline-variant' }}"
                    style="font-variation-settings: 'FILL' 1;">
                    {{ $index === 0 ? 'check_circle' : 'chevron_right' }}
                </span>
            </div>
        @empty
            <div class="rounded-2xl bg-surface-container-low/70 border border-outline-variant/20 px-3.5 py-6 text-center text-sm text-on-surface-variant">
                New chapters are being added to the library.
            </div>
        @endforelse
    </div>

    {{-- What the library holds --}}
    <div class="mt-auto grid grid-cols-3 gap-2 pt-1">
        @foreach([
            ['menu_book', $preview['chapters_total'], 'Chapters'],
            ['quiz', $preview['questions'], 'Questions'],
            ['workspace_premium', 'IGCSE', 'Syllabus'],
        ] as [$icon, $value, $label])
            <div class="rounded-2xl bg-primary/5 px-2 py-3 text-center">
                <span class="material-symbols-outlined text-[18px] text-primary">{{ $icon }}</span>
                <div class="font-bold text-on-surface text-base leading-tight">{{ $value }}</div>
                <div class="text-[11px] text-on-surface-variant">{{ $label }}</div>
            </div>
        @endforeach
    </div>
</div>
<!-- Floating Elements -->
<div class="absolute -top-5 -left-4 sm:-left-8 glass-panel px-4 py-3 rounded-2xl flex items-center gap-3 animate-bounce shadow-xl z-20" style="animation-duration: 3s;">
<div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
<span class="material-symbols-outlined">trending_up</span>
</div>
<div>
<div class="font-bold text-sm text-on-surface">Grade A*</div>
<div class="text-xs text-on-surface-variant">Target Reached</div>
</div>
</div>

</div>
</div>
</section>
<!-- Value Proposition Section -->
<section class="py-2xl relative z-10">
<div class="max-w-container-max mx-auto px-md md:px-lg">
<div class="text-center mb-16">
<h2 class="font-headline-lg text-headline-lg md:text-[40px] text-on-surface mb-4">Why Choose Your Biology?</h2>
<p class="text-on-surface-variant max-w-2xl mx-auto font-body-md text-lg">Everything you need to master biology, designed with cognitive science in mind.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<!-- Card 1 -->
<div class="glass-panel p-8 rounded-3xl flex flex-col items-start gap-6 hover:-translate-y-2 transition-all duration-300 hover:shadow-2xl group">
<div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-fixed to-white flex items-center justify-center text-primary shadow-inner group-hover:scale-110 transition-transform">
<span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">schema</span>
</div>
<div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-3">Interactive Diagrams</h3>
<p class="font-body-md text-on-surface-variant leading-relaxed">Master complex systems with labeled, high-fidelity visual aids designed specifically for clarity and retention.</p>
</div>
</div>
<!-- Card 2 -->
<div class="glass-panel p-8 rounded-3xl flex flex-col items-start gap-6 hover:-translate-y-2 transition-all duration-300 hover:shadow-2xl group">
<div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#e0e7ff] to-white flex items-center justify-center text-[#4f46e5] shadow-inner group-hover:scale-110 transition-transform">
<span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">menu_book</span>
</div>
<div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-3">Expert Notes</h3>
<p class="font-body-md text-on-surface-variant leading-relaxed">Comprehensive study guides written specifically for the IGCSE syllabus, cutting through the noise to focus on what matters.</p>
</div>
</div>
<!-- Card 3 -->
<div class="glass-panel p-8 rounded-3xl flex flex-col items-start gap-6 hover:-translate-y-2 transition-all duration-300 hover:shadow-2xl group">
<div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#fce7f3] to-white flex items-center justify-center text-[#db2777] shadow-inner group-hover:scale-110 transition-transform">
<span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">quiz</span>
</div>
<div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-3">Exam-Ready Quizzes</h3>
<p class="font-body-md text-on-surface-variant leading-relaxed">Test your knowledge with MCQs and theory questions meticulously designed to mimic real exam formats and difficulty.</p>
</div>
</div>
</div>
</div>
</section>
<!-- Courses List Section -->
<section id="courses" class="py-2xl relative z-10">
<div class="absolute inset-0 bg-gradient-to-b from-transparent via-primary/5 to-transparent -z-10"></div>
<div class="max-w-container-max mx-auto px-md md:px-lg">
<div class="text-center mb-16">
<h2 class="font-headline-lg text-headline-lg md:text-[40px] text-on-surface mb-4">Our Courses</h2>
<p class="text-on-surface-variant max-w-2xl mx-auto font-body-md text-lg">Master the IGCSE Biology syllabus with our comprehensive, expert-led modules.</p>
</div>
<div class="grid grid-cols-1 gap-8">
@forelse($courses as $course)
<div class="glass-panel rounded-3xl overflow-hidden flex flex-col md:flex-row hover:shadow-xl transition-shadow duration-300 group">
<div class="md:w-[320px] h-48 md:h-auto relative overflow-hidden bg-gradient-to-br from-primary-fixed to-white flex items-center justify-center">
<span class="material-symbols-outlined text-primary text-[64px] group-hover:scale-105 transition-transform duration-700">science</span>
<div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-primary shadow-sm">{{ $course->category?->title ?: 'Course' }}</div>
</div>
<div class="p-8 flex-1 flex flex-col justify-center">
<h3 class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors mb-2">{{ $course->title }}</h3>
<p class="text-on-surface-variant text-body-md mb-6 line-clamp-2">{{ $course->excerpt }}</p>
<div class="flex flex-wrap items-center gap-6 mt-auto border-t border-outline-variant/20 pt-6">
<div class="flex items-center gap-2 text-on-surface-variant font-label-sm">
<span class="material-symbols-outlined text-primary text-[20px]">menu_book</span>
<span class="">{{ $course->chapters_count }} {{ Str::plural('Chapter', $course->chapters_count) }}</span>
</div>
<div class="ml-auto">
<a class="bg-primary text-on-primary px-6 py-2.5 rounded-full font-label-md hover:bg-primary-container transition-colors shadow-md" href="{{ route('public.course.chapters', $course) }}">Explore Module</a>
</div>
</div>
</div>
</div>
@empty
<div class="glass-panel rounded-3xl p-16 text-center">
<div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-primary-fixed to-white flex items-center justify-center text-primary mb-6">
<span class="material-symbols-outlined text-[32px]">menu_book</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2">Courses are on the way</h3>
<p class="text-on-surface-variant font-body-md">Our modules are being prepared. Check back shortly.</p>
</div>
@endforelse
</div>
@if($courses->isNotEmpty())
<div class="mt-12 text-center">
<a class="inline-flex items-center justify-center bg-white text-on-surface px-8 py-3 rounded-full font-label-md hover:bg-surface-container-low transition-all border border-outline-variant/30 shadow-sm gap-2" href="{{ route('public.courses') }}">Browse All Courses <span class="material-symbols-outlined text-[20px]">arrow_forward</span></a>
</div>
@endif
</div>
</section>

<!-- Mission Section -->
<section class="py-2xl relative z-10">
<div class="max-w-[1000px] mx-auto px-md">
<div class="glass-panel p-12 md:p-16 rounded-[2.5rem] text-center relative overflow-hidden shadow-2xl">
<div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent"></div>
<div class="relative z-10">
<div class="w-20 h-20 mx-auto bg-gradient-to-br from-primary to-surface-tint rounded-full flex items-center justify-center mb-8 shadow-lg">
<span class="material-symbols-outlined text-white text-[40px]">lightbulb</span>
</div>
<h2 class="font-headline-lg text-headline-lg md:text-[40px] text-on-surface mb-6">Our Mission: Biology Made Simpler</h2>
<p class="font-body-lg text-body-lg text-on-surface-variant text-lg md:text-xl max-w-3xl mx-auto leading-relaxed">
                            We believe that mastering IGCSE Biology shouldn't be about memorizing endless textbooks. It's about understanding the core concepts and applying them with confidence. Your Biology was built to cut through the complexity, providing students with clear, focused, and interactive tools that illuminate the subject matter and pave the way to exam success.
                        </p>
</div>
</div>
</div>
</section>
</main>
@endsection
