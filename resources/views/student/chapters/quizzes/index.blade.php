@extends('layouts.student')

@section('title', 'Quizzes – ' . $chapter->title)
@section('meta-description', 'Assessments for ' . $chapter->title)

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .glass-card {
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.4);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        box-shadow: 0 10px 30px rgba(0, 19, 48, 0.08);
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')

{{-- Breadcrumb + Header --}}
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 pt-4">
    <div>
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-3 flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', $course) }}" class="hover:text-primary transition-colors">{{ $course->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">{{ $chapter->title }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface font-semibold">Quizzes</span>
        </nav>
        <h1 class="text-on-background" style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;">Chapter Assessment</h1>
        <p class="text-on-surface-variant text-base mt-1 max-w-2xl">
            @if($search || $type)
                <span class="font-semibold text-primary">{{ $quizzes->total() }}</span>
                {{ Str::plural('quiz', $quizzes->total()) }} matched in {{ $chapter->title }}.
            @else
                Every quiz set on {{ $chapter->title }}.
            @endif
        </p>
    </div>

    {{-- Search and kind, both handled on the server. --}}
    <x-chapter-filter placeholder="Search quizzes"
        :search="$search"
        :active="$search || $type"
        :clear="route('student.chapters.quizzes', ['courseId' => $courseId, 'chapterId' => $chapterId])">

        <x-chapter-filter.select name="type">
            <option value="">All kinds</option>
            @foreach(\App\Http\Resources\QuizResource::TYPE_LABELS as $value => $label)
                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
            @endforeach
        </x-chapter-filter.select>
    </x-chapter-filter>
</div>

{{-- Quizzes Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @forelse($quizzes as $quiz)
        @php
            $sat = $attempts[$quiz['id']] ?? null;

            // Written out in full so the classes survive a Tailwind build.
            $badge = match ($quiz['type']) {
                'mcqs' => 'bg-primary-container/20 text-primary border-primary/20',
                'theory' => 'bg-tertiary-container/20 text-tertiary border-tertiary/20',
                default => 'bg-secondary-container/20 text-secondary border-secondary/20',
            };

            // What the button offers depends on where the last attempt got to.
            [$action, $icon] = match (true) {
                $sat === null => ['Start quiz', 'play_arrow'],
                $sat['open'] => ['Resume', 'play_arrow'],
                default => ['Take again', 'restart_alt'],
            };
        @endphp

        <article class="glass-card rounded-2xl p-6 flex flex-col h-full group">
            <div class="flex justify-between items-start mb-4 gap-2">
                <div class="flex gap-2 flex-wrap min-w-0">
                    <span class="{{ $badge }} text-xs font-bold px-3 py-1 rounded-full border">{{ $quiz['type_label'] }}</span>
                    @if($sat && $sat['passed'])
                        <span class="inline-flex items-center gap-1 text-tertiary text-xs font-semibold" title="You have passed this quiz">
                            <span class="material-symbols-outlined" style="font-size:16px;font-variation-settings:'FILL' 1;">check_circle</span>
                            Passed
                        </span>
                    @elseif($sat && $sat['awaiting'])
                        <span class="inline-flex items-center gap-1 text-on-surface-variant text-xs font-semibold" title="Your written answers are with the instructor">
                            <span class="material-symbols-outlined" style="font-size:16px;">hourglass_top</span>
                            Being marked
                        </span>
                    @endif
                </div>
                @if($sat)
                    <span class="text-on-surface-variant text-xs shrink-0">
                        {{ $sat['count'] }} {{ Str::plural('attempt', $sat['count']) }}
                    </span>
                @endif
            </div>

            <h3 class="text-on-surface font-semibold text-base mb-2 group-hover:text-primary transition-colors">{{ $quiz['title'] }}</h3>

            @if($quiz['excerpt'])
                <p class="text-on-surface-variant text-sm mb-4 line-clamp-2">{{ $quiz['excerpt'] }}</p>
            @endif

            {{-- What sitting it involves. --}}
            <ul class="text-on-surface-variant text-xs space-y-1.5 mb-5 flex-1">
                <li class="flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:16px;">help</span>
                    {{ $quiz['questions_count'] }} {{ Str::plural('question', $quiz['questions_count']) }}
                    @if($quiz['total_marks'])
                        &middot; {{ rtrim(rtrim(number_format($quiz['total_marks'], 2, '.', ''), '0'), '.') }} marks
                    @endif
                </li>
                <li class="flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size:16px;">schedule</span>
                    {{ $quiz['duration'] ? $quiz['duration'] . ' minutes' : 'No time limit' }}
                </li>
                @if($quiz['passing_score'] !== null)
                    <li class="flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:16px;">flag</span>
                        Pass at {{ $quiz['passing_score'] }}
                    </li>
                @endif
            </ul>

            {{-- The best result so far, once there is one worth showing. --}}
            @if($sat && $sat['best_marks'] !== null)
                <div class="bg-surface-container-lowest border border-outline-variant/40 rounded-lg px-4 py-2.5 mb-4 flex items-center justify-between">
                    <span class="text-on-surface-variant text-xs">Best so far</span>
                    <span class="text-on-surface text-sm font-semibold">
                        {{ rtrim(rtrim(number_format($sat['best_marks'], 2, '.', ''), '0'), '.') }}@if($sat['best_total'])<span class="text-on-surface-variant font-normal">/{{ rtrim(rtrim(number_format($sat['best_total'], 2, '.', ''), '0'), '.') }}</span>@endif
                    </span>
                </div>
            @endif

            <div class="pt-4 border-t border-outline-variant/30 flex items-center justify-between mt-auto gap-2">
                @if($sat)
                    <a href="{{ route('student.quizzes.report', $sat['latest_uuid']) }}"
                        class="text-on-surface-variant text-xs font-semibold hover:text-primary transition-colors shrink-0">
                        Last report
                    </a>
                @else
                    <span class="text-on-surface-variant text-xs">Not attempted</span>
                @endif

                @if($quiz['questions_count'] > 0)
                    <a href="{{ route('student.chapters.quizzes.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'quizId' => $quiz['uuid']]) }}"
                        class="bg-primary text-on-primary text-xs font-semibold py-2 px-4 rounded-full flex items-center gap-1.5 hover:bg-primary/90 transition-colors shrink-0">
                        {{ $action }}
                        <span class="material-symbols-outlined" style="font-size:16px;">{{ $icon }}</span>
                    </a>
                @else
                    {{-- No questions on it yet: offering to start would open an
                         empty paper. --}}
                    <span class="text-on-surface-variant text-xs border border-outline-variant/60 rounded-full py-2 px-4 shrink-0">
                        Not ready yet
                    </span>
                @endif
            </div>
        </article>
    @empty
        <div class="col-span-full glass-card rounded-2xl py-20 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 rounded-full bg-primary/5 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary text-4xl">{{ $search || $type ? 'search_off' : 'quiz' }}</span>
            </div>
            <h3 class="text-on-surface font-semibold text-lg mb-2">
                {{ $search || $type ? 'Nothing matched' : 'No assessments yet' }}
            </h3>
            <p class="text-on-surface-variant text-sm max-w-md mb-6">
                @if($search || $type)
                    No quiz in this chapter matches those filters.
                @else
                    No assessments have been set for this chapter yet. Check back soon.
                @endif
            </p>
            <a href="{{ $search || $type
                    ? route('student.chapters.quizzes', ['courseId' => $courseId, 'chapterId' => $chapterId])
                    : route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full inline-flex items-center gap-2">
                {{ $search || $type ? 'Clear filters' : 'Back to the chapter' }}
                <span class="material-symbols-outlined text-sm">{{ $search || $type ? 'restart_alt' : 'arrow_forward' }}</span>
            </a>
        </div>
    @endforelse
</div>

@if($quizzes->hasPages())
    <div class="mt-10 flex justify-center">
        {{ $quizzes->links() }}
    </div>
@endif

@endsection
