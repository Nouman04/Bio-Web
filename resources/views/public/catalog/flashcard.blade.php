@extends('public.layouts.app')

@section('title', $deck->title . ' — Flashcards | Lumina LMS')

@include('public.catalog._styles')

@push('styles')
<style>
    /* The glow sits behind the card, so the card itself has to be positioned. */
    .ambient-glow { position: relative; }
</style>
@endpush

@section('content')
<main class="flex-grow w-full max-w-container-max mx-auto px-md md:px-lg py-lg md:py-xl flex flex-col md:flex-row gap-lg relative z-10">

@if($cards->isEmpty())
<section class="w-full glass-panel rounded-xl p-lg text-center">
<span class="material-symbols-outlined text-primary text-[40px] mb-2">style</span>
<h1 class="font-headline-md text-headline-md text-on-surface mb-2">{{ $deck->title }} has no cards yet</h1>
<a href="{{ route('public.course.chapter.flashcards', [$course, $chapter]) }}" class="font-label-md text-primary hover:text-primary-container">Back to all sets</a>
</section>
@else

<!-- Left Sidebar: Context & Session -->
<aside class="w-full md:w-64 flex-shrink-0 space-y-md">
<!-- Back/Context Link -->
<a class="inline-flex items-center gap-xs text-secondary hover:text-primary font-label-md text-label-md transition-colors active:scale-95 group mb-sm"
    href="{{ route('public.course.chapter.flashcards', [$course, $chapter]) }}">
<span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
                {{ $course->title }}: {{ $chapter->title }}
            </a>

<!-- Deck Title -->
<div class="glass-panel p-md rounded-lg">
<h1 class="font-headline-lg-mobile md:font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-xs">{{ $deck->title }}</h1>
<p class="font-body-md text-body-md text-secondary">{{ $cards->count() }} {{ Str::plural('card', $cards->count()) }} in this set.</p>
</div>

<!-- Session Stats Panel -->
<div class="glass-panel p-md rounded-lg space-y-md">
<div class="flex items-center gap-xs text-primary font-label-md text-label-md mb-sm">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">analytics</span>
                    Session Progress
                </div>
<!-- Circular Progress -->
<div class="flex justify-center items-center py-sm relative">
<svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 100 100">
<circle class="text-surface-container-high" cx="50" cy="50" fill="transparent" r="40" stroke="currentColor" stroke-width="8"></circle>
<circle id="progress-ring" class="text-primary transition-all duration-500 ease-in-out" cx="50" cy="50" fill="transparent" r="40" stroke="currentColor" stroke-dasharray="251.2" stroke-dashoffset="251.2" stroke-width="8"></circle>
</svg>
<div class="absolute inset-0 flex flex-col items-center justify-center">
<span id="stat-done" class="font-headline-md text-headline-md text-on-background">0</span>
<span class="font-label-sm text-label-sm text-secondary uppercase tracking-wider">of {{ $cards->count() }}</span>
</div>
</div>
<div class="space-y-sm pt-sm border-t border-glass-stroke">
<div class="flex justify-between items-center font-label-md text-label-md">
<span class="flex items-center gap-xs text-secondary">
<span class="w-2 h-2 rounded-full bg-outline-variant"></span> Remaining</span>
<span id="stat-remaining" class="text-on-background font-semibold">{{ $cards->count() }}</span>
</div>
<div class="flex justify-between items-center font-label-md text-label-md">
<span class="flex items-center gap-xs text-secondary">
<span class="w-2 h-2 rounded-full bg-primary"></span> Got It</span>
<span id="stat-known" class="text-on-background font-semibold">0</span>
</div>
<div class="flex justify-between items-center font-label-md text-label-md">
<span class="flex items-center gap-xs text-secondary">
<span class="w-2 h-2 rounded-full bg-error"></span> Needs Review</span>
<span id="stat-review" class="text-on-background font-semibold">0</span>
</div>
</div>
</div>

<!-- Quick Actions -->
<div class="flex flex-col gap-sm pt-sm">
<button id="session-restart" type="button"
    class="w-full flex items-center justify-center gap-sm py-sm px-md rounded-DEFAULT font-label-md text-label-md text-secondary hover:text-on-background hover:bg-surface-container-high transition-all active:scale-95 border border-transparent hover:border-glass-stroke">
<span class="material-symbols-outlined">restart_alt</span>
                    Restart Session
                </button>
</div>
</aside>

<!-- Main Flashcard Area -->
<section class="flex-grow flex flex-col items-center justify-center min-h-[500px] w-full relative">
<!-- Context Hint -->
<div class="absolute top-0 left-0 right-0 flex justify-center pb-md z-20">
<span class="glass-panel px-md py-xs rounded-full font-label-sm text-label-sm text-secondary flex items-center gap-xs">
<span class="material-symbols-outlined text-[16px]">touch_app</span>
                    Click card to flip
                </span>
</div>

<!-- The Flashcard, flanked by its step arrows. The arrows show at every width
     — they are the only way to reach the other cards. -->
<div class="w-full flex items-center justify-center gap-sm md:gap-md">
<button id="card-prev" type="button" aria-label="Previous card" title="Previous card"
    class="flex w-11 h-11 md:w-14 md:h-14 shrink-0 rounded-full glass-panel items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary/40 hover:shadow-primary-glow transition-all active:scale-95">
<span class="material-symbols-outlined md:text-[28px]">chevron_left</span>
</button>

<div id="deck-card" class="flex-1 min-w-0 max-w-2xl aspect-[4/3] md:aspect-[16/10] perspective-1000 cursor-pointer ambient-glow flip-card"
    onclick="this.classList.toggle('flipped')">
<div class="w-full h-full relative transform-style-3d flip-card-inner">
<!-- Front (Question) -->
<div class="absolute inset-0 w-full h-full backface-hidden glass-panel rounded-xl flex flex-col items-center justify-center p-xl shadow-lg border-2 border-transparent hover:border-primary/20 transition-colors">
<span class="absolute top-md left-md font-label-sm text-label-sm text-secondary uppercase tracking-wider">Question</span>
<span class="absolute top-md right-md font-label-sm text-label-sm text-outline"><span id="card-index">1</span> / {{ $cards->count() }}</span>
<h2 id="card-front" class="font-headline-lg text-headline-lg md:text-display-lg text-on-background text-center"></h2>
</div>
<!-- Back (Answer) -->
<div class="absolute inset-0 w-full h-full backface-hidden glass-panel rounded-xl flex flex-col items-center justify-center p-xl shadow-lg border-2 border-primary-fixed rotate-y-180 bg-surface-container-lowest">
<span class="absolute top-md left-md font-label-sm text-label-sm text-secondary uppercase tracking-wider">Answer</span>
<div class="max-w-md text-center space-y-md">
<p id="card-back" class="font-body-lg text-body-lg text-on-surface"></p>
</div>
</div>
</div>
</div>

<button id="card-next" type="button" aria-label="Next card" title="Next card"
    class="flex w-11 h-11 md:w-14 md:h-14 shrink-0 rounded-full glass-panel items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary/40 hover:shadow-primary-glow transition-all active:scale-95">
<span class="material-symbols-outlined md:text-[28px]">chevron_right</span>
</button>
</div>

<!-- Action Controls -->
<div class="mt-lg w-full max-w-2xl flex items-center justify-center gap-md">
<button id="mark-review" type="button"
    class="flex-1 max-w-[200px] flex items-center justify-center gap-sm py-sm px-md rounded-lg font-label-md text-label-md border-2 border-outline-variant text-on-surface-variant hover:bg-surface-container hover:border-outline transition-all active:scale-95">
<span class="material-symbols-outlined">close</span>
                    Still Learning
                </button>
<button id="mark-known" type="button"
    class="flex-1 max-w-[200px] flex items-center justify-center gap-sm py-sm px-md rounded-lg font-label-md text-label-md bg-primary text-on-primary hover:bg-surface-tint shadow-[0_4px_14px_0_rgba(0, 19, 48, 0.39)] transition-all active:scale-95">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">check</span>
                    Got It
                </button>
</div>

<!-- Where you are in the deck -->
<p class="mt-md font-label-md text-label-md text-on-surface-variant">
    Card <span id="card-index-foot">1</span> of {{ $cards->count() }}
</p>

<!-- Shown once every card has been judged -->
<div id="session-done" class="mt-lg glass-panel rounded-xl px-lg py-md text-center hidden">
<p class="font-headline-md text-headline-md text-on-surface mb-xs">Set complete</p>
<p class="font-body-md text-body-md text-on-surface-variant"><span id="done-summary"></span></p>
</div>
</section>
@endif
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cards = @json($cards);
        if (!cards.length) return;

        const card = document.getElementById('deck-card');
        const front = document.getElementById('card-front');
        const back = document.getElementById('card-back');
        const index = document.getElementById('card-index');
        const ring = document.getElementById('progress-ring');
        const done = document.getElementById('session-done');
        const summary = document.getElementById('done-summary');
        const prev = document.getElementById('card-prev');
        const next = document.getElementById('card-next');
        const indexFoot = document.getElementById('card-index-foot');

        // The ring is r=40, so its circumference is 2πr ≈ 251.2 — the same
        // number the dasharray uses.
        const CIRCUMFERENCE = 251.2;

        let at = 0;
        let verdicts = new Array(cards.length).fill(null);

        function show() {
            // Always land on the question side when the card changes.
            card.classList.remove('flipped');
            front.textContent = cards[at].question;
            back.textContent = cards[at].answer || 'No answer recorded for this card.';
            index.textContent = at + 1;
            indexFoot.textContent = at + 1;
            refreshStats();
        }

        function refreshStats() {
            const known = verdicts.filter(v => v === 'known').length;
            const review = verdicts.filter(v => v === 'review').length;
            const judged = known + review;

            document.getElementById('stat-done').textContent = judged;
            document.getElementById('stat-known').textContent = known;
            document.getElementById('stat-review').textContent = review;
            document.getElementById('stat-remaining').textContent = cards.length - judged;

            ring.setAttribute('stroke-dashoffset', CIRCUMFERENCE * (1 - judged / cards.length));

            const finished = judged === cards.length;
            done.classList.toggle('hidden', !finished);
            if (finished) {
                summary.textContent = `${known} known · ${review} to review`;
            }
        }

        function judge(verdict) {
            verdicts[at] = verdict;
            // Move on to the next unjudged card, or stay put at the end.
            const nextUnjudged = verdicts.findIndex((v, i) => v === null && i > at);
            at = nextUnjudged !== -1 ? nextUnjudged : Math.min(at + 1, cards.length - 1);
            show();
        }

        document.getElementById('mark-known').addEventListener('click', () => judge('known'));
        document.getElementById('mark-review').addEventListener('click', () => judge('review'));

        // Wraps around, so both arrows stay usable on the first and last card.
        function step(by) {
            at = (at + by + cards.length) % cards.length;
            show();
        }

        prev.addEventListener('click', () => step(-1));
        next.addEventListener('click', () => step(1));

        document.getElementById('session-restart').addEventListener('click', () => {
            verdicts = new Array(cards.length).fill(null);
            at = 0;
            show();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') step(-1);
            if (e.key === 'ArrowRight') step(1);
            if (e.key === ' ') { e.preventDefault(); card.classList.toggle('flipped'); }
        });

        show();
    });
</script>
@endpush
