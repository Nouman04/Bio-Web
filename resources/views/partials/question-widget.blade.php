@php
    $questionBank = [
        ['id' => 1, 'text' => 'What is the time complexity of searching for an element in a balanced Binary Search Tree (BST)?'],
        ['id' => 2, 'text' => 'Explain the concept of polymorphic dispatch in object-oriented programming with a real-world example.'],
        ['id' => 3, 'text' => 'Which of the following data structures operates on a Last-In, First-Out (LIFO) principle?'],
        ['id' => 4, 'text' => 'Describe the difference between TCP and UDP protocols and give a use case for each.'],
        ['id' => 5, 'text' => 'What is the output of the following Python code snippet involving list comprehensions?'],
    ];
@endphp
<div class="question-widget flex flex-col gap-2"
     data-field-name="{{ $qwFieldName ?? 'question_ids' }}"
     data-new-field-name="{{ $qwNewFieldName ?? 'new_questions' }}">
    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">{{ $qwLabel ?? 'Linked Questions (Optional)' }}</label>
    <div class="border border-outline-variant/40 dark:border-slate-700 rounded-xl bg-surface-container-lowest/60 dark:bg-slate-900/40 p-4 flex flex-col gap-3">
        {{-- Link an existing question --}}
        <div class="flex flex-col sm:flex-row gap-2">
            <select class="question-widget-select flex-1 bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                <option value="">Select an existing question…</option>
                @foreach ($questionBank as $q)
                    <option value="{{ $q['id'] }}" data-text="{{ $q['text'] }}">{{ \Illuminate\Support\Str::limit($q['text'], 70) }}</option>
                @endforeach
            </select>
            <button type="button" class="question-widget-add shrink-0 flex items-center justify-center gap-2 px-4 py-2 bg-primary/10 text-primary text-sm font-semibold rounded-lg hover:bg-primary/20 transition-colors">
                <i class="fa-solid fa-link text-xs"></i> Link
            </button>
        </div>

        <div class="flex items-center gap-3">
            <div class="h-px bg-outline-variant/30 dark:bg-slate-700 flex-1"></div>
            <span class="text-[10px] font-semibold uppercase tracking-wider text-on-surface-variant/60 dark:text-slate-500">or write a new one</span>
            <div class="h-px bg-outline-variant/30 dark:bg-slate-700 flex-1"></div>
        </div>

        {{-- Create a brand new question --}}
        <div class="flex flex-col sm:flex-row gap-2">
            <input type="text" class="question-widget-new-input flex-1 bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none" placeholder="Type a new question…">
            <button type="button" class="question-widget-add-new shrink-0 flex items-center justify-center gap-2 px-4 py-2 bg-tertiary/10 text-tertiary text-sm font-semibold rounded-lg hover:bg-tertiary/20 transition-colors">
                <i class="fa-solid fa-plus text-xs"></i> Add New
            </button>
        </div>

        <ul class="question-widget-list flex flex-col gap-2"></ul>
        <p class="question-widget-empty text-xs text-on-surface-variant dark:text-slate-500 italic">No questions linked yet — link an existing one or write a new one above.</p>
    </div>
</div>
