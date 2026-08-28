{{--
    The bell in the header, and the panel it drops down.

    The list is filled by public/js/notifications.js — from the websocket when
    Pusher is connected, and from /notifications/feed on load and as a fallback
    poll. The markup here is the shell plus the empty and loading states.
--}}
@php
    $service = app(\App\Services\NotificationService::class);
    $user = auth()->user();
    $unread = $user?->unreadNotifications()->count() ?? 0;
@endphp

<div class="relative" id="notification-bell"
    data-feed="{{ route('notifications.feed') }}"
    data-read-all="{{ route('notifications.read-all') }}"
    data-index="{{ route('notifications') }}"
    @if($user)
        data-user="{{ $user->id }}"
        data-channel="{{ $service->channelFor($user) }}"
    @endif>

    <button type="button" id="notification-trigger" aria-haspopup="true" aria-expanded="false"
        class="w-9 h-9 sm:w-10 sm:h-10 bg-surface-container-lowest dark:bg-slate-800 rounded-full flex items-center justify-center text-on-surface-variant dark:text-slate-300 shadow-sm hover:text-primary hover:scale-105 active:scale-95 dark:hover:text-white transition-all relative border dark:border-slate-700"
        title="Notifications">
        <i class="fa-regular fa-bell"></i>

        {{-- The count, hidden while there is nothing to report. --}}
        <span id="notification-count"
            class="notification-count absolute -top-1 -right-1 min-w-[1.15rem] h-[1.15rem] px-1 rounded-full text-[10px] font-bold leading-[1.15rem] text-center {{ $unread ? '' : 'hidden' }}">
            {{ $unread > 99 ? '99+' : $unread }}
        </span>
    </button>

    {{-- Panel --}}
    <div id="notification-panel"
        class="hidden absolute right-0 mt-2 w-[22rem] max-w-[calc(100vw-2rem)] rounded-2xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 shadow-2xl z-50 overflow-hidden">

        <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-outline-variant/20 dark:border-slate-700 bg-surface-container-low/40 dark:bg-slate-900/40">
            <h3 class="text-sm font-bold text-on-surface dark:text-white">Notifications</h3>
            <button type="button" id="notification-read-all"
                class="text-xs font-semibold text-primary hover:underline disabled:opacity-40 disabled:no-underline"
                @disabled(! $unread)>
                Mark all read
            </button>
        </div>

        {{-- Rows land here. --}}
        <div id="notification-list" class="notification-scroll max-h-[22rem] overflow-y-auto divide-y divide-outline-variant/20 dark:divide-slate-700">
            <p class="px-4 py-10 text-center text-sm text-on-surface-variant dark:text-slate-400">Loading…</p>
        </div>

        <a href="{{ route('notifications') }}"
            class="flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-primary hover:bg-primary/5 transition-colors border-t border-outline-variant/20 dark:border-slate-700">
            View all notifications
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    </div>

    {{-- One row, cloned per notification by the script. --}}
    <template id="notification-row">
        <div class="notification-row flex items-start gap-3 px-4 py-3 hover:bg-primary/5 transition-colors" data-id="">
            <span class="notification-icon w-8 h-8 shrink-0 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs"></span>

            {{-- The row itself opens the notification: marks it read, then goes
                 wherever it points. --}}
            <a class="notification-open flex-1 min-w-0" href="#">
                <p class="notification-title text-sm font-semibold text-on-surface dark:text-white truncate"></p>
                <p class="notification-body text-xs text-on-surface-variant dark:text-slate-400 line-clamp-2"></p>
                <p class="notification-ago text-[11px] text-on-surface-variant/70 dark:text-slate-500 mt-0.5"></p>
            </a>

            <button type="button" class="notification-dismiss w-6 h-6 shrink-0 rounded-full flex items-center justify-center text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors"
                title="Mark as read">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>
    </template>
</div>
