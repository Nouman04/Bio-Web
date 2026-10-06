{{--
    Import past papers into the course's question bank (no chapter yet).

    Each paper is a pair: the question paper, and optionally its mark scheme,
    whose answers are saved with the questions. Both are read in the browser,
    so every pair shows real progress.
--}}
<div id="import-pdf-modal" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('import-pdf-modal').classList.add('hidden')"></div>

    <div class="relative w-full max-w-2xl mx-4 bg-surface-container-lowest dark:bg-slate-800 rounded-2xl shadow-2xl border border-outline-variant/30 dark:border-slate-700 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/20 dark:border-slate-700">
            <div>
                <h3 class="text-base font-semibold text-on-surface dark:text-slate-100">Import Past Papers</h3>
                <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-0.5">
                    Into <span class="text-primary font-semibold">{{ $courseTitle }}</span>
                </p>
            </div>
            <button type="button" onclick="document.getElementById('import-pdf-modal').classList.add('hidden')"
                class="w-8 h-8 flex items-center justify-center rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="pdf-import-form" action="{{ route('courses.import.pdf', $courseId) }}" class="p-6 flex flex-col gap-5 max-h-[75vh] overflow-y-auto">
            <div class="flex flex-col gap-2">
                <div>
                    <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Papers</span>
                    <p class="text-[11px] text-on-surface-variant/80 dark:text-slate-500 mt-0.5">
                        One row per paper: the question paper, and its mark scheme for the answers. Without a mark scheme, questions are saved with no answer.
                    </p>
                </div>

                <div id="pdf-import-pairs" class="flex flex-col gap-3"></div>

                <button type="button" id="pdf-import-add"
                    class="self-start inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold text-primary hover:bg-primary/10 transition-colors">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add another paper
                </button>
            </div>

            <p class="text-[11px] text-on-surface-variant/80 dark:text-slate-500">
                Questions are saved to this course, without a chapter, with paper number, session and marks taken from the paper.
                Importing the same paper again skips questions already saved but still adds missing answers.
            </p>

            <div id="pdf-import-progress" class="hidden flex-col gap-2">
                <div class="flex items-center justify-between text-xs font-semibold text-on-surface dark:text-slate-200">
                    <span>Overall</span><span id="pdf-import-overall">0%</span>
                </div>
                <div class="h-1.5 w-full bg-surface-container-high dark:bg-slate-700 rounded-full overflow-hidden">
                    <div id="pdf-import-overall-bar" class="h-full bg-primary rounded-full transition-all duration-300" style="width:0%"></div>
                </div>
            </div>

            <p id="pdf-import-error" class="hidden text-xs text-error"></p>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('import-pdf-modal').classList.add('hidden')"
                    class="px-5 py-2 rounded-full border border-outline-variant text-on-surface-variant text-sm font-semibold hover:bg-surface-container-low transition-colors">Close</button>
                <button type="submit" id="pdf-import-submit"
                    class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center gap-2">
                    <i class="fa-solid fa-file-arrow-up text-xs"></i> Start Import
                </button>
            </div>
        </form>
    </div>
</div>

{{-- One paper: the file in each slot is taken as what the slot says. --}}
<template id="pdf-import-pair">
    <div class="pair rounded-xl border border-outline-variant/30 dark:border-slate-700 p-3 flex flex-col gap-2">
        <div class="flex items-center justify-between">
            <span class="pair-title text-xs font-semibold text-on-surface dark:text-slate-200"></span>
            <button type="button" class="pair-remove w-6 h-6 flex items-center justify-center rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors disabled:hidden" aria-label="Remove paper">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach ([['qp', 'Question paper', 'Required', 'fa-file-lines'], ['ms', 'Mark scheme (answers)', 'Optional', 'fa-file-circle-check']] as [$name, $title, $empty, $icon])
                <label class="slot flex items-center gap-3 border-2 border-dashed border-outline-variant/50 dark:border-slate-700 rounded-xl px-3 py-2.5 cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-colors min-w-0"
                    data-empty="{{ $empty }}" data-icon="{{ $icon }} text-on-surface-variant">
                    <i class="slot-icon fa-solid {{ $icon }} text-on-surface-variant"></i>
                    <span class="flex flex-col min-w-0">
                        <span class="text-xs font-semibold text-on-surface dark:text-slate-200">{{ $title }}</span>
                        <span class="slot-name text-[11px] text-on-surface-variant dark:text-slate-400 truncate">{{ $empty }}</span>
                    </span>
                    <input type="file" name="{{ $name }}" accept="application/pdf" data-no-file-name class="hidden">
                </label>
            @endforeach
        </div>
        <div class="pair-progress hidden flex-col gap-1">
            <div class="h-1 w-full bg-surface-container-high dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="bar h-full bg-primary rounded-full transition-all duration-300" style="width:0%"></div>
            </div>
            <span class="status text-xs font-semibold text-primary"></span>
        </div>
    </div>
</template>

@push('scripts')
    @vite('resources/js/past-paper-import.js')
@endpush
