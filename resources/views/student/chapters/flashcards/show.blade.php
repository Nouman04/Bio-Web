@extends('layouts.student')

@section('title', 'Flashcard Practice')

@push('styles')
<style>
    .fc-perspective {
        perspective: 1000px;
        -webkit-perspective: 1000px;
    }
    .fc-preserve-3d {
        transform-style: preserve-3d;
        -webkit-transform-style: preserve-3d;
    }
    .fc-backface-hidden {
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
    }
    .fc-rotate-y-180 {
        transform: rotateY(180deg);
        -webkit-transform: rotateY(180deg);
    }
    .flashcard-inner {
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .is-flipped .flashcard-inner {
        transform: rotateY(180deg);
        -webkit-transform: rotateY(180deg);
    }
    /* Explicit visibility toggle: the correct face shows regardless of
       backface-visibility/3D transform support, the rotateY above is purely cosmetic. */
    .fc-front {
        opacity: 1;
        transition: opacity 0s linear 0.3s;
    }
    .fc-back {
        opacity: 0;
        pointer-events: none;
        transition: opacity 0s linear 0.3s;
    }
    .is-flipped .fc-front {
        opacity: 0;
        pointer-events: none;
    }
    .is-flipped .fc-back {
        opacity: 1;
        pointer-events: auto;
    }
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
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
    <div class="w-full max-w-3xl mb-8">
        <a href="{{ route('student.chapters.flashcards', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="text-on-surface-variant hover:text-primary transition-colors flex items-center gap-2 group w-max">
            <span class="material-symbols-outlined group-hover:-translate-x-1 transition-transform text-sm">arrow_back</span>
            <span class="text-sm font-semibold">Exit Study Mode</span>
        </a>
    </div>

    <!-- Background decorative elements -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-primary/5 rounded-full blur-3xl -z-10"></div>
    
    <!-- Progress Bar Container -->
    <div class="w-full max-w-3xl mb-6">
        <div class="flex justify-between mb-2">
            <span class="text-sm text-on-surface-variant">Progress &middot; Card {{ $flashcardId }} of {{ $totalCards }}</span>
            <span class="text-sm text-primary font-semibold">{{ $progress }}%</span>
        </div>
        <div class="h-2 w-full bg-surface-container rounded-full overflow-hidden shadow-inner">
            <div class="h-full bg-gradient-to-r from-primary to-primary-container rounded-full transition-all duration-500 ease-out shadow-sm" style="width: {{ $progress }}%;"></div>
        </div>
    </div>

    <!-- Flashcard Container -->
    <div class="fc-perspective w-full max-w-3xl h-[400px] mb-6 group cursor-pointer" onclick="this.classList.toggle('is-flipped')">
        <div class="flashcard-inner relative w-full h-full text-center fc-preserve-3d shadow-xl rounded-xl transition-transform duration-500 glass-panel hover:shadow-2xl hover:shadow-primary/10">
            <!-- Front side (Question) -->
            <div class="absolute w-full h-full fc-backface-hidden fc-front bg-surface-container-lowest rounded-xl p-8 flex flex-col items-center justify-center border border-on-surface/5">
                <span class="absolute top-6 left-6 text-sm text-on-surface-variant uppercase tracking-wide flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">help_outline</span>
                    Question
                </span>
                <h2 class="text-2xl font-semibold text-on-background mt-4">{{ $card['question'] }}</h2>
                <div class="absolute bottom-6 w-full flex justify-center opacity-50 group-hover:opacity-100 transition-opacity">
                    <span class="text-sm font-semibold text-primary flex items-center gap-2 bg-primary/5 px-4 py-1.5 rounded-full">
                        <span class="material-symbols-outlined text-base">touch_app</span>
                        Tap to reveal answer
                    </span>
                </div>
            </div>
            <!-- Back side (Answer) -->
            <div class="absolute w-full h-full fc-backface-hidden fc-back bg-surface-container-lowest rounded-xl p-8 flex flex-col items-center justify-center border-2 border-primary/20 fc-rotate-y-180">
                <span class="absolute top-6 left-6 text-sm font-semibold text-primary uppercase tracking-wide flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">lightbulb</span>
                    Answer
                </span>
                <p class="text-5xl text-primary font-bold">{{ $card['answer'] }}</p>
                <p class="text-lg text-on-surface-variant mt-4 max-w-lg">{{ $card['explanation'] }}</p>
            </div>
        </div>
    </div>

    <!-- Controls Area -->
    <div class="w-full max-w-3xl flex flex-col gap-4">
        <!-- Navigation Controls -->
        <div class="flex justify-between items-center bg-surface glass-panel px-6 py-4 rounded-xl shadow-sm">
            @if ($flashcardId > 1)
                <a href="{{ route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => $flashcardId - 1]) }}" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors text-sm font-semibold px-4 py-2 rounded-lg hover:bg-primary/5">
                    <span class="material-symbols-outlined text-sm">arrow_back_ios_new</span>
                    Previous
                </a>
            @else
                <span class="flex items-center gap-2 text-on-surface-variant/40 text-sm font-semibold px-4 py-2 rounded-lg cursor-not-allowed">
                    <span class="material-symbols-outlined text-sm">arrow_back_ios_new</span>
                    Previous
                </span>
            @endif
            @if ($flashcardId < $totalCards)
                <a href="{{ route('student.chapters.flashcards.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'flashcardId' => $flashcardId + 1]) }}" class="flex items-center gap-2 text-on-surface hover:text-primary transition-colors text-sm font-semibold px-4 py-2 rounded-lg hover:bg-primary/5">
                    Next
                    <span class="material-symbols-outlined text-sm">arrow_forward_ios</span>
                </a>
            @else
                <span class="flex items-center gap-2 text-on-surface-variant/40 text-sm font-semibold px-4 py-2 rounded-lg cursor-not-allowed">
                    Next
                    <span class="material-symbols-outlined text-sm">arrow_forward_ios</span>
                </span>
            @endif
        </div>
    </div>
</div>
@endsection
