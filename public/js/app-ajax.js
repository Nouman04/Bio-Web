/**
 * Shared AJAX helpers for the admin panel.
 *
 * Loaded globally from layouts/app.blade.php, so any page can use it without
 * repeating fetch/CSRF/loading/toast plumbing:
 *
 *   <form data-ajax-form action="{{ route('courses.store') }}" method="POST">
 *   form.addEventListener('ajax:success', (e) => e.detail.response);
 *
 *   App.toast('success', 'Saved');
 *   App.confirmDelete({ text: 'Delete this course?' }).then(...)
 *   App.request(url, { method: 'DELETE' });
 */
window.App = (function () {
    'use strict';

    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ── Notifications ──────────────────────────────────────────────────── */

    /**
     * Small top-centre toast. `icon` is any SweetAlert2 icon: success, error,
     * warning, info, question.
     */
    function toast(icon, title) {
        if (typeof Swal === 'undefined') {
            console[icon === 'error' ? 'error' : 'log'](title);
            return;
        }

        return Swal.fire({
            toast: true,
            position: 'top',
            icon,
            title,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            width: '22rem',
            customClass: { popup: 'app-toast' },
        });
    }

    /**
     * Confirmation dialog used before destructive actions.
     * Resolves to true when the user confirms.
     */
    async function confirmDelete({
        title = 'Are you sure?',
        text = 'This action cannot be undone.',
        confirmButtonText = 'Yes, delete it',
        cancelButtonText = 'Cancel',
    } = {}) {
        if (typeof Swal === 'undefined') {
            return window.confirm(`${title}\n\n${text}`);
        }

        const result = await Swal.fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText,
            cancelButtonText,
            reverseButtons: true,
            focusCancel: true,
            customClass: {
                confirmButton: 'app-swal-confirm',
                cancelButton: 'app-swal-cancel',
            },
            buttonsStyling: false,
        });

        return result.isConfirmed;
    }

    /* ── Requests ───────────────────────────────────────────────────────── */

    /**
     * fetch() wrapper that sends the CSRF token, asks for JSON, and throws an
     * error carrying { status, errors, payload } when the response is not ok.
     */
    async function request(url, { method = 'GET', body = null, headers = {} } = {}) {
        const options = {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
                ...headers,
            },
            credentials: 'same-origin',
        };

        if (body instanceof FormData) {
            // Let the browser set the multipart boundary itself.
            options.body = body;
        } else if (body !== null) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(body);
        }

        const response = await fetch(url, options);
        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            const error = new Error(payload.message || 'Something went wrong. Please try again.');
            error.status = response.status;
            error.errors = payload.errors || {};
            error.payload = payload;
            throw error;
        }

        return payload;
    }

    /* ── Button loading state ───────────────────────────────────────────── */

    const SPINNER = '<span class="app-spinner" aria-hidden="true"></span>';

    function setButtonLoading(button, loading) {
        if (!button) return;

        if (loading) {
            if (button.dataset.originalHtml === undefined) {
                button.dataset.originalHtml = button.innerHTML;
            }
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.classList.add('is-loading');
            button.innerHTML = `${SPINNER}<span>${button.dataset.loadingText || 'Saving…'}</span>`;
        } else {
            button.disabled = false;
            button.removeAttribute('aria-busy');
            button.classList.remove('is-loading');
            if (button.dataset.originalHtml !== undefined) {
                button.innerHTML = button.dataset.originalHtml;
            }
        }
    }

    /* ── Validation errors ──────────────────────────────────────────────── */

    function clearFieldErrors(form) {
        form.querySelectorAll('.app-field-error').forEach(node => node.remove());
        form.querySelectorAll('.app-field-invalid').forEach(node => node.classList.remove('app-field-invalid'));
    }

    function showFieldErrors(form, errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            // Laravel returns dotted keys for nested input names.
            const name = field.replace(/\.(\d+)/g, '[$1]');
            const input = form.querySelector(`[name="${name}"], [name="${name}[]"]`);
            if (!input) return;

            input.classList.add('app-field-invalid');

            const message = document.createElement('p');
            message.className = 'app-field-error';
            message.textContent = Array.isArray(messages) ? messages[0] : messages;

            // A Quill-backed textarea is hidden, so anchor the message to its editor.
            const anchor = input.classList.contains('hidden')
                ? input.previousElementSibling ?? input
                : input;
            anchor.insertAdjacentElement('afterend', message);
        });
    }

    /* ── Form handling ──────────────────────────────────────────────────── */

    /**
     * Quill writes into its hidden textarea on every keystroke, but flush it
     * here too so FormData never depends on listener ordering.
     */
    function syncQuillEditors(form) {
        form.querySelectorAll('textarea[data-quill]').forEach(textarea => {
            const quill = textarea.quillInstance;
            if (!quill) return;

            const isEmpty = quill.getText().trim() === '' && !quill.root.querySelector('img');
            textarea.value = isEmpty ? '' : quill.root.innerHTML;
        });
    }

    /**
     * Submits a form over AJAX. Disables the submit button with a spinner,
     * renders validation errors inline, toasts the outcome, and emits
     * `ajax:success` / `ajax:error` on the form so callers can react.
     */
    function submitForm(form, event) {
        // Something else already cancelled this submit (e.g. a required editor).
        if (event?.defaultPrevented) return;
        event?.preventDefault();

        // A form may have several submit buttons (Save Draft / Publish); the
        // spinner belongs on the one actually pressed.
        const button = event?.submitter
            || form.querySelector('[type="submit"]')
            || document.querySelector(`[type="submit"][form="${form.id}"]`);

        syncQuillEditors(form);
        clearFieldErrors(form);
        setButtonLoading(button, true);

        const method = (form.dataset.ajaxMethod || form.method || 'POST').toUpperCase();

        return request(form.action, { method: method === 'GET' ? 'GET' : 'POST', body: new FormData(form) })
            .then(payload => {
                if (payload.message) toast('success', payload.message);
                form.dispatchEvent(new CustomEvent('ajax:success', { detail: { payload } }));
                return payload;
            })
            .catch(error => {
                if (error.status === 422 && Object.keys(error.errors).length) {
                    showFieldErrors(form, error.errors);
                    toast('error', 'Please fix the highlighted fields.');
                } else {
                    toast('error', error.message);
                }
                form.dispatchEvent(new CustomEvent('ajax:error', { detail: { error } }));
            })
            .finally(() => setButtonLoading(button, false));
    }

    /* ── Server-side DataTables ─────────────────────────────────────────── */

    /**
     * Builds a server-side DataTable with the panel's shared defaults:
     * no client search box, cache-busted ajax, and skeleton rows in place of
     * the "Processing..." overlay.
     *
     * Extra options beyond DataTables' own:
     *   shimmerTemplate — selector of a <template> holding one skeleton <tr>
     *   shimmerRows     — how many to render (default 6)
     *   statsContainer  — selector of a stat-card grid to blank while loading
     *   onStats         — callback given `json.stats` after every draw
     */
    function dataTable(selector, options = {}) {
        const {
            shimmerTemplate = null,
            shimmerRows = 6,
            statsContainer = null,
            onStats = null,
            ajax = {},
            ...rest
        } = options;

        const table = new DataTable(selector, {
            processing: true,
            serverSide: true,
            searching: false,
            lengthMenu: [10, 25, 50, 100],
            pageLength: 10,
            language: {
                emptyTable: 'Nothing here yet.',
                zeroRecords: 'Nothing matches these filters.',
            },
            ...rest,
            ajax: {
                // jQuery caches GET requests, which would let ajax.reload()
                // replay a stale page after an edit.
                cache: false,
                ...ajax,
            },
        });

        const tableEl = document.querySelector(selector);
        const tableBody = tableEl?.querySelector('tbody');
        const template = shimmerTemplate ? document.querySelector(shimmerTemplate) : null;
        const stats = statsContainer ? document.querySelector(statsContainer) : null;

        function showShimmer() {
            stats?.classList.add('is-loading');
            if (!template || !tableBody) return;

            tableBody.replaceChildren();
            for (let row = 0; row < shimmerRows; row++) {
                tableBody.appendChild(template.content.cloneNode(true));
            }
        }

        showShimmer();

        $(selector).on('processing.dt', (e, settings, processing) => {
            if (processing) showShimmer();
        });

        // Extra payload keys (e.g. `stats`) ride along on the DataTables JSON.
        $(selector).on('xhr.dt', (e, settings, json) => {
            stats?.classList.remove('is-loading');
            if (onStats && json?.stats) onStats(json.stats);
        });

        return table;
    }

    /** Attaches AJAX submission to every form marked with data-ajax-form. */
    function bindForms(root = document) {
        root.querySelectorAll('form[data-ajax-form]').forEach(form => {
            if (form.dataset.ajaxBound) return;
            form.dataset.ajaxBound = 'true';
            form.addEventListener('submit', (event) => submitForm(form, event));
        });
    }

    document.addEventListener('DOMContentLoaded', () => bindForms());

    return {
        toast,
        confirmDelete,
        request,
        dataTable,
        submitForm,
        bindForms,
        setButtonLoading,
        clearFieldErrors,
        showFieldErrors,
        csrfToken,
    };
})();
