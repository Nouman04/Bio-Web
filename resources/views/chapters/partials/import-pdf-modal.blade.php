{{--
    Import one or more past-paper PDFs into a chapter's question bank.

    Each PDF is read in the browser, so the page shows real progress per PDF
    (pages read, then saving) and across the whole batch.
--}}
@php
    $pdfChapters = \App\Models\Chapter::where('course_id', $courseId->id)->orderBy('chapter_number')->get(['id', 'chapter_number', 'title']);
@endphp
<div id="import-pdf-modal" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="document.getElementById('import-pdf-modal').classList.add('hidden')"></div>

    <div class="relative w-full max-w-2xl mx-4 bg-surface-container-lowest dark:bg-slate-800 rounded-2xl shadow-2xl border border-outline-variant/30 dark:border-slate-700 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/20 dark:border-slate-700">
            <div>
                <h3 class="text-base font-semibold text-on-surface dark:text-slate-100">Import Past Paper PDFs</h3>
                <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-0.5">
                    Into <span class="text-primary font-semibold">{{ $courseTitle }}</span>
                </p>
            </div>
            <button type="button" onclick="document.getElementById('import-pdf-modal').classList.add('hidden')"
                class="w-8 h-8 flex items-center justify-center rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 flex flex-col gap-4 max-h-[70vh] overflow-y-auto">
            <ul class="text-[11px] text-on-surface-variant/80 dark:text-slate-500 flex flex-col gap-1 list-disc pl-4">
                <li>Each numbered question is saved with its tables and figures, under the topic <span class="font-semibold">Past Paper Questions</span>.</li>
                <li>Paper number and session come from the cover page; marks come from each question's <span class="font-semibold">[Total]</span>.</li>
                <li>Uploading the same PDF again skips questions already saved.</li>
            </ul>

            <form id="pdf-import-form" action="{{ route('courses.import.pdf', $courseId) }}" class="flex flex-col gap-3">
                <label class="flex flex-col gap-1">
                    <span class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter</span>
                    <select name="chapter_id" class="rounded-xl border border-outline-variant/40 dark:border-slate-700 bg-surface-container-lowest dark:bg-slate-900 text-sm px-3 py-2">
                        <option value="">Choose a chapter…</option>
                        @foreach ($pdfChapters as $chapter)
                            <option value="{{ $chapter->id }}">{{ $chapter->chapter_number }}. {{ $chapter->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label for="pdf-import-files"
                    class="border-2 border-dashed border-outline-variant/50 dark:border-slate-700 rounded-xl p-6 flex flex-col items-center justify-center text-center cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-colors group">
                    <div class="w-10 h-10 rounded-full bg-primary-container/20 text-primary flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <span class="text-sm font-semibold text-on-surface dark:text-slate-200">Choose PDFs</span>
                    <span id="pdf-import-names" class="text-xs text-on-surface-variant dark:text-slate-400 mt-1">One or more question papers</span>
                    <input id="pdf-import-files" type="file" accept="application/pdf" multiple data-no-file-name class="hidden">
                </label>

                <p id="pdf-import-error" class="hidden text-xs text-error"></p>

                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" onclick="document.getElementById('import-pdf-modal').classList.add('hidden')"
                        class="px-5 py-2 rounded-full border border-outline-variant text-on-surface-variant text-sm font-semibold hover:bg-surface-container-low transition-colors">Close</button>
                    <button type="submit" id="pdf-import-submit"
                        class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center gap-2">
                        <i class="fa-solid fa-file-arrow-up text-xs"></i> Start Import
                    </button>
                </div>
            </form>

            <div id="pdf-import-progress" class="hidden flex-col gap-3 rounded-xl border border-outline-variant/30 dark:border-slate-700 p-4">
                <div class="flex items-center justify-between text-sm font-semibold text-on-surface dark:text-slate-200">
                    <span>Overall</span><span id="pdf-import-overall">0%</span>
                </div>
                <div class="h-2 w-full bg-surface-container-high dark:bg-slate-700 rounded-full overflow-hidden">
                    <div id="pdf-import-overall-bar" class="h-full bg-primary rounded-full transition-all duration-300" style="width:0%"></div>
                </div>
                <div id="pdf-import-list" class="flex flex-col gap-3 border-t border-outline-variant/20 pt-3"></div>
            </div>
        </div>
    </div>
</div>

<template id="pdf-import-row">
    <div class="flex flex-col gap-1">
        <div class="flex items-center justify-between gap-3">
            <span class="name text-xs font-semibold text-on-surface dark:text-slate-200 truncate"></span>
            <span class="status text-xs font-semibold shrink-0 text-primary">Waiting</span>
        </div>
        <div class="h-1.5 w-full bg-surface-container-high dark:bg-slate-700 rounded-full overflow-hidden">
            <div class="bar h-full bg-primary rounded-full transition-all duration-300" style="width:0%"></div>
        </div>
    </div>
</template>

@push('scripts')
    @vite('resources/js/past-paper-import.js')
@endpush
