@php
    // Both question types, so a written question can be either.
    $questionTypes = \App\Models\QuestionCategory::orderBy('type')->get(['id', 'type']);
@endphp
{{--
    Question picker.

    Search the bank with Tom Select (type-ahead, multi-select), and/or write
    brand new questions — each with its own type, and options plus a correct
    answer when that type is MCQ. Posts `question_ids[]` for existing ones and
    `new_questions[i][…]` for new.
--}}
<div class="question-widget flex flex-col gap-2"
     data-field-name="{{ $qwFieldName ?? 'question_ids' }}"
     data-new-field-name="{{ $qwNewFieldName ?? 'new_questions' }}"
     data-search-url="{{ route('questions.search') }}">
    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">{{ $qwLabel ?? 'Linked Questions (Optional)' }}</label>

    {{-- Caps at ~22rem: queueing new questions scrolls this panel instead of
         stretching the modal it sits in. --}}
    <div class="border border-outline-variant/40 dark:border-slate-700 rounded-xl bg-surface-container-lowest/60 dark:bg-slate-900/40 p-4 flex flex-col gap-3 max-h-[22rem] overflow-y-auto custom-scrollbar">
        {{-- Select existing questions --}}
        <div class="flex flex-col gap-1.5">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-on-surface-variant/70 dark:text-slate-500">Select existing questions</span>
            <select class="question-widget-select" multiple placeholder="Type to search the question bank…" autocomplete="off"></select>
        </div>

        <div class="flex items-center gap-3">
            <div class="h-px bg-outline-variant/30 dark:bg-slate-700 flex-1"></div>
            <span class="text-[10px] font-semibold uppercase tracking-wider text-on-surface-variant/60 dark:text-slate-500">or write new ones</span>
            <div class="h-px bg-outline-variant/30 dark:bg-slate-700 flex-1"></div>
        </div>

        {{-- Compose a brand new question --}}
        <div class="question-widget-composer flex flex-col gap-2">
            <input type="text"
                class="question-widget-new-input w-full bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none"
                placeholder="Type a new question…">

            <div class="flex flex-col sm:flex-row gap-2">
                <select class="question-widget-new-type flex-1 bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    @foreach($questionTypes as $type)
                        <option value="{{ $type->id }}" data-type="{{ $type->type }}">{{ $type->type === 'mcqs' ? 'MCQ' : 'Theory' }}</option>
                    @endforeach
                </select>
                <button type="button" class="question-widget-add-new shrink-0 flex items-center justify-center gap-2 px-4 py-2 bg-tertiary/10 text-tertiary text-sm font-semibold rounded-lg hover:bg-tertiary/20 transition-colors">
                    <i class="fa-solid fa-plus text-xs"></i> Add
                </button>
            </div>

            {{-- Only for MCQs: the options and which one is correct --}}
            <div class="question-widget-new-options hidden flex-col gap-2 pl-3 border-l-2 border-tertiary/30">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-on-surface-variant/70 dark:text-slate-500">
                    Options <span class="font-normal normal-case tracking-normal text-outline">— tick the correct answer</span>
                </span>
                <ul class="question-widget-option-list flex flex-col gap-2"></ul>
                <button type="button" class="question-widget-option-add self-start text-xs font-semibold text-primary hover:text-primary/80 transition-colors inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add option
                </button>
            </div>

            {{-- Where the question came from, if it came from a past paper.
                 Optional as a whole; once opened, everything but the source is
                 required — see LinksQuestions::validateNewQuestions(). --}}
            @if($qwPastPaper ?? false)
                <div class="question-widget-paper-wrap flex flex-col gap-2 pl-3 border-l-2 border-primary/30">
                    <label class="inline-flex items-center gap-2 self-start cursor-pointer">
                        <input type="checkbox" class="question-widget-paper-toggle w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary/40">
                        <span class="text-[11px] font-semibold uppercase tracking-wide text-on-surface-variant/70 dark:text-slate-500">
                            From a past paper
                        </span>
                    </label>

                    <div class="question-widget-paper hidden flex-col gap-2">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-semibold uppercase tracking-wide text-outline">Date</span>
                                <input type="date" class="question-widget-paper-date w-full bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-semibold uppercase tracking-wide text-outline">Paper No</span>
                                <input type="text" class="question-widget-paper-no w-full bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none" placeholder="e.g. 2">
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-semibold uppercase tracking-wide text-outline">Marks</span>
                                <input type="number" step="0.25" min="0" class="question-widget-paper-marks w-full bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none" placeholder="e.g. 6">
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-outline">Source <span class="font-normal normal-case tracking-normal">— optional</span></span>
                            <input type="text" class="question-widget-paper-source w-full bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none" placeholder="e.g. Cambridge IGCSE 0610">
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Queued new questions — scrolls once the list grows --}}
        <div class="question-widget-new-wrap hidden flex-col gap-1.5">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-on-surface-variant/70 dark:text-slate-500">
                    New questions <span class="question-widget-count">(0)</span>
                </span>
                <button type="button" class="question-widget-clear text-[11px] font-semibold text-on-surface-variant hover:text-error transition-colors">Clear all</button>
            </div>
            <ul class="question-widget-list flex flex-col gap-2"></ul>
        </div>
    </div>
</div>
