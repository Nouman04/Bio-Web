@extends('layouts.student')

@section('title', 'Flashcard Practice')

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

@php
    $cards = [
        1 => [
            'question' => 'What is the time complexity of searching for an element in an unsorted array?',
            'answer' => 'O(n)',
            'explanation' => 'In the worst case, you might have to check every single element in the array to find the target value, making the time complexity linear.',
        ],
        2 => [
            'question' => 'What is the time complexity of searching for an element in a sorted array using binary search?',
            'answer' => 'O(log n)',
            'explanation' => 'Binary search halves the remaining search space with each comparison, so the number of steps grows logarithmically with the input size.',
        ],
        3 => [
            'question' => 'What is the time complexity of inserting an element at the end of a dynamic array?',
            'answer' => 'O(1) amortized',
            'explanation' => 'Appending is constant time on average, since the array only needs to resize (and copy) occasionally as it grows.',
        ],
        4 => [
            'question' => 'What is the time complexity of inserting an element at the beginning of an array?',
            'answer' => 'O(n)',
            'explanation' => 'Every existing element must shift one position to the right to make room, which takes time proportional to the array size.',
        ],
        5 => [
            'question' => 'What is the space complexity of a typical array-based data structure?',
            'answer' => 'O(n)',
            'explanation' => 'Memory usage grows linearly with the number of elements stored, since each element occupies its own contiguous slot.',
        ],
    ];
    $totalCards = count($cards);
    $flashcardId = max(1, min($totalCards, (int) $flashcardId));
    $card = $cards[$flashcardId];
    $progress = round(($flashcardId / $totalCards) * 100);
@endphp

@section('content')
<div class="flex-1 flex flex-col items-center justify-center relative py-8">
    <!-- Breadcrumb back to list -->
    <div class="w-full max-w-3xl mb-8 flex items-center justify-between">
        <a href="{{ route('student.chapters.flashcards', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="text-on-surface-variant hover:text-primary transition-colors flex items-center gap-2 group w-max">
            <span class="material-symbols-outlined group-hover:-translate-x-1 transition-transform text-sm">arrow_back</span>
            <span class="text-sm font-semibold">Exit Study Mode</span>
        </a>
        <button id="saveCardBtn" onclick="toggleSaveIcon(this)" class="text-on-surface-variant hover:text-primary transition-colors flex items-center gap-2" title="Save card">
            <span class="material-symbols-outlined text-sm">bookmark_border</span>
            <span class="text-sm font-semibold" data-save-label="Save Card">Save Card</span>
        </button>
    </div>

    <!-- Background decorative elements -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/5 rounded-full blur-3xl -z-10"></div>

    <!-- Progress Bar Container -->
    <div class="w-full max-w-3xl mb-6">
        <div class="flex justify-between mb-2">
            <span class="text-sm text-on-surface-variant" id="progressLabel">Progress &middot; Card {{ $flashcardId }} of {{ $totalCards }}</span>
            <span class="text-sm text-primary font-semibold" id="progressPercent">{{ $progress }}%</span>
        </div>
        <div class="h-2 w-full bg-surface-container rounded-full overflow-hidden shadow-inner">
            <div id="progressBarFill" class="h-full bg-gradient-to-r from-primary to-primary-container rounded-full transition-all duration-500 ease-out shadow-sm" style="width: {{ $progress }}%;"></div>
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
                <h2 id="cardQuestion" class="text-2xl font-semibold text-on-background mt-4">{{ $card['question'] }}</h2>
                <div class="absolute bottom-6 w-full flex justify-center opacity-50 group-hover:opacity-100 transition-opacity">
                    <span class="text-sm font-semibold text-primary flex items-center gap-2 bg-primary/5 px-4 py-1.5 rounded-full">
                        <span class="material-symbols-outlined text-base">touch_app</span>
                        Tap to reveal answer
                    </span>
                </div>
            </div>
            <!-- Answer card -->
            <div class="fc-answer absolute w-full h-full bg-surface-container-lowest rounded-xl p-8 flex flex-col items-center justify-center border-2 border-primary/20">
                <span class="absolute top-6 left-6 text-sm font-semibold text-primary uppercase tracking-wide flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">lightbulb</span>
                    Answer
                </span>
                <p id="cardAnswer" class="text-5xl text-primary font-bold">{{ $card['answer'] }}</p>
                <p id="cardExplanation" class="text-lg text-on-surface-variant mt-4 max-w-lg">{{ $card['explanation'] }}</p>
            </div>
        </div>
    </div>

    <!-- Controls Area -->
    <div class="w-full max-w-3xl flex flex-col gap-4">
        <!-- Navigation Controls -->
        <div class="flex justify-between items-center bg-surface glass-panel px-6 py-4 rounded-xl shadow-sm">
            <a id="prevBtn" href="{{ $flashcardId > 1 ? route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => $flashcardId - 1]) : '#' }}"
               onclick="return goToCard(-1)"
               class="flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg transition-colors {{ $flashcardId > 1 ? 'text-on-surface-variant hover:text-primary hover:bg-primary/5' : 'text-on-surface-variant/40 pointer-events-none' }}">
                <span class="material-symbols-outlined text-sm">arrow_back_ios_new</span>
                Previous
            </a>
            <a id="nextBtn" href="{{ $flashcardId < $totalCards ? route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => $flashcardId + 1]) : '#' }}"
               onclick="return goToCard(1)"
               class="flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg transition-colors {{ $flashcardId < $totalCards ? 'text-on-surface hover:text-primary hover:bg-primary/5' : 'text-on-surface-variant/40 pointer-events-none' }}">
                Next
                <span class="material-symbols-outlined text-sm">arrow_forward_ios</span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const fcCards = @json($cards);
    const fcTotal = {{ $totalCards }};
    let fcCurrentId = {{ $flashcardId }};
    let fcAnimating = false;
    const fcBaseUrl = '{{ route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => '__ID__']) }}';

    function fcUrlFor(id) {
        return fcBaseUrl.replace('__ID__', id);
    }

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

    function fcResetSaveButton() {
        const saveBtn = document.getElementById('saveCardBtn');
        if (!saveBtn) return;
        saveBtn.classList.remove('is-saved');
        const icon = saveBtn.querySelector('.material-symbols-outlined');
        icon.textContent = 'bookmark_border';
        icon.style.fontVariationSettings = "'FILL' 0";
        icon.style.color = '';
        const label = saveBtn.querySelector('[data-save-label]');
        if (label) label.textContent = label.dataset.saveLabel;
    }

    function fcRenderCard(id) {
        const card = fcCards[id];

        document.getElementById('cardQuestion').textContent = card.question;
        document.getElementById('cardAnswer').textContent = card.answer;
        document.getElementById('cardExplanation').textContent = card.explanation;

        const progress = Math.round((id / fcTotal) * 100);
        document.getElementById('progressLabel').textContent = 'Progress · Card ' + id + ' of ' + fcTotal;
        document.getElementById('progressPercent').textContent = progress + '%';
        document.getElementById('progressBarFill').style.width = progress + '%';

        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        const prevEnabled = id > 1;
        const nextEnabled = id < fcTotal;

        prevBtn.href = prevEnabled ? fcUrlFor(id - 1) : '#';
        nextBtn.href = nextEnabled ? fcUrlFor(id + 1) : '#';

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

        fcResetSaveButton();
        history.replaceState(null, '', fcUrlFor(id));
    }

    function goToCard(direction) {
        const targetId = fcCurrentId + direction;
        if (fcAnimating || targetId < 1 || targetId > fcTotal) return false;

        fcAnimating = true;
        const stage = document.getElementById('flashcardStage');

        // Always reset the flip before swapping content so the incoming card
        // starts on the question side and never shows a stale/mismatched answer.
        stage.classList.remove('is-flipped');

        const outClass = direction > 0 ? 'fc-anim-out-left' : 'fc-anim-out-right';
        stage.classList.add(outClass);

        setTimeout(() => {
            fcCurrentId = targetId;
            fcRenderCard(fcCurrentId);

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
</script>
@endpush
@endsection
