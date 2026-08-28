{{-- Status column — same pill shape as the chapters grid --}}
@php
    $pill = [
        'published' => 'bg-tertiary-container/20 text-tertiary',
        'closed' => 'bg-error/10 text-error',
    ][$quiz->status] ?? 'bg-surface-container-low text-on-surface-variant dark:bg-slate-900 dark:text-slate-300';
@endphp
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $pill }}">
    {{ ucfirst($quiz->status) }}
</span>
