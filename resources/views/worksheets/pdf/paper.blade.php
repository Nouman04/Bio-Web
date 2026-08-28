<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $worksheet->title }} — Question paper</title>
    @include('worksheets.pdf._styles')
</head>
<body>

@include('worksheets.pdf._cover', ['scheme' => false])

<div class="page-break"></div>

<h2>Questions</h2>
<hr class="rule">

@forelse($questions as $index => $question)
    @php
        $ref = $references[$question->id] ?? null;
        $isMcq = $question->category?->type === 'mcqs';
    @endphp

    <div class="q">
        <div class="head">
            @if($ref && $ref->marks)
                <span class="marks">[{{ rtrim(rtrim(number_format((float) $ref->marks, 1, '.', ''), '0'), '.') }}]</span>
            @endif
            <span class="num">{{ $index + 1 }}.</span>
            <span class="text">{{ $question->question }}</span>
        </div>

        @if($ref)
            <p class="ref">
                @if($ref->paper_no) Paper {{ $ref->paper_no }} @endif
                @if($ref->date) &middot; {{ \Illuminate\Support\Carbon::parse($ref->date)->format('M Y') }} @endif
                @if($ref->question_no) &middot; Q{{ $ref->question_no }} @endif
                @if($ref->source) &middot; {{ $ref->source }} @endif
            </p>
        @endif

        @if($isMcq && $question->options->isNotEmpty())
            <ol class="options" type="A">
                @foreach($question->options as $option)
                    <li>{{ $option->title }}</li>
                @endforeach
            </ol>
        @else
            {{-- Room to write, sized off what the question is worth. --}}
            @php $lines = max(3, min(12, (int) ceil((float) ($ref->marks ?? 3)) * 2)); @endphp
            <div class="lines">
                @for($line = 0; $line < $lines; $line++)
                    <div></div>
                @endfor
            </div>
        @endif
    </div>
@empty
    <p class="muted">This worksheet has no questions on it.</p>
@endforelse

</body>
</html>
