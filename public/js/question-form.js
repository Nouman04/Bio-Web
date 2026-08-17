/**
 * Shared behaviour for the question forms — the "add questions" page and the
 * edit modal in the bank.
 *
 * Field names are assigned as soon as inputs appear or change, never at submit
 * time: App's submit listener builds its FormData first, so anything named
 * later would be left out of the request.
 *
 * Markup contract:
 *   <select class="question-type">     options carry data-type="mcqs|theory"
 *   .answer-theory                     shown for theory questions
 *   .answer-mcq  > .option-list        shown for MCQs
 *   <template id="option-row-template">
 *   <template id="question-row-template">  (add page only)
 *   #question-rows                         (add page only)
 */
window.QuestionForm = (function () {
    'use strict';

    /* ── Answer mode ────────────────────────────────────────────────────── */

    /** `scope` is one add-page row, or the edit form. */
    function applyAnswerMode(scope) {
        if (!scope) return;

        const select = scope.querySelector('.question-type');
        const isMcq = select?.selectedOptions[0]?.dataset.type === 'mcqs';

        const theory = scope.querySelector('.answer-theory');
        const mcq = scope.querySelector('.answer-mcq');

        theory?.classList.toggle('hidden', isMcq);
        theory?.classList.toggle('flex', !isMcq);
        mcq?.classList.toggle('hidden', !isMcq);
        mcq?.classList.toggle('flex', isMcq);

        // An MCQ always starts with two empty options to fill.
        const list = mcq?.querySelector('.option-list');
        if (isMcq && list) {
            while (list.children.length < 2) addOptionRow(list);
        }

        refreshFieldNames();
    }

    function addOptionRow(list, { title = '', correct = false } = {}) {
        const template = document.getElementById('option-row-template');
        if (!template || !list) return;

        list.appendChild(template.content.cloneNode(true));

        const row = list.lastElementChild;
        row.querySelector('[data-role="option"]').value = title;
        row.querySelector('[data-role="correct"]').checked = correct;
        groupOptionRadios(list);
        refreshFieldNames();
    }

    /**
     * Radios only behave as a group when they share a name, and each row or
     * form needs its own group.
     */
    function groupOptionRadios(list) {
        const group = list.dataset.group ??= `options-${Math.random().toString(36).slice(2)}`;
        list.querySelectorAll('[data-role="correct"]').forEach(radio => { radio.name = group; });
    }

    /* ── Field naming ───────────────────────────────────────────────────── */

    function refreshFieldNames() {
        nameQuestionRowFields();

        document.querySelectorAll('form[data-question-form]').forEach(form => {
            if (!form.querySelector('#question-rows')) nameOptionFields(form);
        });
    }

    /** Rows on the add page post as questions[i][field]. */
    function nameQuestionRowFields() {
        document.querySelectorAll('#question-rows .question-row').forEach((row, index) => {
            row.querySelectorAll('[data-name]').forEach(field => {
                field.name = `questions[${index}][${field.dataset.name}]`;
            });

            nameOptionFields(row, `questions[${index}]`);
        });
    }

    /**
     * Options post as options[i], with correct_option holding the index of the
     * ticked one, so the two line up server-side.
     */
    function nameOptionFields(scope, prefix = '') {
        const list = scope.querySelector('.option-list');
        if (!list) return;

        // A hidden field carries the chosen index; radios are UI only.
        let correct = scope.querySelector('[data-role="correct-index"]');
        if (!correct) {
            correct = document.createElement('input');
            correct.type = 'hidden';
            correct.dataset.role = 'correct-index';
            list.parentElement.appendChild(correct);
        }
        correct.name = prefix ? `${prefix}[correct_option]` : 'correct_option';
        correct.value = '';

        list.querySelectorAll('.option-row').forEach((row, index) => {
            row.querySelector('[data-role="option"]').name = prefix
                ? `${prefix}[options][${index}]`
                : `options[${index}]`;

            if (row.querySelector('[data-role="correct"]').checked) {
                correct.value = index;
            }
        });
    }

    /* ── Repeatable question rows (add page) ────────────────────────────── */

    function addQuestionRow() {
        const rows = document.getElementById('question-rows');
        const template = document.getElementById('question-row-template');
        if (!rows || !template) return;

        rows.appendChild(template.content.cloneNode(true));
        renumberQuestionRows();
        applyAnswerMode(rows.lastElementChild);
        rows.lastElementChild?.querySelector('[data-name="question"]')?.focus();
    }

    function renumberQuestionRows() {
        const rows = document.querySelectorAll('#question-rows .question-row');

        rows.forEach((row, index) => {
            row.querySelector('.question-row-number').textContent = index + 1;
            // A single row cannot be removed — there would be nothing to save.
            row.querySelector('.question-row-remove').classList.toggle('hidden', rows.length === 1);
        });

        // Row indexes moved, so the field names have to follow.
        nameQuestionRowFields();
    }

    /** Back to a single empty row, e.g. after a successful save. */
    function resetRows() {
        const rows = document.getElementById('question-rows');
        if (!rows) return;

        rows.replaceChildren();
        addQuestionRow();
    }

    /* ── Wiring ─────────────────────────────────────────────────────────── */

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('add-question-row')?.addEventListener('click', () => addQuestionRow());

        document.querySelectorAll('form[data-question-form]').forEach(form => {
            if (form.querySelector('#question-rows')) {
                addQuestionRow();
            } else {
                applyAnswerMode(form);
            }
        });
    });

    // Delegated: rows and option rows appear after load.
    document.addEventListener('click', (e) => {
        const removeRow = e.target.closest('.question-row-remove');
        if (removeRow) {
            removeRow.closest('.question-row').remove();
            renumberQuestionRows();
            return;
        }

        const addOption = e.target.closest('.option-add');
        if (addOption) {
            addOptionRow(addOption.closest('.answer-mcq').querySelector('.option-list'));
            return;
        }

        const removeOption = e.target.closest('.option-remove');
        if (removeOption) {
            const list = removeOption.closest('.option-list');
            removeOption.closest('.option-row').remove();
            // Never leave an MCQ with fewer than two boxes to fill.
            while (list.children.length < 2) addOptionRow(list);
            refreshFieldNames();
        }
    });

    document.addEventListener('change', (e) => {
        if (e.target.matches('.question-type')) {
            applyAnswerMode(e.target.closest('.question-row') ?? e.target.closest('form'));
        }

        // Ticking a different option changes which index is submitted.
        if (e.target.matches('[data-role="correct"]')) refreshFieldNames();
    });

    return {
        applyAnswerMode,
        addOptionRow,
        addQuestionRow,
        resetRows,
        refreshFieldNames,
    };
})();
