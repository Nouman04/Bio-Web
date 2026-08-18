@php
    // Through the chain the edit page keeps the course › chapter context.
    $editRoute = ($chain ?? null)
        ? route('courses.chapters.quizzes.edit', [$chain['course']->id, $chain['chapter']->id, $quiz->id])
        : route('quizzes.edit', $quiz->id);
@endphp
{{-- Title column — mirrors the chapters grid: name, then one muted meta line --}}
@php
    $meta = array_filter([
        $quiz->questions_count . ($quiz->questions_count === 1 ? ' question' : ' questions'),
        $quiz->duration ? $quiz->duration . ' mins' : null,
        $quiz->passing_score !== null ? 'pass ' . rtrim(rtrim(number_format($quiz->passing_score, 2), '0'), '.') . ' marks' : null,
        \App\Http\Controllers\QuizController::TYPES[$quiz->type] ?? null,
    ]);
@endphp
<div class="flex flex-col gap-0.5 min-w-0">
    <a href="{{ $editRoute }}"
        class="font-semibold text-on-surface dark:text-white hover:text-primary transition-colors truncate">
        {{ $quiz->title }}
    </a>
    <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
        {{ implode(' · ', $meta) }}
    </span>
</div>
