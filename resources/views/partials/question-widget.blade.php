{{--
    Question picker.

    Search the bank with Tom Select (type-ahead, multi-select), and/or write
    brand new questions which are queued in a scrollable list and created on
    submit. Posts `question_ids[]` for existing and `new_questions[]` for new.
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

        {{-- Write brand new questions --}}
        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text"
                class="question-widget-new-input flex-1 bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none"
                placeholder="Type a new question, then press Enter…">
            <button type="button" class="question-widget-add-new shrink-0 flex items-center justify-center gap-2 px-4 py-2 bg-tertiary/10 text-tertiary text-sm font-semibold rounded-lg hover:bg-tertiary/20 transition-colors">
                <i class="fa-solid fa-plus text-xs"></i> Add
            </button>
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
