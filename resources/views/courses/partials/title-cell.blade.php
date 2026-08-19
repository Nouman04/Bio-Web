{{-- Title column for the courses DataTable --}}
<div class="flex flex-col gap-0.5 min-w-0">
    <a href="{{ route('courses.chapters', $course) }}"
        class="font-semibold text-on-surface dark:text-white hover:text-primary transition-colors truncate">
        {{ $course->title }}
    </a>
    <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
        /{{ $course->slug }} · updated {{ $course->updated_at?->diffForHumans() ?? '—' }}
    </span>
</div>
