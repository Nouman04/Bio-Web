{{-- The opening page of both documents: what this worksheet is made of. --}}
<div class="cover">
    <h1>{{ $worksheet->title }}</h1>
    <p class="sub">{{ $scheme ? 'Mark scheme' : 'Question paper' }} &middot; {{ $worksheet->course?->title }}</p>
    <hr class="rule">

    <table class="tiles">
        <tr>
            <td><span class="n">{{ $summary['total'] }}</span><span class="l">Questions</span></td>
            <td><span class="n">{{ rtrim(rtrim(number_format($summary['marks'], 1, '.', ''), '0'), '.') ?: '0' }}</span><span class="l">Total marks</span></td>
            <td><span class="n">{{ $summary['mcqs'] }}</span><span class="l">Multiple choice</span></td>
            <td><span class="n">{{ $summary['theory'] }}</span><span class="l">Theory</span></td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td class="k">Course</td>
            <td>{{ $worksheet->course?->title ?: '—' }}</td>
        </tr>
        <tr>
            <td class="k">Chapters</td>
            <td>
                @forelse($summary['chapters'] as $chapter)
                    <span class="chip">{{ $chapter }}</span>
                @empty
                    <span class="muted">All chapters</span>
                @endforelse
            </td>
        </tr>
        <tr>
            <td class="k">Topics</td>
            <td>
                {{-- Every topic that was asked for. One that had nothing under
                     the chosen papers or years is still listed, and says so. --}}
                @forelse($summary['topics'] as $topic)
                    @php $used = in_array($topic, $summary['topics_used'] ?? [], true); @endphp
                    <span class="chip">{{ $topic }}@unless($used)<span class="muted"> — none</span>@endunless</span>
                @empty
                    <span class="muted">—</span>
                @endforelse
            </td>
        </tr>
        <tr>
            <td class="k">Papers</td>
            <td>
                @forelse($summary['papers'] as $paper)
                    <span class="chip">Paper {{ $paper }}</span>
                @empty
                    <span class="muted">—</span>
                @endforelse
            </td>
        </tr>
        <tr>
            <td class="k">Years</td>
            <td>
                @forelse($summary['years'] as $year)
                    <span class="chip">{{ $year }}</span>
                @empty
                    <span class="muted">—</span>
                @endforelse
            </td>
        </tr>
        <tr>
            <td class="k">Generated</td>
            <td>
                {{ now()->format('d M Y, H:i') }}
                @if($worksheet->creator)
                    &middot; by {{ $worksheet->creator->name }}
                @endif
            </td>
        </tr>
    </table>

    @unless($scheme)
        <p class="small muted">
            Answer every question in the space provided. The mark for each question is shown in brackets.
        </p>
    @endunless
</div>
