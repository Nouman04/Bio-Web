{{-- Student column for the roster DataTable --}}
<div class="flex items-center gap-3 min-w-0">
    <span class="w-9 h-9 shrink-0 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold uppercase">
        {{ Str::of($student->name)->explode(' ')->take(2)->map(fn ($part) => Str::substr($part, 0, 1))->implode('') ?: '?' }}
    </span>
    <div class="flex flex-col gap-0.5 min-w-0">
        <a href="{{ route('students.show', $student->uuid) }}"
            class="font-semibold text-on-surface dark:text-white hover:text-primary transition-colors truncate">
            {{ $student->name }}
        </a>
        <span class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
            {{ $student->roles->pluck('name')->implode(', ') ?: 'No role' }}
        </span>
    </div>
</div>
