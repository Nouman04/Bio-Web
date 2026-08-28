@extends('layouts.app')

@section('title', 'Dashboard')
@section('meta-description', 'Platform overview: content, students and assessment at a glance.')

@section('page-title', 'Dashboard Overview')
@section('page-subtitle', "Here's what's happening with your platform today.")

@section('content')

@php
    use Laravel\Cashier\Cashier;

    // Written out in full so the classes survive a Tailwind build.
    $cards = [
        ['Courses',    $totals['courses'],    'fa-solid fa-graduation-cap',    'icon-bg-indigo', route('courses')],
        ['Chapters',   $totals['chapters'],   'fa-solid fa-layer-group',       'icon-bg-blue',   route('courses')],
        ['Questions',  $totals['questions'],  'fa-regular fa-circle-question', 'icon-bg-teal',   route('questions')],
        ['Quizzes',    $totals['quizzes'],    'fa-solid fa-clipboard-question','icon-bg-amber',  route('quizzes')],
        ['Students',   $totals['students'],   'fa-solid fa-users',             'icon-bg-rose',   route('students')],
        ['Categories', $totals['categories'], 'fa-solid fa-shapes',            'icon-bg-orange', route('categories')],
    ];
@endphp

{{-- ── Overview ───────────────────────────────────────────────────────── --}}
<div class="mb-6 flex flex-wrap justify-between items-end gap-3">
    <h3 class="text-xl font-semibold text-on-background dark:text-white">Overview</h3>
    <span class="text-xs text-on-surface-variant dark:text-slate-400">{{ now()->format('l, d M Y') }}</span>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    @foreach($cards as [$label, $stat, $icon, $tint, $url])
        <a href="{{ $url }}"
            class="stat-card p-6 rounded-3xl shadow-sm bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 relative overflow-hidden flex flex-col justify-between h-40">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-primary/10 rounded-full opacity-50"></div>

            <div class="flex justify-between items-start relative z-10">
                <div class="w-12 h-12 rounded-2xl {{ $tint }} flex items-center justify-center text-white shadow-md">
                    <i class="{{ $icon }} text-xl"></i>
                </div>
            </div>

            <div class="relative z-10 mt-auto">
                <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider mb-1">
                    Total {{ $label }}
                </p>
                <div class="flex items-baseline gap-3">
                    <span class="text-3xl font-bold text-on-background dark:text-white">{{ number_format($stat['value']) }}</span>
                    @if($stat['new'] > 0)
                        <span class="text-xs font-medium text-tertiary dark:text-tertiary-fixed-dim bg-tertiary-container/20 dark:bg-tertiary-container/40 px-2 py-0.5 rounded-full whitespace-nowrap">
                            +{{ $stat['new'] }} this month
                        </span>
                    @endif
                </div>
            </div>
        </a>
    @endforeach
</div>

{{-- ── Needs attention + subscriptions ────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-10">
    @php
        $tiles = [
            ['Awaiting marking', $attention['awaiting_marking'], 'fa-solid fa-pen-to-square', route('quizzes.review'), $attention['awaiting_marking'] > 0],
            ['Quizzes in progress', $attention['in_progress'], 'fa-solid fa-hourglass-half', route('quizzes'), false],
            ['Subscribed students', $attention['subscribers'], 'fa-solid fa-user-check', route('students'), false],
            ['Chapters with no questions', $attention['chapters_without_questions'], 'fa-solid fa-triangle-exclamation', route('questions'), $attention['chapters_without_questions'] > 0],
        ];
    @endphp

    @foreach($tiles as [$label, $value, $icon, $url, $urgent])
        <a href="{{ $url }}"
            class="flex items-center gap-4 p-5 rounded-2xl bg-surface-container-lowest dark:bg-slate-800 border {{ $urgent ? 'border-error/40' : 'border-outline-variant/30 dark:border-slate-700' }} shadow-sm hover:shadow-md transition-shadow">
            <span class="w-11 h-11 shrink-0 rounded-xl flex items-center justify-center {{ $urgent ? 'bg-error/10 text-error' : 'bg-primary/10 text-primary' }}">
                <i class="{{ $icon }}"></i>
            </span>
            <div class="min-w-0">
                <p class="text-2xl font-bold text-on-background dark:text-white leading-tight">{{ number_format($value) }}</p>
                <p class="text-xs text-on-surface-variant dark:text-slate-400 truncate">{{ $label }}</p>
            </div>
        </a>
    @endforeach
</div>

{{-- ── Charts ─────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Past-paper coverage: the widest real spread in the data. --}}
    <div class="lg:col-span-2 bg-surface-container-lowest dark:bg-slate-800 p-6 sm:p-8 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-6">
            <div>
                <h4 class="text-lg font-bold text-on-background dark:text-white">Past-paper coverage</h4>
                <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-1">
                    Questions in the bank, by the year they were set
                </p>
            </div>
            <span class="text-xs font-semibold text-on-surface-variant bg-surface-container-high dark:bg-slate-700 px-3 py-1 rounded-full">
                {{ array_sum($charts['papers']['data']) }} referenced
            </span>
        </div>
        <div class="h-64"><canvas id="chart-papers"></canvas></div>
    </div>

    {{-- What the library is made of. --}}
    <div class="bg-surface-container-lowest dark:bg-slate-800 p-6 sm:p-8 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <h4 class="text-lg font-bold text-on-background dark:text-white">Library</h4>
        <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-1 mb-6">What students have to study</p>
        <div class="h-64"><canvas id="chart-library"></canvas></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">

    {{-- Where the questions actually are. --}}
    <div class="lg:col-span-2 bg-surface-container-lowest dark:bg-slate-800 p-6 sm:p-8 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-6">
            <div>
                <h4 class="text-lg font-bold text-on-background dark:text-white">Questions per chapter</h4>
                <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-1">
                    A chapter with none cannot produce a worksheet
                </p>
            </div>
        </div>
        <div class="h-72"><canvas id="chart-chapters"></canvas></div>
    </div>

    {{-- How the assessment is going. --}}
    <div class="bg-surface-container-lowest dark:bg-slate-800 p-6 sm:p-8 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <h4 class="text-lg font-bold text-on-background dark:text-white">Quiz outcomes</h4>
        <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-1 mb-6">Every attempt on your courses</p>
        <div class="h-72"><canvas id="chart-outcomes"></canvas></div>
    </div>
</div>

{{-- ── Activity and money ─────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 bg-surface-container-lowest dark:bg-slate-800 p-6 sm:p-8 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-6">
            <div>
                <h4 class="text-lg font-bold text-on-background dark:text-white">Activity</h4>
                <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-1">Sign-ups and quiz attempts, month by month</p>
            </div>
            @if($charts['activity']['sparse'])
                <span class="text-xs font-medium text-on-surface-variant bg-surface-container-high dark:bg-slate-700 px-3 py-1 rounded-full">
                    Too little history to read as a trend
                </span>
            @endif
        </div>
        <div class="h-64"><canvas id="chart-activity"></canvas></div>
    </div>

    <div class="flex flex-col gap-6">
        {{-- Subscription income --}}
        <div class="bg-surface-container-lowest dark:bg-slate-800 p-6 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700">
            <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider mb-2">
                Recurring, per month
            </p>
            <p class="text-3xl font-bold text-on-background dark:text-white">
                {{ Cashier::formatAmount($revenue['monthly'], $revenue['currency']) }}
            </p>
            <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-2">
                From {{ $revenue['subscribers'] }} {{ Str::plural('subscriber', $revenue['subscribers']) }}
                across {{ $revenue['plans'] }} priced {{ Str::plural('plan', $revenue['plans']) }}.
                A yearly plan counts as a twelfth.
            </p>
        </div>

        {{-- Latest goings-on --}}
        <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-sm border border-outline-variant/30 dark:border-slate-700 flex-1 overflow-hidden">
            <div class="px-6 py-4 border-b border-outline-variant/20 dark:border-slate-700">
                <h4 class="text-base font-bold text-on-background dark:text-white">Latest activity</h4>
            </div>
            <div class="divide-y divide-outline-variant/20 dark:divide-slate-700">
                @forelse($recent as $row)
                    <a href="{{ $row['url'] }}" class="flex items-start gap-3 px-6 py-3 hover:bg-primary/5 transition-colors">
                        <span class="w-8 h-8 shrink-0 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs">
                            <i class="{{ $row['icon'] }}"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-on-surface dark:text-white truncate">{{ $row['title'] }}</p>
                            <p class="text-xs text-on-surface-variant dark:text-slate-400">
                                {{ $row['note'] }} &middot; {{ $row['when']->diffForHumans() }}
                            </p>
                        </div>
                    </a>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-on-surface-variant">Nothing has happened yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- The UMD build, which defines window.Chart. The chart.min.js already
     in public/cdn is the ES-module build: loaded with a plain script tag it
     defines nothing and the canvases stay blank. --}}
<script src="{{ asset('cdn/chart.umd.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Chart === 'undefined') {
            return;
        }

        const data = @json($charts);

        /* Colours come from the theme tokens, so the charts follow light and
           dark along with everything else. */
        const token = (name, alpha = 1) => {
            const rgb = getComputedStyle(document.documentElement)
                .getPropertyValue('--c-' + name).trim();
            return rgb ? `rgba(${rgb.split(/\s+/).join(', ')}, ${alpha})` : `rgba(0, 19, 48, ${alpha})`;
        };

        const ink = token('on-surface-variant');
        const grid = token('outline-variant', 0.35);

        Chart.defaults.font.family = 'Geist, system-ui, sans-serif';
        Chart.defaults.color = ink;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.boxWidth = 8;

        const axes = (horizontal = false) => ({
            x: {
                grid: { display: horizontal, color: grid, drawBorder: false },
                ticks: { color: ink, precision: 0 },
            },
            y: {
                grid: { display: !horizontal, color: grid, drawBorder: false },
                ticks: { color: ink, precision: 0 },
                beginAtZero: true,
            },
        });

        /* Past-paper coverage by year. */
        new Chart(document.getElementById('chart-papers'), {
            type: 'bar',
            data: {
                labels: data.papers.labels,
                datasets: [{
                    label: 'Questions',
                    data: data.papers.data,
                    backgroundColor: token('primary', 0.85),
                    hoverBackgroundColor: token('primary'),
                    borderRadius: 6,
                    maxBarThickness: 44,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: axes(),
            },
        });

        /* What the library holds. */
        new Chart(document.getElementById('chart-library'), {
            type: 'doughnut',
            data: {
                labels: data.library.labels,
                datasets: [{
                    data: data.library.data,
                    backgroundColor: [
                        token('primary', 0.9), token('secondary', 0.9), token('tertiary', 0.9),
                        token('primary', 0.55), token('secondary', 0.55), token('tertiary', 0.55),
                    ],
                    borderWidth: 0,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '58%',
                plugins: { legend: { position: 'bottom', labels: { padding: 14 } } },
            },
        });

        /* Where the questions sit. Horizontal, because chapter titles are long. */
        new Chart(document.getElementById('chart-chapters'), {
            type: 'bar',
            data: {
                labels: data.chapters.labels,
                datasets: [{
                    label: 'Questions',
                    data: data.chapters.data,
                    backgroundColor: data.chapters.data.map(
                        (n) => (n === 0 ? token('error', 0.75) : token('primary', 0.85))
                    ),
                    borderRadius: 6,
                    maxBarThickness: 26,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: axes(true),
            },
        });

        /* How attempts have gone. */
        new Chart(document.getElementById('chart-outcomes'), {
            type: 'doughnut',
            data: {
                labels: data.outcomes.labels,
                datasets: [{
                    data: data.outcomes.data,
                    backgroundColor: [
                        token('tertiary', 0.9),   // passed
                        token('error', 0.85),     // not passed
                        token('secondary', 0.9),  // awaiting marking
                        token('primary', 0.7),    // in progress
                        token('outline', 0.6),    // timed out
                    ],
                    borderWidth: 0,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '58%',
                plugins: { legend: { position: 'bottom', labels: { padding: 12 } } },
            },
        });

        /* Sign-ups against attempts. */
        new Chart(document.getElementById('chart-activity'), {
            type: 'line',
            data: {
                labels: data.activity.labels,
                datasets: [
                    {
                        label: 'New accounts',
                        data: data.activity.students,
                        borderColor: token('primary'),
                        backgroundColor: token('primary', 0.12),
                        fill: true, tension: 0.35,
                        pointRadius: 4, pointBackgroundColor: token('primary'),
                    },
                    {
                        label: 'Quiz attempts',
                        data: data.activity.attempts,
                        borderColor: token('tertiary'),
                        backgroundColor: token('tertiary', 0.12),
                        fill: true, tension: 0.35,
                        pointRadius: 4, pointBackgroundColor: token('tertiary'),
                    },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom', labels: { padding: 14 } } },
                scales: axes(),
            },
        });
    });
</script>
@endpush
