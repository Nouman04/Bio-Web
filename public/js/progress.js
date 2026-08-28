/**
 * Reports what the reader did with a module back to the server.
 *
 * A page opts in with a single attribute on any element:
 *
 *     <div data-module="{uuid}" data-module-type="note">
 *
 * From there this decides what to watch for:
 *
 *   video    a <video> on the page, reported as it plays and once at 90%
 *   reading  a dwell timer that only counts while the tab is visible
 *   any      a [data-module-complete] checkbox or button, ticked by hand
 *
 * The server decides what any of it means; nothing here marks anything
 * complete on its own.
 */
(function () {
    'use strict';

    const READING_TYPES = ['note', 'diagram', 'guide', 'summary'];

    /** How long a reading page must be open, matching ProgressService. */
    const MIN_VIEW_SECONDS = 12;

    /** How often a playing video reports in, so a part-watch is not lost. */
    const VIDEO_REPORT_SECONDS = 15;

    function post(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        })
            .then((response) => (response.ok ? response.json() : null))
            .catch(() => null);
    }

    /**
     * Moves any bar on the page that is listening, so a completion shows
     * without a reload.
     */
    function paint(state) {
        if (!state) {
            return;
        }

        document.dispatchEvent(new CustomEvent('progress:updated', { detail: state }));

        document.querySelectorAll('[data-module-complete]').forEach((control) => {
            if (control.type === 'checkbox') {
                control.checked = state.completed;
            }
            control.dataset.completed = state.completed ? '1' : '0';
        });

        const set = (selector, value) => {
            document.querySelectorAll(selector).forEach((element) => {
                const percent = Math.round(value);
                element.style.width = percent + '%';
                element.dataset.progress = String(percent);
                if (element.hasAttribute('data-progress-label')) {
                    element.textContent = percent + '%';
                }
            });
        };

        if (state.chapter) {
            set('[data-progress-chapter]', state.chapter.progress);
        }
        if (state.course) {
            set('[data-progress-course]', state.course.progress);
        }
    }

    function start(root) {
        const uuid = root.dataset.module;
        const type = root.dataset.moduleType;

        if (!uuid) {
            return;
        }

        const base = (root.dataset.moduleBase || '/student/progress') + '/' + uuid;
        const report = (action, payload) => post(base + '/' + action, payload).then(paint);

        /* Manual "Mark as complete" — available on every type. */
        document.querySelectorAll('[data-module-complete]').forEach((control) => {
            control.addEventListener('change', () => {
                report('manual', { completed: control.checked ? 1 : 0 });
            });

            // A button rather than a checkbox toggles against what it last knew.
            if (control.type !== 'checkbox') {
                control.addEventListener('click', (event) => {
                    event.preventDefault();
                    report('manual', { completed: control.dataset.completed === '1' ? 0 : 1 });
                });
            }
        });

        if (type === 'video') {
            watchVideo(root, report);
        } else if (READING_TYPES.includes(type)) {
            timeReading(report);
        }
    }

    /**
     * Reports the furthest point reached rather than the current one, so
     * skipping back does not undo a watch.
     */
    function watchVideo(root, report) {
        const video = root.querySelector('video') || document.querySelector('video');

        if (!video) {
            return;
        }

        let furthest = 0;
        let lastReported = 0;
        let done = false;

        const percent = () => {
            if (!video.duration || !isFinite(video.duration)) {
                return 0;
            }
            furthest = Math.max(furthest, video.currentTime);

            return Math.min(100, Math.round((furthest / video.duration) * 100));
        };

        video.addEventListener('timeupdate', () => {
            const now = percent();

            if (done || now - lastReported < (VIDEO_REPORT_SECONDS / (video.duration || 1)) * 100) {
                return;
            }

            lastReported = now;
            done = now >= 90;
            report('watched', { percent: now });
        });

        // Watching to the end counts even if the last tick was missed.
        video.addEventListener('ended', () => {
            if (!done) {
                done = true;
                report('watched', { percent: 100 });
            }
        });

        // Leaving part way through keeps the position.
        window.addEventListener('pagehide', () => {
            const now = percent();
            if (!done && now > lastReported) {
                report('watched', { percent: now });
            }
        });
    }

    /**
     * Counts only the time the page is actually in front of the reader — a tab
     * left open in the background does not read itself.
     */
    function timeReading(report) {
        let seconds = 0;
        let sent = false;

        const tick = setInterval(() => {
            if (document.visibilityState !== 'visible') {
                return;
            }

            seconds += 1;

            if (!sent && seconds >= MIN_VIEW_SECONDS) {
                sent = true;
                clearInterval(tick);
                report('viewed', { seconds: seconds });
            }
        }, 1000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-module]').forEach(start);
    });
})();
