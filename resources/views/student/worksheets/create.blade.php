@extends('layouts.student')

@section('title', 'Create Worksheet')
@section('page-title', 'Create Worksheet')
@section('page-subtitle', 'Pull questions out of the bank and turn them into a paper.')

@section('content')

<div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
    <a class="hover:text-primary transition-colors" href="{{ route('student.dashboard') }}">Home</a>
    <i class="fa-solid fa-chevron-right text-[10px]"></i>
    <a class="hover:text-primary transition-colors" href="{{ route('student.worksheets') }}">Worksheets</a>
    <i class="fa-solid fa-chevron-right text-[10px]"></i>
    <span class="text-primary dark:text-primary-fixed-dim font-semibold">Create</span>
</div>

<form id="worksheet-form" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf

    {{-- The selection --}}
    <div class="lg:col-span-2 flex flex-col gap-5">

        <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl p-6 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
            <h3 class="font-bold text-on-surface dark:text-white mb-5">What is on it</h3>

            <div class="flex flex-col gap-1.5 mb-5">
                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="title">Worksheet name</label>
                <input id="title" name="title" type="text" placeholder="e.g. Cell Biology — Paper 4 practice"
                    class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface dark:text-slate-200">
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="course">Course <span class="text-error">*</span></label>
                <select id="course" name="course"
                    class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface dark:text-slate-200">
                    <option value="">Choose a course…</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->uuid }}">{{ $course->title }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-on-surface-variant dark:text-slate-500">One course per worksheet. Only questions that came from a past paper are used.</p>
            </div>
        </div>

        <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl p-6 border border-outline-variant/30 dark:border-slate-700 shadow-sm">
            <h3 class="font-bold text-on-surface dark:text-white mb-1">What to pull in</h3>
            <p class="text-xs text-on-surface-variant dark:text-slate-400 mb-5">
                Choices inside one box are alternatives; the boxes stack up. Two topics
                and 2024 means <span class="font-semibold text-on-surface dark:text-slate-200">either topic, from 2024</span> —
                a topic with nothing that year simply contributes none. Leave a box empty
                to put no restriction on it.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="chapters">Chapters</label>
                    <select id="chapters" name="chapters[]" multiple placeholder="All chapters"></select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="topics">Topics</label>
                    <select id="topics" name="topics[]" multiple placeholder="All topics"></select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="papers">Paper numbers</label>
                    <select id="papers" name="papers[]" multiple placeholder="All papers">
                        @foreach($papers as $paper)
                            <option value="{{ $paper }}">Paper {{ $paper }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="years">Years</label>
                    <select id="years" name="years[]" multiple placeholder="All years">
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- What the selection would produce --}}
    <div class="lg:col-span-1">
        <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl p-6 border border-outline-variant/30 dark:border-slate-700 shadow-sm lg:sticky lg:top-4">
            <h3 class="font-bold text-on-surface dark:text-white mb-4">Your paper</h3>

            <div id="preview-empty" class="py-8 text-center">
                <i class="fa-solid fa-filter text-2xl text-on-surface-variant/40 mb-2"></i>
                <p class="text-sm text-on-surface-variant dark:text-slate-400">Choose a course to see the count.</p>
            </div>

            <div id="preview" class="hidden">
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="rounded-xl bg-primary/5 p-3 text-center">
                        <p class="text-2xl font-bold text-primary leading-tight" data-preview="total">0</p>
                        <p class="text-[11px] text-on-surface-variant">Questions</p>
                    </div>
                    <div class="rounded-xl bg-tertiary/5 p-3 text-center">
                        <p class="text-2xl font-bold text-tertiary leading-tight" data-preview="marks">0</p>
                        <p class="text-[11px] text-on-surface-variant">Total marks</p>
                    </div>
                    <div class="rounded-xl bg-surface-container p-3 text-center">
                        <p class="text-lg font-bold text-on-surface dark:text-white leading-tight" data-preview="mcqs">0</p>
                        <p class="text-[11px] text-on-surface-variant">Multiple choice</p>
                    </div>
                    <div class="rounded-xl bg-surface-container p-3 text-center">
                        <p class="text-lg font-bold text-on-surface dark:text-white leading-tight" data-preview="theory">0</p>
                        <p class="text-[11px] text-on-surface-variant">Theory</p>
                    </div>
                </div>

                <dl class="text-xs space-y-2 mb-5">
                    <div>
                        <dt class="font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Topics covered</dt>
                        <dd class="text-on-surface dark:text-slate-200" data-preview-list="topics">—</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Papers</dt>
                        <dd class="text-on-surface dark:text-slate-200" data-preview-list="papers">—</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Years</dt>
                        <dd class="text-on-surface dark:text-slate-200" data-preview-list="years">—</dd>
                    </div>
                </dl>

                <p id="preview-none" class="hidden text-xs text-error mb-4">
                    No past-paper question matches all of those. Try dropping a year or adding a paper.
                </p>

                <button type="submit" id="worksheet-submit"
                    class="w-full bg-primary text-on-primary px-6 py-3 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-file-circle-plus text-xs"></i>
                    Create worksheet
                </button>
                <p class="text-[11px] text-on-surface-variant dark:text-slate-500 text-center mt-3">
                    The paper and mark scheme are downloaded from the worksheet itself.
                </p>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('worksheet-form');
        const optionsUrl = '{{ route('student.worksheets.options') }}';
        const previewUrl = '{{ route('student.worksheets.preview') }}';
        const storeUrl = '{{ route('student.worksheets.store') }}';

        const settings = {
            plugins: ['remove_button'],
            valueField: 'uuid',
            labelField: 'title',
            searchField: 'title',
            maxOptions: null,
        };

        const chapters = new TomSelect('#chapters', settings);
        const topics = new TomSelect('#topics', settings);
        const papers = new TomSelect('#papers', { plugins: ['remove_button'] });
        const years = new TomSelect('#years', { plugins: ['remove_button'] });

        const course = document.getElementById('course');
        const preview = document.getElementById('preview');
        const previewEmpty = document.getElementById('preview-empty');
        const previewNone = document.getElementById('preview-none');
        const submit = document.getElementById('worksheet-submit');

        /** The five filters, as the server expects them. */
        function params() {
            const query = new URLSearchParams();
            query.set('course', course.value);
            chapters.getValue().forEach((v) => query.append('chapters[]', v));
            topics.getValue().forEach((v) => query.append('topics[]', v));
            papers.getValue().forEach((v) => query.append('papers[]', v));
            years.getValue().forEach((v) => query.append('years[]', v));
            return query;
        }

        function get(url) {
            return fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            }).then((r) => (r.ok ? r.json() : null));
        }

        // Chapters and topics both come from the course.
        function reloadOptions() {
            if (!course.value) {
                chapters.clear(true); chapters.clearOptions();
                topics.clear(true); topics.clearOptions();
                return Promise.resolve();
            }

            return get(optionsUrl + '?' + params().toString()).then((body) => {
                if (!body) return;

                const chosenChapters = chapters.getValue();
                chapters.clearOptions();
                chapters.addOptions(body.chapters);
                chapters.setValue(chosenChapters.filter((v) => body.chapters.some((c) => c.uuid === v)), true);

                const chosenTopics = topics.getValue();
                topics.clear(true);
                topics.clearOptions();
                topics.addOptions(body.topics);
                topics.setValue(chosenTopics.filter((v) => body.topics.some((t) => t.uuid === v)), true);
            });
        }

        let pending = null;
        function refreshPreview() {
            if (!course.value) {
                preview.classList.add('hidden');
                previewEmpty.classList.remove('hidden');
                return;
            }

            previewEmpty.classList.add('hidden');
            preview.classList.remove('hidden');

            // Only the newest answer counts; the filters change quickly.
            const token = Symbol('preview');
            pending = token;

            get(previewUrl + '?' + params().toString()).then((body) => {
                if (!body || pending !== token) return;

                document.querySelector('[data-preview="total"]').textContent = body.total;
                document.querySelector('[data-preview="marks"]').textContent =
                    Number(body.marks || 0).toFixed(1).replace(/\.0$/, '');
                document.querySelector('[data-preview="mcqs"]').textContent = body.mcqs;
                document.querySelector('[data-preview="theory"]').textContent = body.theory;

                const list = (key, values, prefix) => {
                    const el = document.querySelector('[data-preview-list="' + key + '"]');
                    el.textContent = values && values.length
                        ? values.map((v) => (prefix ?? '') + v).join(', ')
                        : '—';
                };
                list('topics', body.topics);
                list('papers', body.papers, 'Paper ');
                list('years', body.years);

                previewNone.classList.toggle('hidden', body.total > 0);
                submit.disabled = body.total === 0;
                submit.classList.toggle('opacity-50', body.total === 0);
            });
        }

        // Only the course changes what is on offer; the other four just add.
        course.addEventListener('change', () => reloadOptions().then(refreshPreview));
        [chapters, topics, papers, years].forEach((select) => select.on('change', refreshPreview));

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const title = document.getElementById('title');
            if (!title.value.trim()) {
                App.showFieldErrors(form, { title: ['Give the worksheet a name.'] });
                title.focus();
                return;
            }
            App.clearFieldErrors(form);

            const body = params();
            body.set('title', title.value.trim());

            App.setButtonLoading(submit, true);

            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': App.csrfToken(),
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                credentials: 'same-origin',
                body: body.toString(),
            })
                .then((r) => r.json().then((json) => ({ ok: r.ok, json })))
                .then(({ ok, json }) => {
                    if (!ok) {
                        App.setButtonLoading(submit, false);
                        App.showFieldErrors(form, json.errors ?? {});
                        App.toast('error', json.message ?? 'That did not save.');
                        return;
                    }
                    App.toast('success', json.message + ' ' + json.questions + ' questions.');
                    window.location = json.url;
                })
                .catch(() => {
                    App.setButtonLoading(submit, false);
                    App.toast('error', 'That did not save. Try again.');
                });
        });
    });
</script>
@endpush
