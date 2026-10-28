@extends('layouts.student')

@section('title', $worksheet->title)
@section('page-title', $worksheet->title)
@section('page-subtitle', 'The paper, and what it is made of.')

@section('content')

<div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
    <a class="hover:text-primary transition-colors" href="{{ route('student.dashboard') }}">Home</a>
    <i class="fa-solid fa-chevron-right text-[10px]"></i>
    <a class="hover:text-primary transition-colors" href="{{ route('student.worksheets') }}">Worksheets</a>
    <i class="fa-solid fa-chevron-right text-[10px]"></i>
    <span class="text-primary dark:text-primary-fixed-dim font-semibold">{{ $worksheet->title }}</span>
</div>

{{-- The summary, the same one that opens both PDFs --}}
<div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl p-6 border border-outline-variant/30 dark:border-slate-700 shadow-sm mb-6">
    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
        <div class="min-w-0">
            <h2 class="text-xl font-bold text-on-surface dark:text-white mb-1">{{ $worksheet->title }}</h2>
            <p class="text-sm text-on-surface-variant dark:text-slate-400">
                {{ $worksheet->course?->title }}
                &middot; built by {{ $worksheet->creator?->name ?: 'Unknown' }}
                &middot; {{ $worksheet->created_at?->format('d M Y') }}
            </p>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 mt-5 text-xs">
                @foreach([
                    ['Chapters', $summary['chapters']],
                    ['Topics', $summary['topics']],
                    ['Papers', array_map(fn ($p) => 'Paper ' . $p, $summary['papers'])],
                    ['Years', $summary['years']],
                ] as [$label, $values])
                    <div>
                        <dt class="font-semibold text-on-surface-variant dark:text-slate-400 mb-1">{{ $label }}</dt>
                        <dd class="flex flex-wrap gap-1">
                            @forelse($values as $value)
                                <span class="px-2 py-0.5 rounded-full bg-surface-container-high text-on-surface-variant text-[11px]">{{ $value }}</span>
                            @empty
                                <span class="text-on-surface-variant/60">&mdash;</span>
                            @endforelse
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="shrink-0 w-full lg:w-auto">
            <div class="grid grid-cols-2 gap-3 mb-4 lg:w-64">
                @foreach([
                    ['Questions', $summary['total'], 'primary'],
                    ['Total marks', rtrim(rtrim(number_format($summary['marks'], 1, '.', ''), '0'), '.') ?: '0', 'tertiary'],
                    ['Multiple choice', $summary['mcqs'], 'secondary'],
                    ['Theory', $summary['theory'], 'secondary'],
                ] as [$label, $value, $tone])
                    <div class="rounded-xl bg-{{ $tone }}/5 p-3 text-center">
                        <p class="text-xl font-bold text-{{ $tone }} leading-tight">{{ $value }}</p>
                        <p class="text-[11px] text-on-surface-variant">{{ $label }}</p>
                    </div>
                @endforeach
            </div>

            {{-- The two documents, built on the queue: a click starts the
                 build, the bar under the button follows it, and the file
                 downloads when it is ready. --}}
            <div class="flex flex-col gap-3 lg:w-64">
                @foreach ([
                    ['paper', 'Question paper', 'fa-file-pdf', 'bg-primary text-on-primary shadow-md hover:shadow-lg'],
                    ['mark-scheme', 'Mark scheme', 'fa-list-check', 'bg-tertiary/10 text-tertiary hover:bg-tertiary/20'],
                ] as [$type, $label, $icon, $style])
                    <div class="flex flex-col gap-1.5" data-pdf="{{ $type }}">
                        <button type="button" data-url="{{ route('student.worksheets.pdf.generate', [$worksheet->uuid, $type]) }}"
                            class="pdf-start w-full text-center {{ $style }} px-5 py-2.5 rounded-full text-sm font-semibold transition-all flex items-center justify-center gap-2 disabled:opacity-60">
                            <i class="fa-solid {{ $icon }} text-xs"></i> {{ $label }}
                        </button>
                        <div class="pdf-progress hidden flex-col gap-1">
                            <div class="h-1.5 w-full bg-surface-container-high dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="pdf-bar h-full bg-primary rounded-full transition-all duration-500" style="width:0%"></div>
                            </div>
                            <div class="flex items-center justify-between gap-2 text-[11px] text-on-surface-variant dark:text-slate-400">
                                <span class="pdf-stage truncate"></span>
                                <span class="pdf-percent font-semibold shrink-0"></span>
                            </div>
                            <a class="pdf-download hidden text-[11px] font-semibold text-primary hover:underline">Download again</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- The questions themselves --}}
<div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl border border-outline-variant/30 dark:border-slate-700 shadow-sm divide-y divide-outline-variant/20 dark:divide-slate-700 overflow-hidden">
    <div class="px-6 py-4 bg-surface-container-low/40 dark:bg-slate-900/40">
        <h3 class="font-bold text-on-surface dark:text-white">{{ $questions->total() }} {{ Str::plural('question', $questions->total()) }}</h3>
    </div>

    @forelse($questions as $question)
        @php $ref = $references[$question->id] ?? null; @endphp
        <div class="px-6 py-4 flex items-start gap-4">
            {{-- Numbered across pages: page 2 starts at 13. --}}
            <span class="text-primary font-bold text-sm shrink-0 w-6">{{ $questions->firstItem() + $loop->index }}.</span>
            <div class="min-w-0 flex-1">
                <div class="question-html rich-content text-sm text-on-surface dark:text-slate-200">{!! $question->safe_question !!}</div>
                <p class="text-[11px] text-on-surface-variant dark:text-slate-500 mt-1">
                    {{ $question->category?->type === 'mcqs' ? 'Multiple choice' : 'Theory' }}
                    @if($question->difficulty_level) &middot; {{ $question->difficulty_level }} @endif
                    @if($ref?->paper_no) &middot; Paper {{ $ref->paper_no }} @endif
                    @if($ref?->date) &middot; {{ \Illuminate\Support\Carbon::parse($ref->date)->format('M Y') }} @endif
                    @if($ref?->question_no) &middot; Q{{ $ref->question_no }} @endif
                </p>
            </div>
            @if($ref && $ref->marks !== null)
                <span class="text-xs font-semibold text-on-surface-variant bg-surface-container-high px-2.5 py-1 rounded-full shrink-0">
                    {{ rtrim(rtrim(number_format((float) $ref->marks, 1, '.', ''), '0'), '.') }}
                </span>
            @endif
        </div>
    @empty
        <p class="px-6 py-12 text-center text-sm text-on-surface-variant">Nothing on this worksheet.</p>
    @endforelse
</div>

@if($questions->hasPages())
    <div class="mt-6">
        {{ $questions->links() }}
    </div>
@endif

@endsection

@push('styles')
<style>
    /* Question markup comes from the Quill editor; mirror the classes it writes. */
    .question-html p { margin: 0; }
    .question-html .ql-indent-1 { padding-left: 2em; }
    .question-html .ql-indent-2 { padding-left: 4em; }
    .question-html .ql-indent-3 { padding-left: 6em; }
    .question-html .ql-align-center { text-align: center; }
    .question-html .ql-align-right { text-align: right; }
    .question-html img { display: inline-block; max-width: 100%; height: auto; margin: 0.5rem 0; }
    .question-html table { width: 100%; border-collapse: collapse; margin: 0.5rem 0; }
    .question-html td, .question-html th { border: 1px solid currentColor; padding: 0.35rem 0.6rem; opacity: 0.9; }
    .question-html ol, .question-html ul { padding-left: 1.5em; }
    .question-html ol { list-style: decimal; }
    .question-html ul { list-style: disc; }
    .question-html p { overflow-wrap: anywhere; }
</style>
@endpush

@push('scripts')
<script>
    // Question paper / mark scheme: start the build on the queue, follow its
    // progress, and download the file when it is ready.
    document.querySelectorAll('[data-pdf]').forEach(box => {
        const button = box.querySelector('.pdf-start');
        const progress = box.querySelector('.pdf-progress');
        const bar = box.querySelector('.pdf-bar');
        const stage = box.querySelector('.pdf-stage');
        const percent = box.querySelector('.pdf-percent');
        const download = box.querySelector('.pdf-download');

        const draw = state => {
            progress.classList.replace('hidden', 'flex');
            bar.style.width = `${state.progress}%`;
            bar.classList.toggle('bg-error', state.status === 'failed');
            bar.classList.toggle('bg-primary', state.status !== 'failed');
            stage.textContent = state.error || state.stage || '';
            stage.classList.toggle('text-error', state.status === 'failed');
            percent.textContent = state.status === 'failed' ? '' : `${state.progress}%`;
            download.classList.toggle('hidden', state.status !== 'completed');
            if (state.download_url) download.href = state.download_url;
        };

        const follow = async state => {
            draw(state);
            if (state.status === 'completed') {
                window.location.href = state.download_url;
                button.disabled = false;
                return;
            }
            if (state.status === 'failed') {
                button.disabled = false;
                return;
            }
            setTimeout(async () => {
                try {
                    follow(await App.request(state.status_url));
                } catch {
                    // A dropped poll is retried rather than ending the wait.
                    follow(state);
                }
            }, 1500);
        };

        button.addEventListener('click', async () => {
            button.disabled = true;
            try {
                follow(await App.request(button.dataset.url, { method: 'POST' }));
            } catch {
                button.disabled = false;
                App.toast('error', 'Could not start the PDF. Please try again.');
            }
        });
    });
</script>
@endpush
