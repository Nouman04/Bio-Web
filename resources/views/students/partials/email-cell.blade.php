{{-- Email column for the roster DataTable --}}
<a href="mailto:{{ $student->email }}"
    class="text-sm text-on-surface-variant dark:text-slate-400 hover:text-primary transition-colors truncate block">
    {{ $student->email }}
</a>
