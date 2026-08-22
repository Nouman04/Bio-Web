{{--
    Turns a content page into one that reports progress.

    Include it on any detail page, handing it the record being shown:

        <x-module-progress :record="$note" />

    It renders nothing for guests, or for content that is not a tracked module.
    Everything else — the dwell timer, the video watcher, the manual checkbox —
    is driven by progress.js off the attributes below.

    A page with its own video keeps it inside this element, or the script falls
    back to the first <video> on the page.
--}}
@php
    $module = auth()->check()
        ? app(\App\Services\ProgressService::class)->moduleFor($record)
        : null;

    $state = $module?->progressFor(auth()->user());
@endphp

@if($module)
    <div data-module="{{ $module->uuid }}"
        data-module-type="{{ $module->type }}"
        data-module-base="{{ url('/student/progress') }}"
        class="{{ $attributes->get('class', '') }}">

        {{ $slot }}

        <label class="inline-flex items-center gap-3 mt-6 cursor-pointer select-none">
            <input type="checkbox"
                data-module-complete
                data-completed="{{ $state?->is_completed ? '1' : '0' }}"
                @checked($state?->is_completed)
                class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary/40">
            <span class="font-label-md text-label-md text-on-surface-variant">Mark as complete</span>
        </label>
    </div>

    @once
        @push('scripts')
            <script src="{{ asset('js/progress.js') }}"></script>
        @endpush
    @endonce
@else
    {{ $slot }}
@endif
