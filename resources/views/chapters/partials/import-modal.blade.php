{{--
    Bulk import of a past-paper question worksheet.

    The upload only stages the file — a real worksheet runs to thousands of
    rows, so the work happens on the queue and this panel watches it.
--}}
<div id="import-worksheet-modal" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeImportWorksheetModal()"></div>

    <div class="relative w-full max-w-2xl mx-4 bg-surface-container-lowest dark:bg-slate-800 rounded-2xl shadow-2xl border border-outline-variant/30 dark:border-slate-700 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant/20 dark:border-slate-700">
            <div>
                <h3 class="text-base font-semibold text-on-surface dark:text-slate-100">Import Question Worksheet</h3>
                <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-0.5">
                    Into <span class="text-primary font-semibold">{{ $courseTitle }}</span>
                </p>
            </div>
            <button type="button" onclick="closeImportWorksheetModal()"
                class="w-8 h-8 flex items-center justify-center rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 flex flex-col gap-4 max-h-[70vh] overflow-y-auto">
            {{-- What the file must contain, so nobody has to guess. --}}
            <div class="rounded-xl border border-outline-variant/30 dark:border-slate-700 bg-surface-container-low/50 dark:bg-slate-900/40 p-4">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant dark:text-slate-400">Expected columns</span>
                    <a href="{{ route('courses.import.template', $courseId) }}"
                        class="text-xs font-semibold text-primary hover:underline inline-flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-download text-[10px]"></i> Template
                    </a>
                </div>
                <p class="text-xs text-on-surface-variant dark:text-slate-400 font-mono leading-relaxed">
                    Question &middot; Year &middot; Session &middot; Chapter &middot; Topic &middot; Paper No. &middot; Q No. &middot; Marks &middot; Source PDF
                </p>
                <ul class="text-[11px] text-on-surface-variant/80 dark:text-slate-500 mt-3 flex flex-col gap-1 list-disc pl-4">
                    <li>Chapters and topics are matched by title and created when they are new.</li>
                    <li>Every row needs a <span class="font-semibold">Chapter</span>; rows without one are reported, not guessed at.</li>
                    <li><span class="font-semibold">Session</span> may be March, May/June or Oct/Nov.</li>
                    <li>Rows already imported are skipped, so the same file can be run twice safely.</li>
                </ul>
            </div>

            <form id="import-worksheet-form" action="{{ route('courses.import.store', $courseId) }}"
                method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                @csrf
                <label for="worksheet-file"
                    class="border-2 border-dashed border-outline-variant/50 dark:border-slate-700 rounded-xl p-6 flex flex-col items-center justify-center text-center cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-colors group">
                    <div class="w-10 h-10 rounded-full bg-primary-container/20 text-primary flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-file-excel"></i>
                    </div>
                    <span class="text-sm font-semibold text-on-surface dark:text-slate-200">Choose a worksheet</span>
                    <span id="worksheet-file-name" class="text-xs text-on-surface-variant dark:text-slate-400 mt-1">.xlsx, .xls or .csv — up to 30MB</span>
                    <input id="worksheet-file" name="worksheet" type="file" accept=".xlsx,.xls,.csv" class="hidden">
                </label>

                <p id="worksheet-file-error" class="hidden text-xs text-error"></p>

                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" onclick="closeImportWorksheetModal()"
                        class="px-5 py-2 rounded-full border border-outline-variant text-on-surface-variant text-sm font-semibold hover:bg-surface-container-low transition-colors">Cancel</button>
                    <button type="submit" id="worksheet-submit"
                        class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors inline-flex items-center gap-2">
                        <i class="fa-solid fa-file-arrow-up text-xs"></i> Start Import
                    </button>
                </div>
            </form>

            {{-- Progress, once a run is under way. --}}
            <div id="worksheet-progress" class="hidden flex-col gap-3 rounded-xl border border-outline-variant/30 dark:border-slate-700 p-4">
                <div class="flex items-center justify-between gap-3">
                    <span id="worksheet-progress-name" class="text-sm font-semibold text-on-surface dark:text-slate-200 truncate"></span>
                    <span id="worksheet-progress-status" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-primary/10 text-primary shrink-0"></span>
                </div>
                <div class="h-2 w-full bg-surface-container-high dark:bg-slate-700 rounded-full overflow-hidden">
                    <div id="worksheet-progress-bar" class="h-full bg-primary rounded-full transition-all duration-500" style="width:0%"></div>
                </div>
                <div id="worksheet-progress-counts" class="text-xs text-on-surface-variant dark:text-slate-400"></div>
                <div id="worksheet-progress-failures" class="hidden text-[11px] text-on-surface-variant dark:text-slate-400 max-h-32 overflow-y-auto border-t border-outline-variant/20 pt-2"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const modal = document.getElementById('import-worksheet-modal');
    const form = document.getElementById('import-worksheet-form');
    const fileInput = document.getElementById('worksheet-file');
    const fileName = document.getElementById('worksheet-file-name');
    const fileError = document.getElementById('worksheet-file-error');
    const submit = document.getElementById('worksheet-submit');
    const progress = document.getElementById('worksheet-progress');

    let poller = null;

    window.openImportWorksheetModal = function () {
        modal.classList.remove('hidden');
        // A run started before a refresh is still going; pick it back up.
        resumeRunningImport();
    };

    window.closeImportWorksheetModal = function () {
        modal.classList.add('hidden');
        stopPolling();
    };

    fileInput?.addEventListener('change', () => {
        const file = fileInput.files[0];
        fileName.textContent = file ? file.name : '.xlsx, .xls or .csv — up to 30MB';
        fileError.classList.add('hidden');
    });

    function stopPolling() {
        if (poller) {
            clearInterval(poller);
            poller = null;
        }
    }

    function paint(state) {
        progress.classList.remove('hidden');
        progress.classList.add('flex');

        document.getElementById('worksheet-progress-name').textContent = state.filename;
        document.getElementById('worksheet-progress-bar').style.width = state.percent + '%';

        const badge = document.getElementById('worksheet-progress-status');
        badge.textContent = state.status;
        badge.className = 'text-xs font-semibold px-2 py-0.5 rounded-full shrink-0 ' + ({
            queued: 'bg-secondary/10 text-secondary',
            running: 'bg-primary/10 text-primary',
            completed: 'bg-tertiary/10 text-tertiary',
            failed: 'bg-error/10 text-error',
        }[state.status] || 'bg-primary/10 text-primary');

        const parts = [`${state.imported} imported`, `${state.skipped} skipped`];
        if (state.created_topics) parts.push(`${state.created_topics} new topics`);
        if (state.created_chapters) parts.push(`${state.created_chapters} new chapters`);
        if (state.error) parts.push(state.error);
        document.getElementById('worksheet-progress-counts').textContent = parts.join(' · ');

        const failures = document.getElementById('worksheet-progress-failures');
        if (state.failures?.length) {
            failures.classList.remove('hidden');
            failures.innerHTML = '';
            state.failures.slice(0, 50).forEach(f => {
                const line = document.createElement('div');
                line.textContent = `Row ${f.row}: ${f.reason}`;
                failures.appendChild(line);
            });
        } else {
            failures.classList.add('hidden');
        }

        if (state.finished) {
            stopPolling();
            if (state.status === 'completed') {
                App.toast('success', `Imported ${state.imported} questions.`);
                // The table behind the modal is now out of date.
                window.LaravelDataTables?.['chapters-table']?.ajax.reload(null, false);
            }
        }
    }

    function watch(uuid) {
        stopPolling();

        const url = `{{ route('courses.import.status', [$courseId, '__ID__']) }}`.replace('__ID__', uuid);
        const tick = () => App.request(url).then(paint).catch(stopPolling);

        tick();
        poller = setInterval(tick, 2000);
    }

    function resumeRunningImport() {
        App.request(`{{ route('courses.import.latest', $courseId) }}`)
            .then(list => {
                const live = (list || []).find(i => !i.finished);
                if (live) watch(live.uuid);
            })
            .catch(() => {});
    }

    form?.addEventListener('submit', (event) => {
        event.preventDefault();

        if (!fileInput.files.length) {
            fileError.textContent = 'Choose a worksheet first.';
            fileError.classList.remove('hidden');
            return;
        }

        App.setButtonLoading(submit, true);
        fileError.classList.add('hidden');

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            body: new FormData(form),
        })
            .then(async response => {
                const body = await response.json().catch(() => ({}));
                if (!response.ok) throw body;
                return body;
            })
            .then(body => {
                App.toast('success', body.message);
                form.reset();
                fileName.textContent = '.xlsx, .xls or .csv — up to 30MB';
                watch(body.import.uuid);
            })
            .catch(body => {
                const message = body?.errors?.worksheet?.[0] || body?.message || 'The worksheet could not be uploaded.';
                fileError.textContent = message;
                fileError.classList.remove('hidden');
            })
            .finally(() => App.setButtonLoading(submit, false));
    });
})();
</script>
@endpush
