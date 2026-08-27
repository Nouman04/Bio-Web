/**
 * The notification bell.
 *
 * Live updates come over Pusher on the user's private channel. Laravel's
 * database+broadcast notifications arrive as the event
 * `Illuminate\Notifications\Events\BroadcastNotificationCreated`, carrying the
 * same payload the database row holds.
 *
 * The websocket is treated as an optimisation, never a requirement: the feed is
 * fetched on load and re-fetched on a slow timer, so the count is still right
 * on a page where Pusher is blocked or unconfigured.
 */
(function () {
    'use strict';

    const BROADCAST_EVENT = 'Illuminate\\Notifications\\Events\\BroadcastNotificationCreated';
    const POLL_MS = 60000;

    document.addEventListener('DOMContentLoaded', () => {
        const bell = document.getElementById('notification-bell');

        if (!bell) {
            return;
        }

        const trigger = document.getElementById('notification-trigger');
        const panel = document.getElementById('notification-panel');
        const list = document.getElementById('notification-list');
        const counter = document.getElementById('notification-count');
        const readAll = document.getElementById('notification-read-all');
        const template = document.getElementById('notification-row');

        const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        /* ── The count ──────────────────────────────────────────────────── */

        function paintCount(unread) {
            const n = Number(unread) || 0;

            counter.textContent = n > 99 ? '99+' : String(n);
            counter.classList.toggle('hidden', n === 0);

            if (readAll) readAll.disabled = n === 0;

            // The tab title carries it too, so it is visible from another tab.
            document.title = document.title.replace(/^\(\d+\+?\)\s*/, '');
            if (n > 0) document.title = '(' + (n > 99 ? '99+' : n) + ') ' + document.title;
        }

        /* ── Rows ───────────────────────────────────────────────────────── */

        function rowFor(item) {
            const row = template.content.cloneNode(true).firstElementChild;

            row.dataset.id = item.id;
            row.querySelector('.notification-icon').innerHTML = '<i class="' + item.icon + '"></i>';
            row.querySelector('.notification-title').textContent = item.title;
            row.querySelector('.notification-body').textContent = item.body;
            row.querySelector('.notification-ago').textContent = item.ago ?? '';

            // Opening goes through the app so the row is marked read on the way.
            row.querySelector('.notification-open').href = bell.dataset.index + '/' + item.id;

            if (!item.read) {
                row.classList.add('bg-primary/5', 'is-unread');
            } else {
                row.querySelector('.notification-dismiss').remove();
            }

            return row;
        }

        function paintList(items) {
            list.replaceChildren();

            if (!items.length) {
                const empty = document.createElement('p');
                empty.className = 'px-4 py-10 text-center text-sm text-on-surface-variant dark:text-slate-400';
                empty.textContent = 'Nothing yet.';
                list.appendChild(empty);
                return;
            }

            items.forEach((item) => list.appendChild(rowFor(item)));
        }

        /* ── Talking to the server ──────────────────────────────────────── */

        function refresh() {
            return fetch(bell.dataset.feed, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then((response) => (response.ok ? response.json() : null))
                .then((body) => {
                    if (!body) return;
                    paintCount(body.unread);
                    paintList(body.items ?? []);
                })
                .catch(() => { /* the count simply stays as it was */ });
        }

        function post(url) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf(),
                },
                credentials: 'same-origin',
            }).then((response) => (response.ok ? response.json() : null));
        }

        /* ── Interaction ────────────────────────────────────────────────── */

        function open() {
            panel.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
            refresh();
        }

        function close() {
            panel.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger?.addEventListener('click', (event) => {
            event.stopPropagation();
            panel.classList.contains('hidden') ? open() : close();
        });

        document.addEventListener('click', (event) => {
            if (!bell.contains(event.target)) close();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });

        // The ✕ on a row: read it without leaving the page.
        list.addEventListener('click', (event) => {
            const dismiss = event.target.closest('.notification-dismiss');
            if (!dismiss) return;

            event.preventDefault();
            event.stopPropagation();

            const row = dismiss.closest('.notification-row');
            const id = row?.dataset.id;
            if (!id) return;

            post(bell.dataset.index + '/' + id + '/read').then((body) => {
                if (!body) return;
                row.classList.remove('bg-primary/5', 'is-unread');
                dismiss.remove();
                paintCount(body.unread);
            });
        });

        readAll?.addEventListener('click', () => {
            post(bell.dataset.readAll).then((body) => {
                if (!body) return;
                paintCount(body.unread);
                refresh();
            });
        });

        /* ── Live ───────────────────────────────────────────────────────── */

        function listen() {
            const channelName = bell.dataset.channel;

            if (!channelName || typeof window.Pusher === 'undefined' || !window.pusherConfig?.key) {
                return false;
            }

            try {
                const pusher = new window.Pusher(window.pusherConfig.key, {
                    cluster: window.pusherConfig.cluster,
                    forceTLS: true,
                    authEndpoint: '/broadcasting/auth',
                    auth: { headers: { 'X-CSRF-TOKEN': csrf() } },
                });

                const channel = pusher.subscribe(channelName);

                channel.bind(BROADCAST_EVENT, () => {
                    // The payload is on the event, but re-reading the feed keeps
                    // the panel and the count consistent with the database.
                    refresh();
                });

                return true;
            } catch (e) {
                return false;
            }
        }

        refresh();
        listen();

        // Even with the socket up: a missed frame should not leave a stale count.
        window.setInterval(refresh, POLL_MS);
    });
})();
