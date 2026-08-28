@extends('public.layouts.app')

@section('title', 'Subscription | Your Biology')

@include('public.catalog._styles')

@section('content')
<main class="flex-grow pt-lg pb-xl px-4 md:px-lg max-w-container-max mx-auto w-full relative z-10 flex flex-col items-center">
<!-- Ambient Background Decoration -->
<div class="absolute top-20 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-primary/5 rounded-full blur-[100px] -z-10 pointer-events-none"></div>

<!-- Hero Section -->
<header class="text-center mb-xl mt-12 max-w-3xl mx-auto">
<span class="inline-flex items-center justify-center bg-error-container text-on-error-container px-3 py-1 rounded-full font-label-sm text-label-sm mb-4 gap-2 border border-error-container/20">
<span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">lock</span>
                Chapter Locked
            </span>
<h1 class="font-display-lg text-display-lg text-on-surface mb-6">Unlock the Full Biology Experience.</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant">
    @if($chapter ?? null)
        “{{ $chapter->title }}” is part of the premium syllabus. Subscribe to Your Biology Premium to open it and every other chapter, and to add the Theory Guide, ATP Guide and Worksheet Generator.
    @else
        Notes, flashcards, MCQs and theory quizzes are open on the free chapters. Subscribe to Your Biology Premium to open every chapter, and to add the Theory Guide, ATP Guide and Worksheet Generator.
    @endif
</p>
@if($course ?? null)
    <a href="{{ route('public.course.chapters', $course) }}"
        class="inline-flex items-center gap-xs mt-6 font-label-md text-label-md text-secondary hover:text-primary transition-colors group">
        <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
        Back to {{ $course->title }}
    </a>
@endif
</header>

<!-- Pricing Cards Container -->
<div class="w-full max-w-5xl grid grid-cols-1 md:grid-cols-2 gap-lg relative">
<!-- Free Card -->
<div class="bg-surface-container-low/80 backdrop-blur-xl border border-glass-stroke rounded-xl p-8 flex flex-col transition-all duration-300 hover:shadow-primary-glow">
<div class="mb-6 border-b border-outline-variant/30 pb-6">
<h2 class="font-headline-md text-headline-md text-on-surface mb-2">Free Preview</h2>
<p class="font-body-md text-body-md text-on-surface-variant">Notes, flashcards and quizzes on the open chapters.</p>
<div class="mt-4 font-display-lg text-display-lg text-on-surface">$0<span class="font-body-md text-body-md text-on-surface-variant font-normal">/forever</span></div>
</div>
{{-- What a reader gets without subscribing: the four open resources, on the
     preview chapters only. --}}
<ul class="flex-grow space-y-4 mb-8">
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-outline mt-1" style="font-variation-settings: 'FILL' 0;">check</span>
<span class="font-body-md text-body-md text-on-surface-variant">Study Notes</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-outline mt-1" style="font-variation-settings: 'FILL' 0;">check</span>
<span class="font-body-md text-body-md text-on-surface-variant">Flashcards</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-outline mt-1" style="font-variation-settings: 'FILL' 0;">check</span>
<span class="font-body-md text-body-md text-on-surface-variant">MCQ Quizzes</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-outline mt-1" style="font-variation-settings: 'FILL' 0;">check</span>
<span class="font-body-md text-body-md text-on-surface-variant">Theory Quizzes</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-outline mt-1" style="font-variation-settings: 'FILL' 0;">check</span>
<span class="font-body-md text-body-md text-on-surface-variant">Video Lessons</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-outline mt-1" style="font-variation-settings: 'FILL' 0;">check</span>
<span class="font-body-md text-body-md text-on-surface-variant">Diagrams</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-outline mt-1" style="font-variation-settings: 'FILL' 0;">check</span>
<span class="font-body-md text-body-md text-on-surface-variant">Guides</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-outline mt-1" style="font-variation-settings: 'FILL' 0;">check</span>
<span class="font-body-md text-body-md text-on-surface-variant">Summaries</span>
</li>
<li class="flex items-start gap-3 text-secondary">
<span class="material-symbols-outlined mt-1" style="font-variation-settings: 'FILL' 0;">lock</span>
<span class="font-body-md text-body-md font-medium">Open chapters only — premium chapters stay locked</span>
</li>
<li class="flex items-start gap-3 text-secondary">
<span class="material-symbols-outlined mt-1" style="font-variation-settings: 'FILL' 0;">lock</span>
<span class="font-body-md text-body-md font-medium">No Theory Guide, ATP Guide or Worksheet Generator</span>
</li>
</ul>
<a href="{{ route('public.courses') }}"
    class="w-full py-4 rounded-full border border-outline-variant text-on-surface font-label-md text-label-md hover:bg-surface-container-high transition-colors scale-95 active:scale-90 text-center block">
                    Continue with Free Preview
                </a>
</div>

<!-- Premium Card -->
<div class="bg-glass-bg backdrop-blur-2xl border-2 border-primary/20 rounded-xl p-8 flex flex-col relative shadow-primary-glow transform md:-translate-y-4 z-10 transition-all duration-300 hover:shadow-[0_15px_30px_-5px_rgba(0, 19, 48, 0.1)] hover:border-primary/40">
<!-- Highlight Badge -->
<div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary text-on-primary font-label-sm text-label-sm px-4 py-1 rounded-full shadow-md whitespace-nowrap">
                    Most Popular Choice
                </div>
<div class="mb-6 border-b border-primary/10 pb-6">
<h2 class="font-headline-md text-headline-md text-primary mb-2 flex items-center gap-2">
                        Premium Subscription
                        <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings: 'FILL' 1;">workspace_premium</span>
</h2>
<p class="font-body-md text-body-md text-on-surface-variant">Every chapter, plus the guides and worksheet generator.</p>
<div class="mt-4 font-display-lg text-display-lg text-on-surface">$19<span class="font-body-md text-body-md text-on-surface-variant font-normal">/month</span></div>
</div>
{{-- Everything the free tier has, plus the three premium-only tools, on every
     chapter rather than the open ones. --}}
<ul class="flex-grow space-y-4 mb-8">
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">lock_open</span>
<span class="font-body-md text-body-md text-on-surface font-semibold">Every chapter open — no locked content</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<span class="font-body-md text-body-md text-on-surface-variant">Study Notes</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<span class="font-body-md text-body-md text-on-surface-variant">Flashcards</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<span class="font-body-md text-body-md text-on-surface-variant">MCQ Quizzes</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<span class="font-body-md text-body-md text-on-surface-variant">Theory Quizzes</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<span class="font-body-md text-body-md text-on-surface-variant">Video Lessons</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<span class="font-body-md text-body-md text-on-surface-variant">Diagrams</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<span class="font-body-md text-body-md text-on-surface-variant">Guides</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<span class="font-body-md text-body-md text-on-surface-variant">Summaries</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">menu_book</span>
<span class="font-body-md text-body-md text-on-surface font-semibold">Theory Guide &amp; ATP Guide</span>
</li>
<li class="flex items-start gap-3">
<span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">assignment</span>
<span class="font-body-md text-body-md text-on-surface font-semibold">Worksheet Generator</span>
</li>
<li class="flex items-start gap-3 bg-primary-container/20 p-2 rounded-lg -ml-2">
<span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">new_releases</span>
<span class="font-body-md text-body-md text-on-surface-variant"><strong class="text-primary">NEW:</strong> The latest content as it is added, updated for you automatically.</span>
</li>
</ul>
{{-- A course that is actually on sale opens the payment popup here rather than
     sending the reader through the plan page first. Without one there is
     nothing to buy yet, so this falls back to the plan page or the gallery. --}}
@if(($plan ?? null)?->isSellable())
    <button type="button" data-open-payment
        class="w-full py-4 rounded-full bg-primary text-on-primary font-label-md text-label-md shadow-primary-glow hover:bg-primary/90 transition-colors scale-95 active:scale-90 flex items-center justify-center gap-2">
                    Start Your Subscription
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
    </button>
@else
    <a href="{{ ($course ?? null)
            ? (($chapter ?? null)
                ? route('public.course.chapter.subscribe.plans', [$course, $chapter])
                : route('public.subscribe.plans', $course))
            : route('public.courses') }}"
        class="w-full py-4 rounded-full bg-primary text-on-primary font-label-md text-label-md shadow-primary-glow hover:bg-primary/90 transition-colors scale-95 active:scale-90 flex items-center justify-center gap-2">
                    Start Your Subscription
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
    </a>
@endif
</div>
</div>
</main>

@if(($plan ?? null)?->isSellable())
    @include('public._payment-modal', ['course' => $course, 'plan' => $plan])
@endif
@endsection
