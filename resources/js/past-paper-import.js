// Chapters page: past-paper PDFs -> questions in the question bank.
// Each PDF is read in the browser (same extractor as /pdf-extractor), then its
// questions are posted to the server, one PDF at a time.
import * as pdfjs from 'pdfjs-dist';
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import { extractQuestions, readCover } from './pdf-question-extractor';

pdfjs.GlobalWorkerOptions.workerSrc = new URL(workerUrl.split('/').pop(), import.meta.url).href;

const $ = id => document.getElementById(id);
const createCanvas = (w, h) => Object.assign(document.createElement('canvas'), { width: w, height: h });

const form = $('pdf-import-form');
const input = $('pdf-import-files');

input?.addEventListener('change', () => {
    $('pdf-import-names').textContent = input.files.length
        ? `${input.files.length} PDF${input.files.length > 1 ? 's' : ''} selected`
        : 'One or more question papers';
});

form?.addEventListener('submit', async e => {
    e.preventDefault();
    const files = [...input.files];
    const error = $('pdf-import-error');
    error.classList.add('hidden');
    if (!form.chapter_id.value || !files.length) {
        error.textContent = !files.length ? 'Choose at least one PDF.' : 'Choose a chapter.';
        error.classList.remove('hidden');
        return;
    }

    const submit = $('pdf-import-submit');
    App.setButtonLoading(submit, true);
    const list = $('pdf-import-list');
    list.replaceChildren();
    $('pdf-import-progress').classList.replace('hidden', 'flex');

    const rows = files.map(file => {
        const row = $('pdf-import-row').content.firstElementChild.cloneNode(true);
        row.querySelector('.name').textContent = file.name;
        list.append(row);
        return row;
    });
    const percents = files.map(() => 0);
    const paint = (i, percent, status, tone = 'text-primary') => {
        percents[i] = percent;
        rows[i].querySelector('.bar').style.width = percent + '%';
        const badge = rows[i].querySelector('.status');
        badge.textContent = status;
        badge.className = `status text-xs font-semibold shrink-0 ${tone}`;
        const overall = Math.round(percents.reduce((a, b) => a + b, 0) / files.length);
        $('pdf-import-overall-bar').style.width = overall + '%';
        $('pdf-import-overall').textContent = `${overall}%`;
    };

    let saved = 0;
    for (const [i, file] of files.entries()) {
        try {
            paint(i, 0, 'Reading…');
            const pdf = await pdfjs.getDocument({ data: await file.arrayBuffer() }).promise;
            const cover = await readCover(pdf);
            const questions = await extractQuestions(pdf, {
                createCanvas,
                // Reading pages is most of the work; saving is the last 10%.
                onProgress: (n, total) => paint(i, Math.round(n / total * 90), `Page ${n}/${total}`),
            });
            if (!questions.length) throw new Error('No numbered questions found');

            paint(i, 90, 'Saving…');
            const body = await App.request(form.action, {
                method: 'POST',
                body: { chapter_id: form.chapter_id.value, filename: file.name, cover, questions },
            });
            saved += body.imported;
            const date = cover.session ? ` · ${cover.session} ${cover.year}` : '';
            paint(i, 100, `${body.imported} saved${body.skipped ? `, ${body.skipped} already there` : ''}${date}`, 'text-tertiary');
        } catch (err) {
            paint(i, 100, err?.message || 'Failed', 'text-error');
            rows[i].querySelector('.bar').classList.replace('bg-primary', 'bg-error');
        }
    }

    App.setButtonLoading(submit, false);
    form.reset();
    $('pdf-import-names').textContent = 'One or more question papers';
    App.toast('success', `Saved ${saved} questions from ${files.length} PDF${files.length > 1 ? 's' : ''}.`);
    window.LaravelDataTables?.['chapters-table']?.ajax.reload(null, false);
});
