// Chapters page: past-paper PDFs -> questions in the question bank.
// Each row is one paper: the file in its Question paper slot is the question
// paper, the file in its Mark scheme slot supplies the answers. Both are read
// in the browser (same extractor as /pdf-extractor), then the questions, each
// with its answer, are posted to the server, one paper at a time.
import * as pdfjs from 'pdfjs-dist';
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import { extractAnswers, extractQuestions, readCover } from './pdf-question-extractor';

pdfjs.GlobalWorkerOptions.workerSrc = new URL(workerUrl.split('/').pop(), import.meta.url).href;

const $ = id => document.getElementById(id);
const createCanvas = (w, h) => Object.assign(document.createElement('canvas'), { width: w, height: h });
const load = async file => pdfjs.getDocument({ data: await file.arrayBuffer() }).promise;

const form = $('pdf-import-form');
const list = $('pdf-import-pairs');

const addPair = () => {
    list.append($('pdf-import-pair').content.firstElementChild.cloneNode(true));
    renumber();
};
const renumber = () => list.querySelectorAll('.pair-title').forEach((el, i) => { el.textContent = `Paper ${i + 1}`; });

// The chosen file's name on its slot.
const showFile = slot => {
    const file = slot.querySelector('input').files[0];
    slot.querySelector('.slot-name').textContent = file?.name ?? slot.dataset.empty;
    slot.classList.toggle('border-primary', !!file);
    slot.classList.toggle('bg-primary/5', !!file);
    slot.querySelector('.slot-icon').className = `slot-icon fa-solid ${file ? 'fa-circle-check text-primary' : slot.dataset.icon}`;
};

if (form) {
    addPair();
    $('pdf-import-add').addEventListener('click', addPair);
    list.addEventListener('change', e => e.target.matches('input[type=file]') && showFile(e.target.closest('.slot')));
    list.addEventListener('click', e => {
        if (!e.target.closest('.pair-remove')) return;
        e.target.closest('.pair').remove();
        if (!list.children.length) addPair();
        renumber();
    });
}

form?.addEventListener('submit', async e => {
    e.preventDefault();
    const error = $('pdf-import-error');
    const say = msg => { error.textContent = msg; error.classList.toggle('hidden', !msg); };
    say('');

    const pairs = [...list.querySelectorAll('.pair:not([data-done])')].map(card => ({
        card,
        qp: card.querySelector('input[name=qp]').files[0],
        ms: card.querySelector('input[name=ms]').files[0],
    }));
    if (!pairs.some(p => p.qp)) return say('Choose at least one question paper.');
    if (pairs.some(p => p.ms && !p.qp)) return say('Every mark scheme needs its question paper in the same row.');
    const todo = pairs.filter(p => p.qp);

    const submit = $('pdf-import-submit');
    App.setButtonLoading(submit, true);
    $('pdf-import-progress').classList.replace('hidden', 'flex');

    const percents = todo.map(() => 0);
    const paint = (i, percent, status, tone = 'text-primary') => {
        percents[i] = percent;
        const card = todo[i].card;
        card.querySelector('.pair-progress').classList.replace('hidden', 'flex');
        const bar = card.querySelector('.bar');
        bar.style.width = percent + '%';
        bar.classList.toggle('bg-error', tone === 'text-error');
        bar.classList.toggle('bg-primary', tone !== 'text-error');
        const badge = card.querySelector('.status');
        badge.textContent = status;
        badge.className = `status text-xs font-semibold ${tone}`;
        const overall = Math.round(percents.reduce((a, b) => a + b, 0) / todo.length);
        $('pdf-import-overall-bar').style.width = overall + '%';
        $('pdf-import-overall').textContent = `${overall}%`;
    };

    let saved = 0, answered = 0;
    for (const [i, { card, qp, ms }] of todo.entries()) {
        try {
            paint(i, 0, 'Reading…');
            const pdf = await load(qp);
            const cover = await readCover(pdf); // paper number and session for the citation
            const questions = await extractQuestions(pdf, {
                createCanvas,
                // Reading pages is most of the work; saving is the last 10%.
                onProgress: (n, total) => paint(i, Math.round(n / total * (ms ? 60 : 90)), `Reading question paper · page ${n}/${total}`),
            });
            if (!questions.length) {
                // Say why, when the reason is the usual one: a mark scheme in the question slot.
                const firstPage = (await (await pdf.getPage(1)).getTextContent()).items.map(i => i.str).join(' ');
                throw new Error(/MARK SCHEME/i.test(firstPage)
                    ? `"${qp.name}" is a mark scheme, so it has no questions. Put the question paper here, and this file in the Mark scheme slot.`
                    : `No numbered questions found in "${qp.name}".`);
            }

            const answers = ms ? await extractAnswers(await load(ms), {
                onProgress: (n, total) => paint(i, 60 + Math.round(n / total * 30), `Reading mark scheme · page ${n}/${total}`),
            }) : {};

            paint(i, 90, 'Saving…');
            const body = await App.request(form.action, {
                method: 'POST',
                body: {
                    filename: qp.name,
                    cover,
                    // The parts only: the joined `html` would send every figure twice.
                    questions: questions.map(q => ({
                        number: q.number,
                        marks: q.marks,
                        nodes: q.nodes.map(n => ({ ...n, answer: answers[n.ref] ?? null })),
                    })),
                },
            });
            saved += body.imported;
            answered += body.answered;
            const parts = [`${body.imported} questions saved`];
            if (ms) parts.push(`${body.answered} answers`);
            if (body.skipped) parts.push(`${body.skipped} already there`);
            paint(i, 100, parts.join(' · '), 'text-tertiary');
            card.dataset.done = '';
            card.querySelectorAll('input, .pair-remove').forEach(el => { el.disabled = true; });
        } catch (err) {
            paint(i, 100, err?.message || 'Failed', 'text-error');
        }
    }

    App.setButtonLoading(submit, false);
    App.toast(saved ? 'success' : 'error', `Saved ${saved} questions and ${answered} answers.`);
    window.LaravelDataTables?.['chapters-table']?.ajax.reload(null, false);
});
