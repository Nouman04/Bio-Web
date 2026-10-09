// Turns an exam-paper PDF (pdf.js document) into one Quill-ready HTML string
// per top-level question. Runs wherever pdf.js runs: pass `createCanvas`.
//
// How it reads a page:
//  - text comes from pdf.js text items (exact positions, no OCR);
//  - graphics come from the rendered page: dark pixels that aren't text.
//    The faint watermark is lighter than DARK, so it drops out.
//  - a graphic block with a thin ruled grid becomes a <table>, anything else
//    (vector diagram, photo, graph paper) is cropped into an <img>, with the
//    labels that sit on it.
//
// Tuned for Cambridge-style papers (header/footer bands, "1 (a) (i)" numbering,
// "[2]" marks, dotted answer lines).

const S = 2;              // render scale: px per PDF point
const DARK = 150;         // luminance below this is ink
const HEADER = 0.065;     // top/bottom page fractions that hold headers, barcodes, footers
const FOOTER = 0.074;
const SIDE = 40;          // pt; the right side holds the rotated "DO NOT WRITE" margin
const BAND_GAP = 10;      // pt; graphics closer than this belong to the same block
const NOISE = /^(\[?Turn over|BLANK PAGE|DO NOT WRITE|PUBLISHED$)/i;

export async function extractQuestions(pdf, { createCanvas, onProgress } = {}) {
    const blocks = [];
    for (let n = 1; n <= pdf.numPages; n++) {
        const page = await pdf.getPage(n);
        blocks.push(...await pageBlocks(page, createCanvas));
        page.cleanup();
        onProgress?.(n, pdf.numPages);
    }
    return toQuestions(blocks);
}

async function pageBlocks(page, createCanvas) {
    const vp = page.getViewport({ scale: S });
    const W = vp.width / S, H = vp.height / S;
    const top = H * HEADER, bottom = H * (1 - FOOTER), left = SIDE, right = W - SIDE;

    const canvas = createCanvas(Math.ceil(vp.width), Math.ceil(vp.height));
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    await page.render({ canvasContext: ctx, viewport: vp }).promise;

    // Text items in top-left page points.
    const items = [], noise = [];
    for (const it of (await page.getTextContent()).items) {
        const [, b, , , e, f] = it.transform;
        if (!it.str.trim() || Math.abs(b) > 0.01 || it.height < 7) continue;
        const base = H - f, h = it.height;
        // A question's last "[Total: 5]" can sit as low as the footer line; marks
        // never look like footer text, so they are kept wherever they are.
        const isMarks = /^\[(Total:\s*)?\d+\]$/.test(it.str.trim());
        if (base < top || (base > bottom && !isMarks) || e < left || e > right) continue;
        const item = { str: it.str, x: e, x1: e + it.width, top: base - h, bottom: base + h * 0.25, base, h };
        (NOISE.test(it.str.trim()) ? noise : items).push(item);
    }

    const ink = inkMask(ctx, canvas, [...items, ...noise], { top, bottom, left, right });
    const blocks = [];
    const used = new Set();

    for (const band of findBands(ink)) {
        const inside = blocks.find(b => b.box && band.y0 / S >= b.box.y0 && band.y1 / S <= b.box.y1);
        if (inside) continue; // already cropped with an earlier figure
        const grid = band.h > 4 * S && tableGrid(ink, band);
        if (grid) {
            blocks.push({ type: 'table', top: band.y0 / S, rows: fillTable(grid, items, used) });
        } else if (band.h > 6 * S) {
            const fig = cropFigure(canvas, band, items, used, W, createCanvas);
            blocks.push({ type: 'image', top: fig.box.y0, ...fig });
        }
    }

    for (const line of toLines(items.filter(it => !used.has(it)))) blocks.push({ type: 'line', ...line });
    return blocks.sort((a, b) => a.top - b.top);
}

// 1 where the page has ink that isn't text, inside the content area.
function inkMask(ctx, canvas, items, area) {
    const w = canvas.width, h = canvas.height;
    const px = ctx.getImageData(0, 0, w, h).data;
    const mask = new Uint8Array(w * h);
    const x0 = Math.floor(area.left * S), x1 = Math.ceil(area.right * S);
    const y0 = Math.floor(area.top * S), y1 = Math.ceil(area.bottom * S);
    for (let y = y0; y < y1; y++) {
        for (let x = x0; x < x1; x++) {
            const i = (y * w + x) * 4;
            if (px[i] * 0.3 + px[i + 1] * 0.59 + px[i + 2] * 0.11 < DARK) mask[y * w + x] = 1;
        }
    }
    for (const it of items) {
        const ty1 = Math.min(h, Math.ceil(it.bottom * S) + 2), tx1 = Math.min(w, Math.ceil(it.x1 * S) + 2);
        for (let y = Math.max(0, Math.floor(it.top * S) - 2); y < ty1; y++) {
            mask.fill(0, y * w + Math.max(0, Math.floor(it.x * S) - 2), y * w + tx1);
        }
    }
    return { mask, w, h };
}

// Vertical runs of rows that contain ink, merged across small gaps.
function findBands({ mask, w, h }) {
    const bands = [];
    let cur = null;
    for (let y = 0; y < h; y++) {
        let n = 0, minX = w, maxX = -1;
        for (let x = 0, r = y * w; x < w; x++) {
            if (mask[r + x]) { n++; if (x < minX) minX = x; maxX = x; }
        }
        if (n < 2) continue;
        if (cur && y - cur.y1 <= BAND_GAP * S) {
            cur.y1 = y; cur.x0 = Math.min(cur.x0, minX); cur.x1 = Math.max(cur.x1, maxX);
        } else {
            cur = { y0: y, y1: y, x0: minX, x1: maxX };
            bands.push(cur);
        }
    }
    return bands
        .map(b => ({ ...b, h: b.y1 - b.y0 + 1, w: b.x1 - b.x0 + 1 }))
        .filter(b => b.w > 10 * S);
}

// Grid lines of a ruled table, or null. A table is mostly empty space crossed
// by at least 2 thin full-width rules and 2 thin full-height rules.
function tableGrid({ mask, w }, band) {
    let ink = 0;
    const rowFill = [], colFill = new Array(band.w).fill(0);
    for (let y = band.y0; y <= band.y1; y++) {
        let n = 0;
        for (let x = band.x0; x <= band.x1; x++) {
            if (mask[y * w + x]) { n++; colFill[x - band.x0]++; }
        }
        rowFill.push(n);
        ink += n;
    }
    if (ink / (band.w * band.h) > 0.1) return null;

    const hLines = rules(rowFill, band.w * 0.5, band.y0);
    const vLines = rules(colFill, band.h * 0.5, band.x0);
    if (!hLines || !vLines || hLines.length < 2 || vLines.length < 2) return null;
    return { ys: hLines.map(v => v / S), xs: vLines.map(v => v / S) };
}

// Centres of runs where fill > min; null if any run is too thick to be a rule.
function rules(fill, min, offset) {
    const out = [];
    for (let i = 0; i < fill.length; i++) {
        if (fill[i] <= min) continue;
        let j = i;
        while (j + 1 < fill.length && fill[j + 1] > min) j++;
        if (j - i > 3 * S) return null;
        out.push(offset + (i + j) / 2);
        i = j;
    }
    return out;
}

function fillTable({ xs, ys }, items, used) {
    const rows = ys.slice(1).map(() => xs.slice(1).map(() => []));
    for (const it of items) {
        const cx = (it.x + it.x1) / 2, cy = (it.top + it.bottom) / 2;
        const r = ys.findIndex((y, i) => i > 0 && cy > ys[i - 1] && cy < y) - 1;
        const c = xs.findIndex((x, i) => i > 0 && cx > xs[i - 1] && cx < x) - 1;
        if (r < 0 || c < 0) continue;
        rows[r][c].push(it);
        used.add(it);
    }
    return rows.map(row => row.map(cell => toLines(cell).map(l => l.text).join(' ')));
}

// Crop a figure together with the labels printed on and around it.
function cropFigure(canvas, band, items, used, pageW, createCanvas) {
    const box = { x0: band.x0 / S, x1: band.x1 / S, y0: band.y0 / S, y1: band.y1 / S };
    const PAD = 4;
    // Papers introduce a figure ("Figure 3.1 shows ...") and caption it below;
    // keys, labels and axis titles between the two belong to the figure.
    const caption = items.find(it => CAPTION.test(it.str.trim()) && it.top > box.y1 && it.top - box.y1 < 60);
    let from = box.y0 - PAD, to = box.y1 + PAD;
    if (caption) {
        to = caption.top;
        const intro = items.filter(it => it !== caption && it.bottom <= box.y0 && it.str.includes(caption.str.trim()))
            .sort((a, b) => b.base - a.base)[0];
        if (intro) {
            let last = intro; // skip the rest of the intro paragraph
            for (const it of items.filter(i => i.base > intro.base).sort((a, b) => a.base - b.base)) {
                if (Math.abs(it.x - intro.x) > 3 || it.base - last.base > it.h * 1.6) break;
                last = it;
            }
            from = last.bottom;
        }
    }
    for (const it of items) {
        const mid = (it.top + it.bottom) / 2;
        if (used.has(it) || it === caption || mid < from || mid > to) continue;
        used.add(it);
        box.x0 = Math.min(box.x0, it.x); box.x1 = Math.max(box.x1, it.x1);
        box.y0 = Math.min(box.y0, it.top); box.y1 = Math.max(box.y1, it.bottom);
    }
    const x0 = Math.max(0, box.x0 - PAD), x1 = Math.min(pageW, box.x1 + PAD);
    const y0 = Math.max(0, box.y0 - PAD), y1 = box.y1 + PAD;
    const out = createCanvas(Math.ceil((x1 - x0) * S), Math.ceil((y1 - y0) * S));
    out.getContext('2d').drawImage(canvas, x0 * S, y0 * S, (x1 - x0) * S, (y1 - y0) * S, 0, 0, out.width, out.height);
    return { src: out.toDataURL('image/png'), width: Math.round((x1 - x0) * 1.33), box: { y0, y1: Math.max(y1, to) } };
}

// Items sharing a baseline, left to right. Smaller raised/lowered items become
// super/subscripts, marked with private-use characters that esc() turns into tags.
function toLines(items) {
    const lines = [];
    for (const it of [...items].sort((a, b) => b.h - a.h || a.base - b.base)) {
        const line = lines.find(l => Math.abs(l.base - it.base) < Math.max(3, l.h * 0.5));
        line ? line.items.push(it) : lines.push({ base: it.base, top: it.top, h: it.h, items: [it] });
    }
    for (const l of lines) {
        l.items.sort((a, b) => a.x - b.x);
        l.x = l.items[0].x;
        l.text = l.items.reduce((s, it, i) => {
            let str = it.str;
            if (it.h < l.h * 0.85 && it.base < l.base - 1) str = SUP[0] + str.trim() + SUP[1];
            else if (it.h < l.h * 0.85 && it.base > l.base + 1) str = SUB[0] + str.trim() + SUB[1];
            return s + (i && it.x - l.items[i - 1].x1 > 1 ? ' ' : '') + str;
        }, '').trim();
    }
    return lines.sort((a, b) => a.top - b.top);
}

// ---- Page blocks -> questions -------------------------------------------------

const PART = /^\(([a-hj-uwyz])\)\s*/; // i, v, x are read as roman sub-parts
const CAPTION = /^(Fig(ure)?|Table)\s+\d+(\.\d+)?$/i;
const SUBPART = /^\(([ivx]+)\)\s*/;
const MARKS = /\s*(\[(?:Total:\s*)?\d+\])$/;

// Each question comes back as a tree of parts, in paper order:
//   nodes: [{ ref: '1', parent: null }, { ref: '1(a)', parent: '1' }, { ref: '1(a)(i)', parent: '1(a)' }, ...]
// Every node holds only its own markup, so joining them in order gives the
// whole question again (`html`). Leaves carry their "[2]" as `marks`; the
// question's "[Total: 13]" becomes its `marks` and is not printed.
function toQuestions(blocks) {
    const questions = [];
    let q = null, node = null, level = 0, prev = null;

    const push = (html, merge = null) => { node.html.push(html); prev = merge; };
    const open = (ref, parent) => {
        node = { ref, parent: parent?.ref ?? null, html: [], marks: null };
        q.nodes.push(node);
        return node;
    };

    for (const b of blocks) {
        const first = b.type === 'line' && b.items[0];
        if (first && /^\d{1,2}$/.test(first.str.trim()) && first.x < 60) {
            q = { number: +first.str.trim(), nodes: [], part: null, marks: null };
            questions.push(q);
            q.root = open(String(q.number), null);
            level = 0;
            b.text = b.text.slice(first.str.trim().length).trim();
            b.qNumber = q.number;
        }
        if (!q) continue; // cover page and anything before question 1

        if (b.type === 'image') { push(`<p class="ql-align-center"><img src="${b.src}" width="${b.width}"></p>`); continue; }
        if (b.type === 'table') { push(tableHtml(b.rows)); continue; }

        let text = b.text.replace(/\bTick \(3\)/g, 'Tick (✓)'); // ponytail: tick glyph maps to "3" in this font; map the symbol font if other glyphs break
        if (CAPTION.test(text)) { push(`<p class="ql-align-center"><strong>${esc(text)}</strong></p>`); continue; }

        // A line can open several levels at once ("1 (a) (i) State ..."): each
        // opens its node, and the line's text goes to the deepest one.
        // ponytail: text between a part's [marks] and the next marker (rare
        // shared lead-ins) stays on the part before it.
        const markers = b.qNumber ? [String(b.qNumber)] : [];
        for (const [re, lvl] of [[PART, 1], [SUBPART, 2]]) {
            const m = text.match(re);
            if (!m) continue;
            markers.push(`(${m[1]})`);
            level = lvl;
            text = text.slice(m[0].length);
            if (lvl === 1) q.part = open(`${q.number}(${m[1]})`, q.root);
            else { const parent = q.part ?? q.root; open(`${parent.ref}(${m[1]})`, parent); }
        }
        if (b.qNumber && markers.length === 1) level = 0;
        // Where the words start, so wrapped lines of the same paragraph line up with it.
        const x = (b.items.find(it => !/^(\d{1,2}|\([a-z]+\))$/.test(it.str.trim())) ?? b.items[0]).x;

        const marks = text.match(MARKS);
        if (marks) text = text.slice(0, marks.index);
        const isAnswer = /\.{10,}/.test(text);

        if (text) {
            const canMerge = !markers.length && !isAnswer && prev && !prev.answer
                && Math.abs(prev.x - x) < 3 && b.base - prev.base < b.h * 1.6;
            if (canMerge) {
                node.html[node.html.length - 1] = node.html.at(-1).replace(/<\/p>$/, ` ${esc(text)}</p>`);
                prev = { ...prev, base: b.base };
            } else {
                const head = markers.map(m => `<strong>${m}</strong> `).join('');
                push(`<p${indent(level)}>${head}${esc(text)}</p>`, { x, base: b.base, answer: isAnswer });
            }
        } else if (markers.length) {
            push(`<p${indent(level)}>${markers.map(m => `<strong>${m}</strong>`).join(' ')}</p>`);
        }
        if (marks?.[1].startsWith('[Total')) {
            q.marks = +marks[1].match(/\d+/)[0];
            q = null; // the question is over; drop copyright pages etc.
        } else if (marks) {
            push(`<p class="ql-align-right"><strong>${marks[1]}</strong></p>`);
            node.marks = (node.marks ?? 0) + +marks[1].match(/\d+/)[0];
        }
    }
    return questions.map(q => {
        const nodes = q.nodes.map(n => ({ ref: n.ref, parent: n.parent, html: n.html.join(''), marks: n.marks }));
        return { number: q.number, html: nodes.map(n => n.html).join(''), marks: q.marks, nodes };
    });
}

// Paper code and session from the cover page: "0610/42", "February/March 2026".
export async function readCover(pdf) {
    const page = await pdf.getPage(1);
    const text = (await page.getTextContent()).items.map(i => i.str).join(' ');
    // Items may split "0610/41" into "0610", "/", "41", so allow spaces around the slash.
    const code = text.match(/\b(\d{4})\s*\/\s*(\d{2})\b/);
    const found = text.match(/\b(February|May|October)\s*\/\s*(March|June|November)\s+(\d{4})\b/i);
    const session = found && [found[0], `${found[1]}/${found[2]}`, found[3]];
    return {
        syllabus: code?.[1] ?? null,
        paperNo: code?.[2] ?? null,
        session: session?.[1] ?? null,
        year: session ? +session[2] : null,
    };
}

// Mark scheme PDF -> { "1(a)(i)": html }, one entry per part, keyed like the
// question paper's part refs. Cambridge mark schemes are one table,
// "Question | Answer | Marks | Guidance", a row per part.
// The narrow Question and Marks headers give the column edges; pages before
// the first header (generic marking principles) are skipped.
// ponytail: text only; figures/tables inside an answer cell come through as plain lines.
export async function extractAnswers(pdf, { onProgress } = {}) {
    const rows = [];
    let cols = null, row = null;
    for (let n = 1; n <= pdf.numPages; n++) {
        const page = await pdf.getPage(n);
        const H = page.getViewport({ scale: 1 }).height;
        const items = [];
        for (const it of (await page.getTextContent()).items) {
            const [, b, , , e, f] = it.transform;
            const base = H - f, h = it.height;
            if (!it.str.trim() || NOISE.test(it.str.trim()) || Math.abs(b) > 0.01 || h < 4 || base < H * HEADER || base > H * (1 - FOOTER)) continue;
            items.push({ str: it.str, x: e, x1: e + it.width, top: base - h, bottom: base + h * 0.25, base, h });
        }
        page.cleanup();
        onProgress?.(n, pdf.numPages);

        for (const l of toLines(items)) {
            // The header repeats at the top of each page and of each question's table.
            const at = w => l.items.find(i => i.str.trim() === w);
            if (at('Question') && at('Answer') && at('Marks')) {
                cols = { question: at('Question').x1 + 8, answer: at('Marks').x - 8, marks: at('Marks').x1 + 8 };
                continue;
            }
            if (!cols) continue;
            const cell = (from, to) => toLines(l.items.filter(i => i.x >= from && i.x < to)).map(c => c.text).join(' ');
            const ref = cell(-Infinity, cols.question).replace(/\s+/g, '');
            const m = ref.match(/^(\d{1,2})(\(.*)?$/);
            if (m) rows.push(row = { number: +m[1], ref, answer: [], marks: [], guidance: [] });
            if (!row) continue;
            for (const [key, from, to] of [['answer', cols.question, cols.answer], ['marks', cols.answer, cols.marks], ['guidance', cols.marks, Infinity]]) {
                const text = cell(from, to);
                if (text) row[key].push(esc(text));
            }
        }
    }

    const out = {};
    for (const r of rows) {
        const guidance = r.guidance.length ? `<td><em>${r.guidance.join('<br>')}</em></td>` : '<td></td>';
        out[r.ref] = (out[r.ref] ?? '') + `<tr><td><strong>${r.ref}</strong></td><td>${r.answer.join('<br>')}</td><td>${r.marks.join(' ')}</td>${guidance}</tr>`;
    }
    for (const k in out) out[k] = `<table><tbody>${out[k]}</tbody></table>`;
    return out;
}

const indent = level => (level ? ` class="ql-indent-${level}"` : '');
const SUP = ['', ''], SUB = ['', ''];
const esc = s => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replaceAll(SUP[0], '<sup>').replaceAll(SUP[1], '</sup>').replaceAll(SUB[0], '<sub>').replaceAll(SUB[1], '</sub>');

function tableHtml(rows) {
    return '<table><tbody>' + rows.map(r => '<tr>' + r.map(c => `<td>${esc(c)}</td>`).join('') + '</tr>').join('') + '</tbody></table>';
}
