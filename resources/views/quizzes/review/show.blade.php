@extends('layouts.app')

@section('title', 'Mark: ' . $quiz->title)
@section('page-title', 'Mark Quiz')
@section('page-subtitle', $quiz->title . ' · ' . ($student?->name ?? 'Student'))

@section('content')
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6 flex-wrap">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('quizzes.review') }}">To Mark</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">{{ $student?->name }}</span>
    </div>

    <form id="grade-form" data-ajax-form method="POST"
        action="{{ route('quizzes.review.grade', $attempt->uuid) }}"
        class="max-w-4xl flex flex-col gap-5">
        @csrf
        @method('PUT')

        {{-- What is being marked --}}
        <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl border border-outline-variant/20 dark:border-slate-700 p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-on-surface dark:text-slate-100 text-lg font-semibold">{{ $quiz->title }}</h2>
                    <p class="text-on-surface-variant dark:text-slate-400 text-xs mt-1">
                        {{ $student?->name }} &middot; handed in {{ $attempt->submitted_at?->diffForHumans() }}
                        @if($chapter) &middot; {{ $chapter->title }} @endif
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-on-surface-variant dark:text-slate-400 text-xs uppercase tracking-wide">Auto-marked so far</div>
                    <div class="text-on-surface dark:text-slate-100 text-lg font-bold tabular-nums" id="running-total">
                        — / {{ rtrim(rtrim((string) $attempt->total_marks, '0'), '.') }}
                    </div>
                </div>
            </div>
        </div>

        @foreach($report as $index => $row)
            <section class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl border border-outline-variant/20 dark:border-slate-700 p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <h3 class="text-on-surface dark:text-slate-100 font-semibold">
                        <span class="text-primary">{{ $index + 1 }}.</span>
                        <span class="[&_p]:mb-2 [&_p:last-child]:mb-0 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_li]:mb-0.5 [&_strong]:font-semibold [&_em]:italic [&_a]:text-primary [&_a]:underline [&_img]:rounded-lg [&_img]:max-w-full">{!! $row['question'] !!}</span>
                    </h3>
                    <span class="text-xs font-semibold text-on-surface-variant bg-surface-container-high dark:bg-slate-700 px-2 py-1 rounded-full shrink-0">
                        out of {{ rtrim(rtrim((string) $row['marks'], '0'), '.') }}
                    </span>
                </div>

                @if($row['is_mcq'])
                    {{-- Already marked; shown for context, not for editing. --}}
                    <div class="flex items-center gap-2 text-sm {{ $row['correct'] ? 'text-tertiary' : 'text-error' }}">
                        <i class="fa-solid {{ $row['correct'] ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                        {{ $row['correct'] ? 'Answered correctly' : 'Answered incorrectly' }}
                        <span class="text-on-surface-variant">— marked automatically</span>
                    </div>
                @else
                    <div class="bg-surface-container-low dark:bg-slate-900/40 border border-outline-variant/20 dark:border-slate-700 rounded-xl p-4 mb-4">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-on-surface-variant dark:text-slate-500 mb-2">Student's answer</div>
                        <p class="text-sm text-on-surface dark:text-slate-200 whitespace-pre-line">{{ $row['written'] ?: 'Left blank.' }}</p>
                    </div>

                    @if($row['expected'])
                        <div class="bg-tertiary/5 border-l-4 border-tertiary rounded-r-xl p-3 mb-4">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-tertiary mb-1">Expected answer</div>
                            <div class="text-sm text-on-surface dark:text-slate-200 [&_p]:mb-2 [&_p:last-child]:mb-0 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_li]:mb-0.5 [&_strong]:font-semibold [&_em]:italic [&_a]:text-primary [&_a]:underline [&_img]:rounded-lg [&_img]:max-w-full">{!! $row['expected'] !!}</div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Marks</label>
                            <input type="number" step="0.25" min="0" max="{{ $row['marks'] }}"
                                name="marks[{{ $row['answer_id'] }}][marks]"
                                value="{{ $row['awarded'] }}"
                                data-max="{{ $row['marks'] }}"
                                class="grade-mark w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface dark:text-slate-200">
                        </div>
                        <div class="sm:col-span-3 flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Feedback <span class="font-normal text-outline">(optional)</span></label>
                            <input type="text" name="marks[{{ $row['answer_id'] }}][feedback]" value="{{ $row['feedback'] }}"
                                placeholder="What would have earned full marks?"
                                class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface dark:text-slate-200">
                        </div>
                    </div>
                @endif
            </section>
        @endforeach

        <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl border border-outline-variant/20 dark:border-slate-700 p-6 shadow-sm flex flex-col gap-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Overall note to the student <span class="font-normal text-outline">(optional)</span></label>
                <textarea name="feedback" rows="3" placeholder="A sentence on how they did…"
                    class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface dark:text-slate-200"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('quizzes.review') }}"
                    class="px-5 py-2 rounded-full border border-outline-variant text-on-surface-variant text-sm font-semibold hover:bg-surface-container-low transition-colors">Cancel</a>
                <button type="submit" data-loading-text="Saving…"
                    class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center gap-2">
                    <i class="fa-solid fa-check text-xs"></i>
                    Save marks &amp; notify student
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    // Keeps every box inside the marks that question is worth, and shows the
    // running total so the instructor can see where the paper lands.
    document.addEventListener('DOMContentLoaded', () => {
        const boxes = Array.from(document.querySelectorAll('.grade-mark'));
        const total = document.getElementById('running-total');
        const outOf = total?.textContent.split('/')[1]?.trim() ?? '';

        function refresh() {
            let sum = 0;
            boxes.forEach(box => {
                const max = parseFloat(box.dataset.max) || 0;
                let value = parseFloat(box.value);

                if (!isNaN(value)) {
                    if (value > max) { value = max; box.value = max; }
                    if (value < 0) { value = 0; box.value = 0; }
                    sum += value;
                }
            });

            if (total) {
                total.textContent = `${sum} / ${outOf}`;
            }
        }

        boxes.forEach(box => box.addEventListener('input', refresh));
        refresh();
    });
</script>
@endpush
