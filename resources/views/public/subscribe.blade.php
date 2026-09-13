@extends('public.layouts.app')

@section('title', 'Subscription | Your Biology')

@include('public.catalog._styles')

@section('content')
@php
    $hasPricing = false;
    $displayPrice = null;
    $displayInterval = 'month';
    $hasPromo = false;
    $originalPrice = null;

    if ($course ?? null) {
        if ($price && $price->price > 0) {
            $hasPricing = true;
            $displayInterval = $price->billing_interval ?? 'month';
            if ($price->hasLivePromo()) {
                $hasPromo = true;
                $originalPrice = $price->formatted_price;
                $displayPrice = $price->formatted_payable;
            } else {
                $displayPrice = $price->formatted_price;
            }
        } elseif ($plan && $plan->price > 0) {
            $hasPricing = true;
            $displayPrice = $plan->formatted_price;
            $displayInterval = $plan->billing_interval ?? 'month';
        }
    } else {
        // Fallback for general subscription page if reached without course context
        $hasPricing = true;
        $displayPrice = '$19';
        $displayInterval = 'month';
    }
@endphp
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
<div class="w-full max-w-5xl grid grid-cols-1 md:grid-cols-2 gap-lg relative items-stretch pt-6">
<!-- Free Card -->
<div class="bg-surface-container-lowest border border-outline-variant/60 rounded-2xl px-8 flex flex-col justify-between h-full transition-all duration-300 hover:shadow-lg hover:border-outline" style="padding-top: 2.5rem; padding-bottom: 2rem;">
    <div>
        <div class="mb-6 border-b border-outline-variant/30 pb-6">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-2">Free Preview</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Notes, flashcards and quizzes on the open chapters.</p>
            <div class="mt-4 font-display-lg text-display-lg text-on-surface">$0<span class="font-body-md text-body-md text-on-surface-variant font-normal">/forever</span></div>
        </div>
        {{-- What a reader gets without subscribing: the open resources on preview chapters only. --}}
        <ul class="space-y-4 mb-8">
            <li class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <span class="font-body-md text-body-md text-on-surface font-medium">Free preview chapters open</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <span class="font-body-md text-body-md text-on-surface-variant">Study Notes (Open chapters)</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <span class="font-body-md text-body-md text-on-surface-variant">Flashcards (Open chapters)</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <span class="font-body-md text-body-md text-on-surface-variant">MCQ Quizzes (Open chapters)</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <span class="font-body-md text-body-md text-on-surface-variant">Theory Quizzes (Open chapters)</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <span class="font-body-md text-body-md text-on-surface-variant">Video Lessons (Open chapters)</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <span class="font-body-md text-body-md text-on-surface-variant">Diagrams (Open chapters)</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <span class="font-body-md text-body-md text-on-surface-variant">Guides (Open chapters)</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary/70 mt-1" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <span class="font-body-md text-body-md text-on-surface-variant">Summaries (Open chapters)</span>
            </li>
            <li class="flex items-start gap-3 text-secondary">
                <span class="material-symbols-outlined mt-1" style="font-variation-settings: 'FILL' 0;">lock</span>
                <span class="font-body-md text-body-md">Theory Guide &amp; ATP Guide <span class="text-xs text-secondary/80 font-normal block">Requires Premium</span></span>
            </li>
            <li class="flex items-start gap-3 text-secondary">
                <span class="material-symbols-outlined mt-1" style="font-variation-settings: 'FILL' 0;">lock</span>
                <span class="font-body-md text-body-md">Worksheet Generator <span class="text-xs text-secondary/80 font-normal block">Requires Premium</span></span>
            </li>
            <li class="flex items-start gap-3 bg-surface-container-high/40 p-2 rounded-lg -ml-2 text-secondary">
                <span class="material-symbols-outlined mt-1" style="font-variation-settings: 'FILL' 0;">lock</span>
                <span class="font-body-md text-body-md text-on-surface-variant">Locked chapters &amp; new updates <span class="text-xs text-secondary/80 font-normal block">Requires Premium</span></span>
            </li>
        </ul>
    </div>

    <div class="mt-auto pt-4 border-t border-outline-variant/20">
        <a href="{{ ($course ?? null) ? route('public.course.chapters', $course) : route('public.courses') }}"
            class="w-full py-4 rounded-full border-2 border-outline-variant/60 text-on-surface font-label-md text-label-md hover:bg-surface-container-high hover:border-outline transition-colors scale-95 active:scale-90 text-center block font-semibold">
            Continue with Free Preview
        </a>
        <p class="mt-3 text-center font-label-sm text-label-sm text-on-surface-variant">
            No credit card required. Free forever.
        </p>
    </div>
</div>

<!-- Premium Card -->
<div class="bg-surface-container-lowest border-2 border-primary/40 rounded-2xl px-8 flex flex-col justify-between h-full relative shadow-primary-glow z-10 transition-all duration-300 hover:shadow-[0_15px_30px_-5px_rgba(0,19,48,0.15)] hover:border-primary" style="padding-top: 2.5rem; padding-bottom: 2rem;">
<!-- Highlight Badge -->
<div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary text-on-primary font-label-sm text-label-sm px-4 py-1.5 rounded-full shadow-md whitespace-nowrap z-20" style="top: -14px;">
    Most Popular Choice
</div>
<div>
    <div class="mb-6 border-b border-primary/10 pb-6">
        <h2 class="font-headline-md text-headline-md text-primary mb-2 flex items-center gap-2">
            Premium Subscription
            <span class="material-symbols-outlined text-primary text-[20px]" style="font-variation-settings: 'FILL' 1;">workspace_premium</span>
        </h2>
        <p class="font-body-md text-body-md text-on-surface-variant">Every chapter, plus the guides and worksheet generator.</p>

        @if($hasPricing)
            <div class="mt-4 flex items-baseline gap-2">
                @if($hasPromo)
                    <span class="font-body-lg text-body-lg text-on-surface-variant line-through">{{ $originalPrice }}</span>
                @endif
                <div class="font-display-lg text-display-lg text-on-surface">
                    {{ $displayPrice }}<span class="font-body-md text-body-md text-on-surface-variant font-normal">/{{ $displayInterval }}</span>
                </div>
            </div>
        @else
            <div class="mt-4">
                <div class="font-headline-md text-headline-md text-on-surface-variant font-semibold">Pricing Coming Soon</div>
                <div class="mt-3 p-3.5 rounded-xl bg-primary-fixed/20 border border-primary-fixed-dim/30 flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-primary text-[20px] mt-0.5 shrink-0" style="font-variation-settings: 'FILL' 1;">info</span>
                    <div class="text-xs text-on-surface">
                        <p class="font-semibold text-primary">Pricing Not Set Yet</p>
                        <p class="text-on-surface-variant mt-0.5 leading-relaxed">
                            This course is part of the premium syllabus, but its subscription pricing has not been configured yet. Please check back soon or explore the free preview chapters.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
    {{-- Everything the free tier has, plus the three premium-only tools, on every
         chapter rather than the open ones. --}}
    <ul class="space-y-4 mb-8">
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
</div>

<div class="mt-auto pt-4 border-t border-primary/10">
    @if($subscribed ?? false)
        <div class="w-full py-4 rounded-full bg-tertiary-container/20 text-tertiary font-label-md text-label-md flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            You already subscribe to this course
        </div>
        @if($course ?? null)
            <a href="{{ route('public.course.chapters', $course) }}"
                class="mt-3 text-center font-label-md text-label-md text-secondary hover:text-primary transition-colors block">
                Go to the chapters
            </a>
        @endif
    @elseif($hasPricing && ($plan ?? null)?->isSellable())
        <button type="button" data-open-payment
            class="w-full py-4 rounded-full bg-primary text-on-primary font-label-md text-label-md shadow-primary-glow hover:bg-primary/90 transition-colors scale-95 active:scale-90 flex items-center justify-center gap-2">
            Start Your Subscription
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </button>
        <p class="mt-3 text-center font-label-sm text-label-sm text-on-surface-variant">
            Secure payment through Stripe. Cancel any time.
        </p>
    @elseif($hasPricing)
        <a href="{{ ($course ?? null)
                ? (($chapter ?? null)
                    ? route('public.course.chapter.subscribe.plans', [$course, $chapter])
                    : route('public.subscribe.plans', $course))
                : route('public.courses') }}"
            class="w-full py-4 rounded-full bg-primary text-on-primary font-label-md text-label-md shadow-primary-glow hover:bg-primary/90 transition-colors scale-95 active:scale-90 flex items-center justify-center gap-2">
            Choose a Plan
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </a>
        <p class="mt-3 text-center font-label-sm text-label-sm text-on-surface-variant">
            Choose your billing interval on the next screen.
        </p>
    @else
        <div class="w-full py-4 rounded-full border border-outline-variant text-on-surface-variant font-label-md text-label-md text-center bg-surface-container-low/50">
            Subscriptions Opening Soon
        </div>
        @if($course ?? null)
            <a href="{{ route('public.course.chapters', $course) }}"
                class="mt-3 text-center font-label-md text-label-md text-secondary hover:text-primary transition-colors block">
                Browse free preview chapters
            </a>
        @else
            <a href="{{ route('public.courses') }}"
                class="mt-3 text-center font-label-md text-label-md text-secondary hover:text-primary transition-colors block">
                Browse courses
            </a>
        @endif
    @endif
</div>
</div>
</div>
</main>

@if($hasPricing && ($plan ?? null)?->isSellable() && !($subscribed ?? false))
    @include('public._payment-modal', ['course' => $course, 'plan' => $plan])
@endif
@endsection
