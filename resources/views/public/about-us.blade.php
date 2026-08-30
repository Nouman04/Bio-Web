@extends('public.layouts.app')

@section('title', 'About Us | Your Biology')

@push('styles')
<style>
body { font-family: 'Geist', sans-serif; }
        .glass-shadow { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 19, 48, 0.05); }
</style>
@endpush

@section('content')
<main class="flex-grow w-full max-w-container-max mx-auto px-md md:px-lg py-xl">
<!-- Hero Section -->
<section class="text-center mb-xl max-w-3xl mx-auto">
<h1 class="font-display-lg text-display-lg text-on-background mb-md">Biology made simpler.<br/><span class="text-primary">Exams made easier.</span></h1>
<p class="font-body-lg text-body-lg text-on-surface-variant">This website was created to make IGCSE Biology easier to understand, revise, and actually enjoy.</p>
</section>
<!-- Story/Mission Section -->
<section class="mb-xl">
<div class="bg-surface-container-lowest rounded-xl p-lg md:p-xl glass-shadow border border-black/5 relative overflow-hidden">
<div class="absolute top-0 right-0 w-64 h-64 bg-primary-fixed/30 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
<div class="relative z-10 flex flex-col md:flex-row gap-lg items-center">
<div class="flex-1">
<div class="inline-flex items-center space-x-2 px-3 py-1 bg-primary/10 text-primary rounded-full font-label-sm text-label-sm mb-md">
<span class="material-symbols-outlined" style="font-size: 16px;">school</span>
<span>Our Mission</span>
</div>
<h2 class="font-headline-lg text-headline-lg text-on-background mb-md">The Teacher's Perspective</h2>
<p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">
                            "As a Biology teacher, I noticed that students often struggle not because Biology is too difficult, but because there is simply too much information and too little clarity. So, I created a place where everything is focused on what actually matters."
                        </p>
</div>
<div class="flex-1 w-full relative">

{{-- Photography brief: Biology classroom --}}
<div class="w-full h-80 object-cover rounded-lg shadow-sm bg-primary/20" role="img" aria-label="Biology classroom"></div>
</div>
</div>
</div>
</section>
<!-- Features Bento Grid -->
<section class="mb-xl">
<div class="text-center mb-lg">
<h2 class="font-headline-lg text-headline-lg text-on-background">Focused on what matters</h2>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-md">
<!-- Feature 1 -->
<div class="bg-surface-container-lowest rounded-xl p-md glass-shadow border border-black/5 flex flex-col items-start transition-transform hover:-translate-y-1">
<div class="w-12 h-12 rounded-lg bg-secondary-container text-primary flex items-center justify-center mb-md">
<span class="material-symbols-outlined" data-weight="fill" style="font-variation-settings: 'FILL' 1;">description</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-background mb-sm">Clear, exam-focused notes</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Streamlined content designed specifically to hit syllabus points without unnecessary fluff.</p>
</div>
<!-- Feature 2 -->
<div class="bg-surface-container-lowest rounded-xl p-md glass-shadow border border-black/5 flex flex-col items-start transition-transform hover:-translate-y-1">
<div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center mb-md">
<span class="material-symbols-outlined" data-weight="fill" style="font-variation-settings: 'FILL' 1;">account_tree</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-background mb-sm">Simple Biology diagrams</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Visual aids stripped down to their essential components for easier memorization.</p>
</div>
<!-- Feature 3 -->
<div class="bg-surface-container-lowest rounded-xl p-md glass-shadow border border-black/5 flex flex-col items-start transition-transform hover:-translate-y-1">
<div class="w-12 h-12 rounded-lg bg-tertiary-fixed text-tertiary flex items-center justify-center mb-md">
<span class="material-symbols-outlined" data-weight="fill" style="font-variation-settings: 'FILL' 1;">quiz</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-background mb-sm">Practice questions and MCQs</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Test your knowledge immediately with targeted, exam-style questions.</p>
</div>
<!-- Feature 4 -->
<div class="bg-surface-container-lowest rounded-xl p-md glass-shadow border border-black/5 flex flex-col items-start transition-transform hover:-translate-y-1">
<div class="w-12 h-12 rounded-lg bg-secondary-fixed text-on-secondary-fixed-variant flex items-center justify-center mb-md">
<span class="material-symbols-outlined" data-weight="fill" style="font-variation-settings: 'FILL' 1;">view_carousel</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-background mb-sm">Flashcards & quick tools</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Rapid revision resources perfect for last-minute review sessions.</p>
</div>
<!-- Feature 5 -->
<div class="bg-surface-container-lowest rounded-xl p-md glass-shadow border border-black/5 flex flex-col items-start transition-transform hover:-translate-y-1 lg:col-span-2">
<div class="w-12 h-12 rounded-lg bg-primary-container text-on-primary-container flex items-center justify-center mb-md">
<span class="material-symbols-outlined" data-weight="fill" style="font-variation-settings: 'FILL' 1;">tips_and_updates</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-background mb-sm">Cambridge-style exam tips & Common traps</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Learn not just the material, but how to answer the specific types of questions examiners love to ask, and learn exactly what mistakes to avoid before you make them in the real exam.</p>
</div>
</div>
</section>
<!-- Core Philosophy & CTA -->
<section class="bg-primary text-on-primary rounded-xl p-lg md:p-xl text-center relative overflow-hidden glass-shadow">
<div class="absolute inset-0 opacity-10 bg-white pointer-events-none"></div>
<div class="relative z-10 max-w-2xl mx-auto">
<span class="material-symbols-outlined mb-sm opacity-80" style="font-size: 32px;">psychology</span>
<p class="font-headline-lg text-headline-lg mb-lg">"Everything is designed with one goal: helping you understand Biology instead of simply memorising it."</p>
<p class="font-body-lg text-body-lg text-primary-fixed mb-lg">
                    Whether you're learning a topic for the first time, preparing for a test, or doing your final revision before the exam, this website is here to make the process simpler.
                </p>
<div class="font-display-lg text-display-lg font-bold tracking-tight mb-md">
                    Learn it. Understand it. Apply it. Ace it.
                </div>
<button class="mt-sm px-8 py-3 bg-surface-container-lowest text-primary font-label-md text-label-md rounded-full hover:bg-surface-bright transition-colors shadow-sm active:scale-95 inline-flex items-center space-x-2">
<span>Start Learning Now</span>
<span class="material-symbols-outlined" style="font-size: 18px;">arrow_forward</span>
</button>
</div>
</section>
</main>
@endsection
