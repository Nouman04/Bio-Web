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

        // Caught here, the reader sees the same inline messages the server
        // would have sent back — without the round trip. Opt out with
        // data-no-client-validation on the form.
        if (form.dataset.noClientValidation === undefined && !validateForm(form)) {
            return Promise.resolve();
        }

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

    /* ── File inputs ────────────────────────────────────────────────────── */

    /**
     * Every file input names what was picked, right under the field.
     *
     * A file input styled as a drop area shows nothing at all once a file is
     * chosen — the native "no file chosen" text is hidden along with the rest of
     * the control — so there was no way to tell whether the pick had taken. This
     * is delegated, so inputs inside a modal built later are covered without
     * re-binding.
     *
     * `data-current-name` on the input seeds the list with the file a record
     * already holds, so an edit form says what is attached before anything new
     * is chosen.
     */
    function fileFieldContainer(input) {
        return input.closest('[data-file-field]') ?? input.parentElement ?? input;
    }

    function readableSize(bytes) {
        if (!bytes) return '';
        const units = ['B', 'KB', 'MB', 'GB'];
        const power = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        return `${(bytes / 1024 ** power).toFixed(power ? 1 : 0)} ${units[power]}`;
    }

    function renderFileNames(input) {
        // A field that already names its own file — the video dropzone does —
        // opts out rather than showing the name twice.
        if (input.dataset.noFileName !== undefined) return;

        const container = fileFieldContainer(input);
        let list = container.nextElementSibling;

        if (!list || !list.classList || !list.classList.contains('app-file-names')) {
            list = document.createElement('ul');
            list.className = 'app-file-names';
            container.insertAdjacentElement('afterend', list);
        }

        const picked = Array.from(input.files || []).map(file => ({
            name: file.name,
            size: readableSize(file.size),
            stored: false,
        }));

        // Nothing picked yet: fall back to whatever the record already holds.
        const names = picked.length
            ? picked
            : (input.dataset.currentName ? [{ name: input.dataset.currentName, size: '', stored: true }] : []);

        list.replaceChildren();
        list.hidden = names.length === 0;

        names.forEach(entry => {
            const item = document.createElement('li');
            item.className = 'app-file-name';

            const icon = document.createElement('i');
            icon.className = `fa-solid ${entry.stored ? 'fa-paperclip' : 'fa-circle-check'} app-file-name-icon`;

            const label = document.createElement('span');
            label.className = 'app-file-name-text';
            label.textContent = entry.name;

            item.append(icon, label);

            const note = entry.size || (entry.stored ? 'currently attached' : '');
            if (note) {
                const meta = document.createElement('span');
                meta.className = 'app-file-name-size';
                meta.textContent = note;
                item.appendChild(meta);
            }

            list.appendChild(item);
        });
    }

    /** Draws the names for every file input under `root`. */
    function bindFileFields(root = document) {
        root.querySelectorAll('input[type="file"]').forEach(renderFileNames);
    }

    document.addEventListener('change', (event) => {
        if (event.target && event.target.matches && event.target.matches('input[type="file"]')) {
            renderFileNames(event.target);
        }
    });

    /* ── Client-side validation ─────────────────────────────────────────── */

    /**
     * The rules a field is checked against before anything is sent.
     *
     * `required`, `type`, `minlength`, `maxlength`, `min`, `max` and `pattern`
     * are read straight off the element, so a field that already states them
     * needs nothing extra. Anything the markup cannot say is a data attribute:
     *
     *   <input name="password_confirmation" data-rule-matches="password">
     *
     * Each rule returns a message when the value is wrong, or null when it is
     * fine. The messages are rendered exactly the way the server's are, so a
     * field looks the same whichever side caught it.
     */
    const RULES = [
        (input, value, label) => (isRequired(input) && value === '' ? `${label} is required.` : null),

        (input, value, label) => (value !== '' && input.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
            ? `${label} must be a valid email address.`
            : null),

        (input, value, label) => (value !== '' && input.type === 'url' && !/^https?:\/\/\S+$/i.test(value)
            ? `${label} must be a full URL, starting http:// or https://.`
            : null),

        (input, value, label) => {
            const min = Number(input.minLength);
            return min > 0 && value !== '' && value.length < min
                ? `${label} must be at least ${min} characters.`
                : null;
        },

        (input, value, label) => {
            const max = Number(input.maxLength);
            return max > 0 && value.length > max
                ? `${label} must be ${max} characters or fewer.`
                : null;
        },

        (input, value, label) => {
            if (value === '' || (input.type !== 'number' && input.type !== 'range')) return null;
            if (Number.isNaN(Number(value))) return `${label} must be a number.`;
            if (input.min !== '' && Number(value) < Number(input.min)) return `${label} must be ${input.min} or more.`;
            if (input.max !== '' && Number(value) > Number(input.max)) return `${label} must be ${input.max} or less.`;
            return null;
        },

        (input, value, label) => {
            const pattern = input.getAttribute('pattern');
            if (!pattern || value === '') return null;
            return new RegExp(`^(?:${pattern})$`).test(value)
                ? null
                : (input.dataset.patternMessage || `${label} is not in the expected format.`);
        },

        (input, value, label) => {
            const other = input.dataset.ruleMatches;
            if (!other || value === '') return null;
            const partner = input.form && input.form.querySelector(`[name="${other}"]`);
            return partner && partner.value !== value
                ? (input.dataset.matchesMessage || `${label} does not match.`)
                : null;
        },
    ];

    /** Whether this field has to be filled in. */
    function isRequired(input) {
        return input.required || input.dataset.ruleRequired !== undefined;
    }

    /** What to call the field in a message. */
    function labelFor(input) {
        if (input.dataset.label) return input.dataset.label;

        const wrapper = input.closest('[data-field], .flex.flex-col');
        const label = wrapper && wrapper.querySelector('label');
        const text = label && label.textContent.replace(/\(optional\)/i, '').trim();

        return text || 'This field';
    }

    /**
     * Checks a form against the rules above and renders the failures the same
     * way the server's are rendered. Returns true when everything passes.
     */
    function validateForm(form) {
        syncQuillEditors(form);
        clearFieldErrors(form);

        const errors = {};

        form.querySelectorAll('input, select, textarea').forEach(input => {
            if (input.disabled || input.type === 'hidden' || !input.name) return;
            if (errors[input.name]) return;

            // A file input is only ever "required", and not even that when the
            // record already holds one.
            if (input.type === 'file') {
                if (isRequired(input) && !input.files.length && !input.dataset.currentName) {
                    errors[input.name] = `${labelFor(input)} is required.`;
                }
                return;
            }

            if (input.type === 'checkbox' || input.type === 'radio') {
                if (isRequired(input) && !form.querySelector(`[name="${input.name}"]:checked`)) {
                    errors[input.name] = `${labelFor(input)} is required.`;
                }
                return;
            }

            const value = (input.value || '').trim();
            const label = labelFor(input);

            for (const rule of RULES) {
                const message = rule(input, value, label);
                if (message) {
                    errors[input.name] = message;
                    break;
                }
            }
        });

        if (Object.keys(errors).length === 0) return true;

        showFieldErrors(form, errors);
        toast('error', 'Please fix the highlighted fields.');

        // Bring the first problem into view rather than leaving it off-screen.
        const first = form.querySelector('.app-field-invalid');
        if (first) first.scrollIntoView({ block: 'center', behavior: 'smooth' });

        return false;
    }

    /** Attaches AJAX submission to every form marked with data-ajax-form. */
    function bindForms(root = document) {
        root.querySelectorAll('form[data-ajax-form]').forEach(form => {
            if (form.dataset.ajaxBound) return;
            form.dataset.ajaxBound = 'true';
            form.addEventListener('submit', (event) => submitForm(form, event));
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        bindForms();
        bindFileFields();

        // Plain (non-AJAX) forms get the same check, so the sign-in and
        // password pages report a bad value before a round trip.
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (event) => {
                if (!validateForm(form)) event.preventDefault();
            });
        });
    });

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
        validateForm,
        bindFileFields,
        csrfToken,
    };
})();
