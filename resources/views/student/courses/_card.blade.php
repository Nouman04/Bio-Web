{{--
    One course card. Courses carry no cover image, so the banner is a gradient
    keyed off the course id — stable per course, and no placeholder stock art.
--}}
@php
    $totals = $progress[$course->id] ?? ['progress' => 0, 'completed_weight' => 0, 'total_weight' => 0];
    $percent = (float) $totals['progress'];
    $palettes = [
        ['#001330', '#7c3aed'],
        ['#0891b2', '#001330'],
        ['#c026d3', '#7c3aed'],
        ['#059669', '#0891b2'],
        ['#ea580c', '#c026d3'],
    ];
    $palette = $palettes[$course->id % count($palettes)];
@endphp

<div class="glass-panel glass-panel-hover rounded-2xl overflow-hidden flex flex-col h-full bg-surface-container-lowest">
    <div class="relative h-48 w-full shrink-0 flex items-center justify-center"
        style="background-color: {{ $palette[0] }};">
        <span class="text-white/90 font-bold" style="font-size:56px;line-height:1;">{{ Str::upper(Str::substr($course->title, 0, 1)) }}</span>
        <div class="absolute inset-0 bg-black/30 opacity-60"></div>
        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-lg text-xs text-primary font-semibold tracking-wide shadow-sm flex items-center gap-1.5">
            <span class="material-symbols-outlined" style="font-size:16px;">school</span>
            {{ $course->category?->title ?: 'Uncategorised' }}
        </div>
        @unless($course->hasStripePlan())
            <div class="absolute top-4 right-4 bg-tertiary-container/90 backdrop-blur-md px-3 py-1.5 rounded-lg text-xs text-on-tertiary-container font-semibold shadow-sm">
                Free
            </div>
        @endunless
    </div>

    <div class="p-6 flex flex-col flex-1">
        <h3 class="text-on-surface mb-3 leading-tight line-clamp-2" style="font-size:20px;line-height:28px;font-weight:600;">{{ $course->title }}</h3>
        <p class="text-on-surface-variant mb-4 line-clamp-2 flex-1 text-sm leading-relaxed">{{ $course->excerpt ?: 'No description yet.' }}</p>

        <div class="flex items-center gap-4 text-outline text-xs mb-6">
            <span class="flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:14px;">menu_book</span>
                {{ $course->chapters_count }} {{ Str::plural('chapter', $course->chapters_count) }}
            </span>
            @if($course->creator)
                <span class="flex items-center gap-1 truncate">
                    <span class="material-symbols-outlined" style="font-size:14px;">person</span>
                    {{ $course->creator->name }}
                </span>
            @endif
        </div>

        <div class="mt-auto">
            <div class="flex justify-between items-center mb-2.5">
                <span class="text-outline text-xs font-medium">Course Progress</span>
                <span class="text-primary text-xs font-bold">{{ rtrim(rtrim(number_format($percent, 1), '0'), '.') }}%</span>
            </div>
            <div class="w-full bg-surface-variant/50 rounded-full h-2 mb-2 overflow-hidden">
                <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
            </div>
            <p class="text-outline text-xs mb-6">
                @if($totals['total_weight'] > 0)
                    {{ $totals['completed_weight'] }} of {{ $totals['total_weight'] }} completed
                @else
                    Nothing to track yet
                @endif
            </p>
            <a href="{{ route('student.chapters', ['courseId' => $course->uuid]) }}"
                class="w-full btn-primary-gradient text-on-primary text-sm font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                {{ $percent > 0 ? 'Continue Learning' : 'Start Learning' }}
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>
    </div>
</div>
