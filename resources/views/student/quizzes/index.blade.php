@extends('layouts.student')

@section('title', 'My Quizzes')
@section('meta-description', 'Every quiz set for you — what is still to do, and how the rest went.')

@push('styles')
<style>
    .glass-panel {
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.4);
        transition: all 0.25s ease;
    }
    .glass-panel:hover { box-shadow: 0 10px 30px rgba(0, 19, 48, 0.08); }
</style>
@endpush

@section('content')

@php
    $anyFilter = collect($filters)->filter()->isNotEmpty();
@endphp

{{-- Header --}}
<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-6 pt-4">
    <div>
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-3 flex-wrap">
            <a href="{{ route('student.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface font-semibold">My Quizzes</span>
        </nav>
        <h1 class="text-on-background" style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.01em;">My Quizzes</h1>
        <p class="text-on-surface-variant text-base mt-1 max-w-2xl">
            @if($anyFilter)
                <span class="font-semibold text-primary">{{ $quizzes->total() }}</span>
                {{ Str::plural('quiz', $quizzes->total()) }} matched.
            @elseif($outstanding)
                <span class="font-semibold text-primary">{{ $outstanding }}</span>
                {{ Str::plural('quiz', $outstanding) }} still to sit, then your latest attempt at each of the rest.
            @else
                Every quiz set for you, showing how your latest attempt at each one went.
            @endif
        </p>
    </div>
</div>

{{-- Filters. The same bar the chapter listings use, so this page does not
     introduce a sixth design. --}}
<div class="mb-8">
    <form method="GET" id="quiz-filters" class="flex flex-wrap gap-3 items-center">
        <x-chapter-filter.select name="course" icon="school" :auto="false">
            <option value="">All courses</option>
            @foreach($courses as $option)
                <option value="{{ $option->uuid }}" @selected($filters['course'] === $option->uuid)>{{ $option->title }}</option>
            @endforeach
        </x-chapter-filter.select>

        <x-chapter-filter.select name="chapter" icon="menu_book" :auto="false">
            <option value="">All chapters</option>
            @foreach($chapters as $option)
                <option value="{{ $option->uuid }}" @selected($filters['chapter'] === $option->uuid)>{{ $option->title }}</option>
            @endforeach
        </x-chapter-filter.select>

        <x-chapter-filter.select name="status" icon="hourglass_top" :auto="false">
            <option value="">Any state</option>
            @foreach(\App\Services\StudentQuizListService::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
            @endforeach
        </x-chapter-filter.select>

        <x-chapter-filter.select name="result" icon="flag" :auto="false">
            <option value="">Any result</option>
            @foreach(\App\Services\StudentQuizListService::RESULTS as $value => $label)
                <option value="{{ $value }}" @selected($filters['result'] === $value)>{{ $label }}</option>
            @endforeach
        </x-chapter-filter.select>

        <button type="submit" class="bg-primary-container text-on-primary-container text-sm font-semibold py-2 px-4 rounded-lg flex items-center gap-2 hover:bg-primary hover:text-on-primary transition-colors">
            <span class="material-symbols-outlined" style="font-size:18px;">filter_list</span> Filter
        </button>

        @if($anyFilter)
            <a href="{{ route('student.quizzes') }}"
                class="text-on-surface-variant hover:text-primary text-sm flex items-center gap-1 transition-colors">
                <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span> Clear
            </a>
        @endif
    </form>
</div>

{{-- The list --}}
<div class="flex flex-col gap-3">
    @forelse($quizzes as $row)
        @php
            // Written out in full so the classes survive a Tailwind build.
            [$tone, $icon] = match ($row['status']) {
                'not_started' => ['bg-surface-container-high text-on-surface-variant', 'radio_button_unchecked'],
                'in_progress' => ['bg-primary/10 text-primary', 'play_circle'],
                'pending_review' => ['bg-secondary/10 text-secondary', 'hourglass_top'],
                'self_marked' => ['bg-primary/10 text-primary', 'fact_check'],
                'expired' => ['bg-error/10 text-error', 'timer_off'],
                default => $row['passed']
                    ? ['bg-tertiary/10 text-tertiary', 'check_circle']
                    : ['bg-error/10 text-error', 'cancel'],
            };

            $sat = $row['attempts'];
        @endphp

        <article class="glass-panel rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="min-w-0">
                <h2 class="text-on-surface font-semibold truncate">{{ $row['title'] }}</h2>
                <p class="text-on-surface-variant text-xs mt-1 truncate">
                    @if($row['course'])
                        {{ $row['course'] }}
                        @if($row['chapter']) &middot; {{ $row['chapter'] }} @endif
                        &middot;
                    @endif
                    {{ $row['sat_at']?->diffForHumans() ?? ($sat ? 'not yet handed in' : 'not sat yet') }}
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                {{-- How many times this quiz has been sat, not just the latest.
                     Nothing to say about one that has never been opened. --}}
                @if($sat)
                    <span class="inline-flex items-center gap-1.5 bg-surface-container-high text-on-surface-variant text-xs font-semibold px-3 py-1 rounded-full whitespace-nowrap"
                        title="You have sat this quiz {{ $sat }} {{ Str::plural('time', $sat) }}">
                        <span class="material-symbols-outlined" style="font-size:14px;">replay</span>
                        {{ $sat }} {{ Str::plural('attempt', $sat) }}
                    </span>
                @endif

                @if($row['earned_marks'] !== null)
                    <span class="text-on-surface font-semibold tabular-nums">
                        {{ rtrim(rtrim(number_format($row['earned_marks'], 2, '.', ''), '0'), '.') }}<span class="text-on-surface-variant font-normal">/{{ rtrim(rtrim(number_format($row['total_marks'] ?? 0, 2, '.', ''), '0'), '.') }}</span>
                    </span>
                @endif

                <span class="inline-flex items-center gap-1.5 {{ $tone }} text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
                    <span class="material-symbols-outlined" style="font-size:14px;">{{ $icon }}</span>
                    {{ $row['status_label'] }}
                </span>

                {{-- Somewhere to go: sit it, pick it back up, or read the
                     marking — whichever the quiz is waiting on. --}}
                @if(in_array($row['status'], ['not_started', 'in_progress'], true))
                    @if($row['url'])
                        <a href="{{ $row['url'] }}"
                            class="bg-primary text-on-primary text-xs font-semibold py-2 px-4 rounded-full flex items-center gap-1.5 hover:bg-primary/90 transition-colors">
                            {{ $row['status'] === 'in_progress' ? 'Resume quiz' : 'Start quiz' }}
                            <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                        </a>
                    @endif
                @elseif($row['attempt_uuid'])
                    {{-- Sitting it again is a fresh attempt: the service only
                         picks a previous one back up while it is still open, so
                         this link starts a new one. The old attempt and its
                         marking stay where they are. --}}
                    @if($row['url'])
                        <a href="{{ $row['url'] }}"
                            class="border border-outline-variant text-on-surface-variant text-xs font-semibold py-2 px-4 rounded-full flex items-center gap-1.5 hover:border-primary hover:text-primary transition-colors">
                            <span class="material-symbols-outlined" style="font-size:16px;">replay</span>
                            Try again
                        </a>
                    @endif

                    <a href="{{ route('student.quizzes.report', $row['attempt_uuid']) }}"
                        class="bg-primary text-on-primary text-xs font-semibold py-2 px-4 rounded-full flex items-center gap-1.5 hover:bg-primary/90 transition-colors">
                        View marking
                        <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                    </a>
                @endif
            </div>
        </article>
    @empty
        <div class="glass-panel rounded-2xl py-20 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 rounded-full bg-primary/5 flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-primary text-4xl">{{ $anyFilter ? 'search_off' : 'quiz' }}</span>
            </div>
            <h3 class="text-on-surface font-semibold text-lg mb-2">
                {{ $anyFilter ? 'Nothing matched' : 'No quizzes are set for you yet' }}
            </h3>
            <p class="text-on-surface-variant text-sm max-w-md mb-6">
                @if($anyFilter)
                    No quiz of yours matches those filters.
                @else
                    Quizzes set on your courses appear here — the ones still to sit first, then
                    your result and the marking for the rest.
                @endif
            </p>
            <a href="{{ $anyFilter ? route('student.quizzes') : route('student.courses') }}"
                class="bg-primary text-on-primary text-sm font-semibold py-2.5 px-6 rounded-full inline-flex items-center gap-2">
                {{ $anyFilter ? 'Clear filters' : 'Go to my courses' }}
                <span class="material-symbols-outlined text-sm">{{ $anyFilter ? 'restart_alt' : 'arrow_forward' }}</span>
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

@push('scripts')
<script>
    // The chapter list is narrowed to the chosen course, so a chapter picked
    // under the old course would filter to nothing. Clear it as the course
    // changes; the refreshed list arrives when Filter is pressed.
    document.getElementById('quiz-filters')?.addEventListener('change', (event) => {
        if (event.target.name !== 'course') return;

        const chapter = event.target.form.elements.chapter;
        if (chapter) chapter.value = '';
    }, true);
</script>
@endpush
