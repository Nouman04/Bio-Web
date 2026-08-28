<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $worksheet->title }} — Mark scheme</title>
    @include('worksheets.pdf._styles')
</head>
<body>

@include('worksheets.pdf._cover', ['scheme' => true])

<div class="page-break"></div>

<h2>Mark scheme</h2>
<hr class="rule">

{{-- The paper at a glance: question number, where it came from, and what it is
     worth — before the answers themselves. --}}
<table class="scheme">
    <thead>
        <tr>
            <th style="width:6%">#</th>
            <th style="width:44%">Question</th>
            <th style="width:12%">Paper</th>
            <th style="width:14%">Date</th>
            <th style="width:10%">Q no.</th>
            <th style="width:8%">Marks</th>
        </tr>
    </thead>
    <tbody>
        @foreach($questions as $index => $question)
            @php $ref = $references[$question->id] ?? null; @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Illuminate\Support\Str::limit($question->question, 90) }}</td>
                <td>{{ $ref->paper_no ?? '—' }}</td>
                <td>{{ $ref?->date ? \Illuminate\Support\Carbon::parse($ref->date)->format('M Y') : '—' }}</td>
                <td>{{ $ref->question_no ?? '—' }}</td>
                <td>{{ $ref && $ref->marks !== null ? rtrim(rtrim(number_format((float) $ref->marks, 1, '.', ''), '0'), '.') : '—' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<hr class="thin-rule">

<h3>Answers</h3>

@forelse($questions as $index => $question)
    @php
        $ref = $references[$question->id] ?? null;
        $isMcq = $question->category?->type === 'mcqs';
        $answers = $question->answer;
    @endphp

    <div class="q">
        <div class="head">
            @if($ref && $ref->marks)
                <span class="marks">[{{ rtrim(rtrim(number_format((float) $ref->marks, 1, '.', ''), '0'), '.') }}]</span>
            @endif
            <span class="num">{{ $index + 1 }}.</span>
            <span class="text">{{ $question->question }}</span>
        </div>

        <p class="ref">
            @if($ref?->paper_no) Paper {{ $ref->paper_no }} @endif
            @if($ref?->date) &middot; {{ \Illuminate\Support\Carbon::parse($ref->date)->format('M Y') }} @endif
            @if($ref?->question_no) &middot; Q{{ $ref->question_no }} @endif
            @if($ref?->source) &middot; {{ $ref->source }} @endif
            @if($question->difficulty_level) &middot; {{ $question->difficulty_level }} @endif
        </p>

        @if($isMcq && $question->options->isNotEmpty())
            @php $correctIds = $answers->pluck('question_option_id')->filter()->all(); @endphp
            <ol class="options" type="A">
                @foreach($question->options as $option)
                    <li class="{{ in_array($option->id, $correctIds, true) ? 'correct' : '' }}">
                        {{ $option->title }}
                        @if(in_array($option->id, $correctIds, true)) &nbsp;&#10004; @endif
                    </li>
                @endforeach
            </ol>
        @endif

        @php
            $written = $answers->pluck('expected_answer')->filter()->implode(' ');
            $notes = $answers->pluck('description')->filter()->implode(' ');
        @endphp

        @if($written || $notes || (! $isMcq))
            <div class="answer">
                <span class="label">Answer</span>
                {{ $written ?: 'Not recorded.' }}
                @if($notes)
                    <div class="small muted" style="margin-top:4px;">{{ $notes }}</div>
                @endif
            </div>
        @endif
    </div>
@empty
    <p class="muted">This worksheet has no questions on it.</p>
@endforelse

</body>
</html>
