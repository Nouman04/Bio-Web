{{-- Title column for the courses DataTable --}}
<div class="flex items-center gap-3 min-w-0">
    {{-- The cover, or the course's initial where there is none yet. --}}
    @if($course->image)
        <img src="{{ $course->image->url }}" alt=""
            class="w-10 h-10 shrink-0 rounded-lg object-cover border border-outline-variant/30 dark:border-slate-700">
    @else
        <span class="w-10 h-10 shrink-0 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs font-bold uppercase">
            {{ Str::substr($course->title, 0, 1) ?: '?' }}
        </span>
    @endif

    <div class="flex flex-col gap-0.5 min-w-0">
        <a href="{{ route('courses.chapters', $course) }}"
            class="font-semibold text-on-surface dark:text-white hover:text-primary transition-colors truncate">
            {{ $course->title }}
        </a>
        <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
            /{{ $course->slug }} · updated {{ $course->updated_at?->diffForHumans() ?? '—' }}
        </span>
    </div>
</div>
