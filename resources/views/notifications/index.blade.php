{{--
    The full notification list.

    Staff and students both land here, so the layout follows whoever is signed
    in rather than the page having two copies.
--}}
@extends(auth()->user()?->isStaff() ? 'layouts.app' : 'layouts.student')

@section('title', 'Notifications')
@section('meta-description', 'Everything the system has told you.')

@section('page-title', 'Notifications')
@section('page-subtitle', 'Everything that has happened on your courses.')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
@endpush

@section('content')

@php
    $staff = auth()->user()?->isStaff();
    $anyFilter = filled($filters['search']) || filled($filters['state']);
@endphp

{{-- Breadcrumbs --}}
<div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6 pt-2">
    <a class="hover:text-primary transition-colors" href="{{ $staff ? route('dashboard') : route('student.dashboard') }}">
        {{ $staff ? 'Home' : 'Dashboard' }}
    </a>
    <i class="fa-solid fa-chevron-right text-[10px]"></i>
    <span class="text-primary dark:text-primary-fixed-dim font-semibold">Notifications</span>
</div>

<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-6">
    <div>
        <h1 class="text-on-background text-2xl font-bold">
            {{ $anyFilter ? $notifications->total() . ' ' . Str::plural('notification', $notifications->total()) . ' matched' : 'Notifications' }}
        </h1>
        <p class="text-on-surface-variant text-sm mt-1">
            @if($unread > 0)
                <span class="font-semibold text-primary">{{ $unread }}</span> unread.
            @else
                Nothing unread.
            @endif
        </p>
    </div>

    {{-- Search and read state, both handled on the server. --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant" style="font-size:18px;">search</span>
            <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search notifications"
                class="bg-surface-container-lowest border border-outline-variant text-on-surface text-sm rounded-lg py-2 pl-10 pr-4 focus:ring-2 focus:ring-primary transition-all w-full sm:w-56">
        </div>

        <x-chapter-filter.select name="state" :auto="false">
            <option value="">Read and unread</option>
            <option value="unread" @selected($filters['state'] === 'unread')>Unread only</option>
            <option value="read" @selected($filters['state'] === 'read')>Read only</option>
        </x-chapter-filter.select>

        <button type="submit" class="bg-primary-container text-on-primary-container text-sm font-semibold py-2 px-4 rounded-lg flex items-center gap-2 hover:bg-primary hover:text-on-primary transition-colors">
            <span class="material-symbols-outlined" style="font-size:18px;">filter_list</span> Filter
        </button>

        @if($anyFilter)
            <a href="{{ route('notifications') }}"
                class="text-on-surface-variant hover:text-primary text-sm flex items-center gap-1 transition-colors">
                <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span> Clear
            </a>
        @endif
    </form>
</div>

@if($unread > 0)
    <div class="flex justify-end mb-3">
        <button type="button" id="notifications-read-all" data-url="{{ route('notifications.read-all') }}"
            class="text-sm font-semibold text-primary hover:underline flex items-center gap-1.5">
            <i class="fa-solid fa-check-double text-xs"></i>
            Mark all as read
        </button>
    </div>
@endif

{{-- The list --}}
<div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl border border-outline-variant/30 dark:border-slate-700 shadow-sm divide-y divide-outline-variant/20 dark:divide-slate-700 overflow-hidden">
    @forelse($rows as $row)
        <div class="notification-item flex items-start gap-4 p-4 transition-colors {{ $row['read'] ? '' : 'bg-primary/5' }}"
            data-id="{{ $row['id'] }}">

            <span class="w-10 h-10 shrink-0 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <i class="{{ $row['icon'] }}"></i>
            </span>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-on-surface dark:text-white flex items-center gap-2">
                    {{ $row['title'] }}
                    @unless($row['read'])
                        <span class="w-2 h-2 rounded-full bg-primary shrink-0" title="Unread"></span>
                    @endunless
                </p>
                <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-0.5">{{ $row['body'] }}</p>
                <p class="text-[11px] text-on-surface-variant/70 dark:text-slate-500 mt-1">{{ $row['ago'] }}</p>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                {{-- Opening marks it read on the way through, then goes wherever
                     the notification points. --}}
                <a href="{{ route('notifications.show', $row['id']) }}"
                    class="w-9 h-9 rounded-lg flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-primary/10 transition-colors"
                    title="{{ $row['url'] ? 'Open it' : 'View' }}">
                    <i class="fa-solid fa-arrow-up-right-from-square text-sm"></i>
                </a>

                @if($row['read'])
                    <button type="button" class="notification-toggle w-9 h-9 rounded-lg flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-primary/10 transition-colors"
                        data-url="{{ route('notifications.unread', $row['id']) }}" title="Mark as unread">
                        <i class="fa-regular fa-envelope text-sm"></i>
                    </button>
                @else
                    <button type="button" class="notification-toggle w-9 h-9 rounded-lg flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-primary/10 transition-colors"
                        data-url="{{ route('notifications.read', $row['id']) }}" title="Mark as read">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                @endif
            </div>
        </div>
    @empty
        <div class="p-16 text-center">
            <div class="w-16 h-16 mx-auto rounded-full bg-primary/5 flex items-center justify-center mb-4">
                <i class="fa-regular fa-bell text-2xl text-primary"></i>
            </div>
            <h3 class="font-semibold text-on-surface dark:text-white mb-1">
                {{ $anyFilter ? 'Nothing matched' : 'Nothing yet' }}
            </h3>
            <p class="text-sm text-on-surface-variant dark:text-slate-400 mb-6">
                {{ $anyFilter
                    ? 'No notification matches those filters.'
                    : 'Notifications about your courses will appear here.' }}
            </p>
            @if($anyFilter)
                <a href="{{ route('notifications') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-on-primary text-sm font-semibold">
                    Clear filters
                    <i class="fa-solid fa-arrow-rotate-left text-xs"></i>
                </a>
            @endif
        </div>
    @endforelse
</div>

@if($notifications->hasPages())
    <div class="mt-8 flex justify-center">
        {{ $notifications->links() }}
    </div>
@endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        function post(url) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
                credentials: 'same-origin',
            }).then((response) => (response.ok ? response.json() : null));
        }

        // Toggling read state re-renders that row from the server, so the icon
        // and the tint never drift from what is actually stored.
        document.querySelectorAll('.notification-toggle').forEach((button) => {
            button.addEventListener('click', () => {
                post(button.dataset.url).then((body) => {
                    if (body) window.location.reload();
                });
            });
        });

        document.getElementById('notifications-read-all')?.addEventListener('click', (event) => {
            post(event.currentTarget.dataset.url).then((body) => {
                if (body) window.location.reload();
            });
        });
    });
</script>
@endpush
