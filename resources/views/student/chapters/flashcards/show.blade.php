@extends('layouts.student')

@section('title', $deck->title . ' – Flashcard Practice')

@push('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }

    /* Two separate cards (question / answer), toggled via opacity — no 3D
       transforms or backface-visibility involved, so there's nothing that can
       render a mirrored or blank face. */
    .fc-squish {
        transition: transform 0.15s ease-in;
    }
    .fc-squish.fc-squished {
        transform: scaleX(0.05);
    }
    .fc-question,
    .fc-answer {
        transition: opacity 0.1s linear;
    }
    .fc-question {
        opacity: 1;
    }
    .fc-answer {
        opacity: 0;
        pointer-events: none;
    }
    .is-flipped .fc-question {
        opacity: 0;
        pointer-events: none;
    }
    .is-flipped .fc-answer {
        opacity: 1;
        pointer-events: auto;
    }

    /* Slide transition for Previous / Next navigation */
    #flashcardStage {
        transition: transform 0.28s ease-out, opacity 0.28s ease-out;
    }
    #flashcardStage.fc-anim-out-left   { transform: translateX(-24px); opacity: 0; }
    #flashcardStage.fc-anim-out-right  { transform: translateX(24px);  opacity: 0; }
    #flashcardStage.fc-anim-in-from-right { transition: none !important; transform: translateX(24px);  opacity: 0; }
    #flashcardStage.fc-anim-in-from-left  { transition: none !important; transform: translateX(-24px); opacity: 0; }
</style>
@endpush

@section('content')
<div class="flex-1 flex flex-col items-center justify-center relative py-8">

    <!-- Breadcrumb back to list -->
    <div class="w-full max-w-3xl mb-6 flex items-center justify-between gap-4">
        <a href="{{ route('student.chapters.flashcards', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
            class="text-on-surface-variant hover:text-primary transition-colors flex items-center gap-2 group w-max">
            <span class="material-symbols-outlined group-hover:-translate-x-1 transition-transform text-sm">arrow_back</span>
            <span class="text-sm font-semibold">Exit Study Mode</span>
        </a>
        <span class="text-on-surface-variant text-xs truncate">{{ $chapter->title }}</span>
    </div>

    <div class="w-full max-w-3xl mb-6">
        <h1 class="text-on-background" style="font-size:24px;line-height:32px;font-weight:700;">{{ $deck->title }}</h1>
    </div>

    <!-- Background decorative elements -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/5 rounded-full blur-3xl -z-10"></div>

    @if($cards->isEmpty())
        <div class="w-full max-w-3xl glass-panel rounded-xl py-20 flex flex-col items-center text-center">
            <div class="w-20 h-20 rounded-full bg-primary/5 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary text-4xl">style</span>
            </div>
            <h2 class="text-on-surface font-semibold text-lg mb-2">This deck is empty</h2>
            <p class="text-on-surface-variant text-sm max-w-md mb-6">No questions have been added to it yet.</p>
            <a href="{{ route('student.chapters.flashcards', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full inline-flex items-center gap-2">
                Back to the decks <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>
    @else
        @php $first = $cards->first(); @endphp

        <!-- Progress Bar Container -->
        <div class="w-full max-w-3xl mb-6">
            <div class="flex justify-between mb-2">
                <span class="text-sm text-on-surface-variant" id="progressLabel">Progress &middot; Card 1 of {{ $cards->count() }}</span>
                <span class="text-sm text-primary font-semibold" id="progressPercent">{{ round(100 / $cards->count()) }}%</span>
            </div>
            <div class="h-2 w-full bg-surface-container rounded-full overflow-hidden shadow-inner">
                <div id="progressBarFill" class="h-full bg-gradient-to-r from-primary to-primary-container rounded-full transition-all duration-500 ease-out shadow-sm"
                    style="width: {{ round(100 / $cards->count()) }}%;"></div>
            </div>
        </div>

        <!-- Flashcard Container -->
        <div id="flashcardStage" class="w-full max-w-3xl h-[400px] mb-6 group cursor-pointer" onclick="toggleFlip()">
            <div id="flashcardInner" class="fc-squish relative w-full h-full text-center shadow-xl rounded-xl glass-panel hover:shadow-2xl hover:shadow-primary/10">
                <!-- Question card -->
                <div class="fc-question absolute w-full h-full bg-surface-container-lowest rounded-xl p-8 flex flex-col items-center justify-center border border-on-surface/5">
                    <span class="absolute top-6 left-6 text-sm text-on-surface-variant uppercase tracking-wide flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">help_outline</span>
                        Question
                    </span>
                    <h2 id="cardQuestion" class="text-2xl font-semibold text-on-background mt-4 px-4">{{ $first['question'] }}</h2>
                    <div class="absolute bottom-6 w-full flex justify-center opacity-50 group-hover:opacity-100 transition-opacity">
                        <span class="text-sm font-semibold text-primary flex items-center gap-2 bg-primary/5 px-4 py-1.5 rounded-full">
                            <span class="material-symbols-outlined text-base">touch_app</span>
                            Tap to reveal answer
                        </span>
                    </div>
                </div>
                <!-- Answer card -->
                <div class="fc-answer absolute w-full h-full bg-surface-container-lowest rounded-xl p-8 flex flex-col items-center justify-center border-2 border-primary/20 overflow-y-auto">
                    <span class="absolute top-6 left-6 text-sm font-semibold text-primary uppercase tracking-wide flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">lightbulb</span>
                        Answer
                    </span>
                    {{-- Sized down from the mockup's 5xl: a real answer is a
                         sentence, not a big-O symbol. --}}
                    <p id="cardAnswer" class="text-2xl text-primary font-bold px-4 mt-6">{{ $first['answer'] }}</p>
                    <p id="cardExplanation" class="text-base text-on-surface-variant mt-4 max-w-lg px-4">{{ $first['explanation'] }}</p>
                </div>
            </div>
        </div>

        <!-- Controls Area -->
        <div class="w-full max-w-3xl flex flex-col gap-4">
            <div class="flex justify-between items-center bg-surface glass-panel px-6 py-4 rounded-xl shadow-sm">
                <button id="prevBtn" type="button" onclick="goToCard(-1)"
                    class="flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg transition-colors text-on-surface-variant/40 pointer-events-none">
                    <span class="material-symbols-outlined text-sm">arrow_back_ios_new</span>
                    Previous
                </button>
                <button id="nextBtn" type="button" onclick="goToCard(1)"
                    class="flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg transition-colors {{ $cards->count() > 1 ? 'text-on-surface hover:text-primary hover:bg-primary/5' : 'text-on-surface-variant/40 pointer-events-none' }}">
                    Next
                    <span class="material-symbols-outlined text-sm">arrow_forward_ios</span>
                </button>
            </div>
        </div>
    @endif
</div>

@if($cards->isNotEmpty())
@push('scripts')
<script>
    // The deck's cards, in the order they were arranged.
    const fcCards = @json($cards);
    const fcTotal = fcCards.length;
    let fcIndex = 0;
    let fcAnimating = false;

    // Pinch the card flat (scaleX), swap which of the two cards is showing,
    // then release it back open — a purely 2D "flip" with no 3D transform
    // or backface-visibility involved.
    function toggleFlip() {
        const stage = document.getElementById('flashcardStage');
        const inner = document.getElementById('flashcardInner');

        inner.classList.add('fc-squished');
        setTimeout(() => {
            stage.classList.toggle('is-flipped');
            inner.classList.remove('fc-squished');
        }, 150);
    }

    function fcRender(index) {
        const card = fcCards[index];

        document.getElementById('cardQuestion').textContent = card.question;
        document.getElementById('cardAnswer').textContent = card.answer;
        document.getElementById('cardExplanation').textContent = card.explanation;

        const position = index + 1;
        const progress = Math.round((position / fcTotal) * 100);
        document.getElementById('progressLabel').textContent = 'Progress · Card ' + position + ' of ' + fcTotal;
        document.getElementById('progressPercent').textContent = progress + '%';
        document.getElementById('progressBarFill').style.width = progress + '%';

        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const prevEnabled = index > 0;
        const nextEnabled = index < fcTotal - 1;

        prevBtn.classList.toggle('pointer-events-none', !prevEnabled);
        prevBtn.classList.toggle('text-on-surface-variant/40', !prevEnabled);
        prevBtn.classList.toggle('text-on-surface-variant', prevEnabled);
        prevBtn.classList.toggle('hover:text-primary', prevEnabled);
        prevBtn.classList.toggle('hover:bg-primary/5', prevEnabled);

        nextBtn.classList.toggle('pointer-events-none', !nextEnabled);
        nextBtn.classList.toggle('text-on-surface-variant/40', !nextEnabled);
        nextBtn.classList.toggle('text-on-surface', nextEnabled);
        nextBtn.classList.toggle('hover:text-primary', nextEnabled);
        nextBtn.classList.toggle('hover:bg-primary/5', nextEnabled);
    }

    function goToCard(direction) {
        const target = fcIndex + direction;

        if (fcAnimating || target < 0 || target > fcTotal - 1) {
            return false;
        }

        fcAnimating = true;
        const stage = document.getElementById('flashcardStage');

        // Always reset the flip before swapping content so the incoming card
        // starts on the question side and never shows a stale/mismatched answer.
        stage.classList.remove('is-flipped');

        const outClass = direction > 0 ? 'fc-anim-out-left' : 'fc-anim-out-right';
        stage.classList.add(outClass);

        setTimeout(() => {
            fcIndex = target;
            fcRender(fcIndex);

            stage.classList.remove(outClass);
            const inClass = direction > 0 ? 'fc-anim-in-from-right' : 'fc-anim-in-from-left';
            stage.classList.add(inClass);

            // Force a reflow so the "no transition" starting position is registered
            // before we remove the class and let it animate back to center.
            void stage.offsetWidth;

            stage.classList.remove(inClass);

            setTimeout(() => { fcAnimating = false; }, 280);
        }, 280);

        return false;
    }

    // Arrow keys step the deck; space flips the card.
    document.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') { goToCard(1); }
        else if (event.key === 'ArrowLeft') { goToCard(-1); }
        else if (event.key === ' ') { event.preventDefault(); toggleFlip(); }
    });
</script>
@endpush
@endif

@endsection
