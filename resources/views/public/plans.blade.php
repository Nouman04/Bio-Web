@extends('public.layouts.app')

@section('title', $course->title . ' — Subscribe | Your Biology')

@include('public.catalog._styles')

@section('content')
<main class="flex-grow pt-lg pb-xl px-4 md:px-lg max-w-container-max mx-auto w-full relative z-10">
<div class="absolute top-20 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-primary/5 rounded-full blur-[100px] -z-10 pointer-events-none"></div>

<header class="text-center mb-xl mt-8 max-w-2xl mx-auto">
<h1 class="font-display-lg text-display-lg text-on-surface mb-4">Subscribe to {{ $course->title }}</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant">
    @if($chapter)
        Opens “{{ $chapter->title }}” and every other chapter in this course.
    @else
        One subscription opens every chapter in this course.
    @endif
</p>
<a href="{{ $chapter
        ? route('public.course.chapter.subscribe', [$course, $chapter])
        : route('public.subscribe') }}"
    class="inline-flex items-center gap-xs mt-6 font-label-md text-label-md text-secondary hover:text-primary transition-colors group">
    <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
    Compare what is included
</a>
</header>

<div class="max-w-lg mx-auto">
<article class="bg-glass-bg backdrop-blur-2xl border-2 border-primary/20 rounded-xl p-8 flex flex-col shadow-primary-glow">
    <span class="font-label-sm text-label-sm text-on-surface-variant mb-2">{{ $course->category?->title ?: 'Course' }}</span>
    <h2 class="font-headline-md text-headline-md text-on-surface mb-2">{{ $course->title }}</h2>
    <p class="font-body-md text-body-md text-on-surface-variant mb-6">{{ $course->excerpt }}</p>

    {{-- Every set of terms the course is sold on. Monthly is the yardstick, so
         the yearly card shows what paying up front saves against it. --}}
    <div class="mb-6 pb-6 border-b border-primary/10">
        @php $monthly = ($plans ?? collect())->firstWhere('billing_interval', 'month'); @endphp

        @forelse(($plans ?? collect())->where('price', '!=', null) as $option)
            @php
                $saving = ($option->billing_interval === 'year' && $monthly)
                    ? (int) $monthly->price * 12 - (int) $option->price
                    : 0;
            @endphp

            <label class="flex items-center gap-4 p-4 rounded-lg border-2 mb-3 cursor-pointer transition-colors
                {{ $loop->first ? 'border-primary bg-primary/5' : 'border-outline-variant/40 hover:border-primary/40' }}">
                <input type="radio" name="interval" value="{{ $option->billing_interval }}"
                    @checked($loop->first)
                    class="plan-interval w-4 h-4 text-primary border-outline-variant focus:ring-primary/40 shrink-0">
                <span class="flex-1 min-w-0">
                    <span class="block font-label-md text-on-surface font-semibold">
                        {{ $option->billing_interval === 'year' ? 'Yearly' : 'Monthly' }}
                    </span>
                    @if($saving > 0)
                        <span class="block font-label-sm text-label-sm text-tertiary">
                            Save {{ \Laravel\Cashier\Cashier::formatAmount($saving, $option->currency) }} a year
                        </span>
                    @endif
                    @if(($promos ?? collect())->has($option->billing_interval))
                        {{-- The code itself, so the reader knows what to type at
                             checkout. --}}
                        <span class="block font-label-sm text-label-sm text-tertiary font-semibold">
                            Use code {{ $promos[$option->billing_interval]->promo_code }}
                            @if($promos[$option->billing_interval]->promo_expires_at)
                                — until {{ $promos[$option->billing_interval]->promo_expires_at->format('j M Y') }}
                            @endif
                        </span>
                    @endif
                </span>
                <span class="text-right shrink-0">
                    @php $promo = ($promos ?? collect())->get($option->billing_interval); @endphp
                    @if($promo)
                        {{-- An offer is running, so the list price is shown struck
                             through beside what a code actually brings it to. --}}
                        <span class="block font-label-sm text-label-sm text-on-surface-variant line-through">{{ $option->formatted_price }}</span>
                        <span class="block font-headline-md text-headline-md text-tertiary">{{ $promo->formatted_payable }}</span>
                    @else
                        <span class="block font-headline-md text-headline-md text-on-surface">{{ $option->formatted_price }}</span>
                    @endif
                    <span class="block font-label-sm text-label-sm text-on-surface-variant">/{{ $option->billing_interval }}</span>
                </span>
            </label>
        @empty
            <span class="font-headline-md text-headline-md text-on-surface-variant">Pricing coming soon</span>
        @endforelse
    </div>

    <ul class="flex-grow space-y-3 mb-8">
        <li class="flex items-start gap-3 font-body-md text-body-md text-on-surface">
            <span class="material-symbols-outlined text-primary text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">lock_open</span>
            All {{ $course->chapters_count }} {{ Str::plural('chapter', $course->chapters_count) }} unlocked
        </li>
        <li class="flex items-start gap-3 font-body-md text-body-md text-on-surface-variant">
            <span class="material-symbols-outlined text-primary/70 text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            Study notes, flashcards, MCQ and theory quizzes
        </li>
        <li class="flex items-start gap-3 font-body-md text-body-md text-on-surface-variant">
            <span class="material-symbols-outlined text-primary/70 text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            Video lessons, diagrams, guides and summaries
        </li>
        <li class="flex items-start gap-3 font-body-md text-body-md text-on-surface-variant">
            <span class="material-symbols-outlined text-primary text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">menu_book</span>
            Theory Guide, ATP Guide and the Worksheet Generator
        </li>
        <li class="flex items-start gap-3 font-body-md text-body-md text-on-surface-variant">
            <span class="material-symbols-outlined text-primary/70 text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">new_releases</span>
            New material as it is published
        </li>
    </ul>

    @if($subscribed)
        <div class="w-full py-4 rounded-full bg-tertiary-container/20 text-tertiary font-label-md text-label-md flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            You already subscribe to this course
        </div>
        <a href="{{ route('public.course.chapters', $course) }}"
            class="mt-3 text-center font-label-md text-label-md text-secondary hover:text-primary transition-colors">Go to the chapters</a>
    @elseif($plan?->isSellable())
        {{-- Opens the payment-method popup; the gateway takes it from there,
             and signing in happens on the way if the reader is not already. --}}
        <button type="button" data-open-payment
            class="w-full py-4 rounded-full bg-primary text-on-primary font-label-md text-label-md shadow-primary-glow hover:bg-primary/90 transition-colors scale-95 active:scale-90 flex items-center justify-center gap-2">
            Subscribe
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </button>
        <p class="mt-4 text-center font-label-sm text-label-sm text-on-surface-variant">
            Secure payment through Stripe. Cancel any time.
        </p>
    @else
        <div class="w-full py-4 rounded-full border border-outline-variant text-on-surface-variant font-label-md text-label-md text-center">
            Not on sale yet
        </div>
        <a href="{{ route('public.courses') }}"
            class="mt-3 text-center font-label-md text-label-md text-secondary hover:text-primary transition-colors">Browse the free chapters</a>
    @endif
</article>
</div>
</main>

@if($plan?->isSellable() && ! $subscribed)
    @include('public._payment-modal', ['course' => $course, 'plan' => $plan])
@endif
@endsection
