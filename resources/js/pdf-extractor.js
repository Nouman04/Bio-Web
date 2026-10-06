// Past-paper PDF -> one Quill editor per question. Everything runs in the
// browser (pdf.js), so the server needs no poppler and stores nothing.
import * as pdfjs from 'pdfjs-dist';
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';
import { extractQuestions } from './pdf-question-extractor';

// Vite's ?url is root-absolute (/build/...); resolve it next to this bundle so
// the app also works from a sub-folder like /EMS/public.
pdfjs.GlobalWorkerOptions.workerSrc = new URL(workerUrl.split('/').pop(), import.meta.url).href;

const TOOLBAR = [
    [{ header: [2, 3, false] }],
    ['bold', 'italic', 'underline'],
    [{ script: 'sub' }, { script: 'super' }],
    [{ align: [] }, { indent: '-1' }, { indent: '+1' }],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['image', 'clean'],
];

const $ = id => document.getElementById(id);
const createCanvas = (w, h) => Object.assign(document.createElement('canvas'), { width: w, height: h });

$('pdf').addEventListener('change', async e => {
    const file = e.target.files[0];
    if (!file) return;

    $('results').replaceChildren();
    $('progress').hidden = false;
    $('status').textContent = 'Reading PDF…';

    try {
        const pdf = await pdfjs.getDocument({ data: await file.arrayBuffer() }).promise;
        const questions = await extractQuestions(pdf, {
            createCanvas,
            onProgress: (n, total) => {
                $('progress').value = n / total;
                $('status').textContent = `Page ${n} of ${total}`;
            },
        });
        questions.forEach(renderQuestion);
        $('status').textContent = questions.length
            ? `${questions.length} questions extracted from ${pdf.numPages} pages.`
            : 'No numbered questions found in this PDF.';
    } catch (err) {
        console.error(err);
        $('status').textContent = `Could not read this PDF: ${err.message}`;
    } finally {
        $('progress').hidden = true;
        e.target.value = '';
    }
});

function renderQuestion(q) {
    const card = $('question-template').content.firstElementChild.cloneNode(true);
    card.querySelector('h2').textContent = `Question ${q.number}`;
    $('results').append(card);

    const quill = new Quill(card.querySelector('.editor'), { theme: 'snow', modules: { table: true, toolbar: TOOLBAR } });
    quill.setContents(quill.clipboard.convert({ html: q.html }), 'silent');

    card.querySelector('.copy').addEventListener('click', async ev => {
        await navigator.clipboard.writeText(quill.getSemanticHTML());
        ev.target.textContent = 'Copied';
        setTimeout(() => (ev.target.textContent = 'Copy HTML'), 1500);
    });
}
