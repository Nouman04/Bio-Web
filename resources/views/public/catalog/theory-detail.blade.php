@extends('public.layouts.app')

@section('title', $quiz->title . ' — Theory Practice | Lumina LMS')

@include('public.catalog._styles')

@push('styles')
<style>
    .theory-answer[hidden] { display: none; }
</style>
@endpush

@section('content')
<main class="max-w-container-max mx-auto px-md md:px-lg py-lg md:py-xl min-h-[calc(100vh-160px)]">
<header class="mb-lg relative z-10">
@include('public.catalog._breadcrumb', ['crumbs' => [
    'Courses' => route('public.courses'),
    $course->title => route('public.course.chapters', $course),
    'Chapter ' . $chapter->chapter_number => route('public.course.chapter.show', [$course, $chapter]),
    'Theory Practice' => route('public.course.chapter.theory', [$course, $chapter]),
    $quiz->title => '#',
]])
<h1 class="font-display-lg text-display-lg text-on-surface mt-2">{{ $quiz->title }}</h1>
<div class="flex flex-wrap items-center gap-md mt-3 font-label-md text-label-md text-on-surface-variant">
<span class="flex items-center gap-xs"><span class="material-symbols-outlined text-[18px] text-primary">edit_document</span>{{ $questions->count() }} {{ Str::plural('question', $questions->count()) }}</span>
<span class="flex items-center gap-xs"><span class="material-symbols-outlined text-[18px] text-primary">workspace_premium</span>{{ $questions->sum(fn ($q) => (float) $q['marks']) }} marks total</span>
@if($quiz->duration)
<span class="flex items-center gap-xs"><span class="material-symbols-outlined text-[18px] text-primary">timer</span>{{ $quiz->duration }} minutes</span>
@endif
</div>
</header>

@if($questions->isEmpty())
<div class="glass-panel rounded-xl p-lg text-center relative z-10">
<span class="material-symbols-outlined text-primary text-[40px] mb-2">edit_document</span>
<h2 class="font-headline-md text-headline-md text-on-surface mb-2">This paper has no questions yet</h2>
<a href="{{ route('public.course.chapter.theory', [$course, $chapter]) }}" class="font-label-md text-primary hover:text-primary-container">Back to theory practice</a>
</div>
@else
<div class="flex flex-col gap-lg relative z-10">
@foreach($questions as $question)
<section class="glass-panel rounded-xl p-md md:p-lg">
<div class="flex items-start justify-between gap-md">
<div class="flex-grow">
<span class="font-label-md text-label-md text-primary tracking-widest uppercase mb-2 block">Question {{ $loop->iteration }}</span>
<h2 class="font-headline-md text-headline-md text-on-surface max-w-3xl">{{ $question['text'] }}</h2>
</div>
<div class="flex flex-col items-end gap-1 shrink-0">
<span class="font-label-sm text-label-sm text-on-surface-variant bg-surface-container-high px-2 py-1 rounded-md whitespace-nowrap">[{{ $question['marks'] }}]</span>
@if($question['difficulty'])
<span class="font-label-sm text-label-sm text-secondary">{{ $question['difficulty'] }}</span>
@endif
</div>
</div>

<!-- Answer space, ruled like an exam booklet -->
<div class="mt-md">
<label class="font-label-sm text-label-sm text-on-surface-variant mb-2 block" for="answer-{{ $loop->index }}">Your answer</label>
<textarea id="answer-{{ $loop->index }}" rows="5"
    class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest p-md font-body-md text-body-md text-on-surface placeholder:text-outline focus:border-primary focus:ring-2 focus:ring-primary/15 outline-none resize-y"
    placeholder="Write your answer here — nothing is submitted or stored."></textarea>
</div>

@if($question['answer'])
<div class="mt-md pt-md border-t border-glass-stroke">
<button type="button" data-reveal
    class="font-label-md text-label-md text-primary hover:text-primary-container inline-flex items-center gap-xs transition-colors">
<span class="material-symbols-outlined text-[18px]">visibility</span>
                        Show model answer
                    </button>
<div class="theory-answer mt-sm font-body-md text-body-md text-on-surface bg-primary/5 border border-primary/20 rounded-lg p-md" hidden>
    {{ $question['answer'] }}
</div>
</div>
@endif
</section>
@endforeach
</div>

<div class="mt-lg text-center relative z-10">
<a href="{{ route('public.course.chapter.theory', [$course, $chapter]) }}"
    class="font-label-md text-label-md text-secondary hover:text-on-surface transition-colors">Back to theory practice</a>
</div>
@endif
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-reveal]').forEach(button => {
            button.addEventListener('click', () => {
                const answer = button.parentElement.querySelector('.theory-answer');
                answer.hidden = !answer.hidden;
                button.querySelector('span').textContent = answer.hidden ? 'visibility' : 'visibility_off';
                button.lastChild.textContent = answer.hidden ? ' Show model answer ' : ' Hide model answer ';
            });
        });
    });
</script>
@endpush
