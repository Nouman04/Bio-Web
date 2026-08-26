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

            {{-- The two documents. --}}
            <div class="flex flex-col gap-2 lg:w-64">
                <a href="{{ route('student.worksheets.paper', $worksheet->uuid) }}"
                    class="w-full text-center bg-gradient-to-r from-primary to-primary-container text-on-primary px-5 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-file-pdf text-xs"></i> Question paper
                </a>
                <a href="{{ route('student.worksheets.mark-scheme', $worksheet->uuid) }}"
                    class="w-full text-center bg-tertiary/10 text-tertiary px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-tertiary/20 transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-list-check text-xs"></i> Mark scheme
                </a>
            </div>
        </div>
    </div>
</div>

{{-- The questions themselves --}}
<div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl border border-outline-variant/30 dark:border-slate-700 shadow-sm divide-y divide-outline-variant/20 dark:divide-slate-700 overflow-hidden">
    <div class="px-6 py-4 bg-surface-container-low/40 dark:bg-slate-900/40">
        <h3 class="font-bold text-on-surface dark:text-white">{{ $questions->count() }} {{ Str::plural('question', $questions->count()) }}</h3>
    </div>

    @forelse($questions as $index => $question)
        @php $ref = $references[$question->id] ?? null; @endphp
        <div class="px-6 py-4 flex items-start gap-4">
            <span class="text-primary font-bold text-sm shrink-0 w-6">{{ $index + 1 }}.</span>
            <div class="min-w-0 flex-1">
                <p class="text-sm text-on-surface dark:text-slate-200">{{ $question->question }}</p>
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

@endsection
