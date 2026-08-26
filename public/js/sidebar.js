/**
 * Sidebar behaviour shared by the admin and student rails.
 *
 * Collapsing was previously per-page state: collapse the rail, follow a link,
 * and it was wide again. It is remembered here instead.
 *
 * The class is also applied inline in the <head> (see the layouts) so a
 * remembered collapse is painted straight away rather than snapping shut after
 * the first frame.
 */
(function () {
    'use strict';

    const KEY = 'sidebar:collapsed';

    /** localStorage throws in private windows; a lost preference is not fatal. */
    function remember(collapsed) {
        try {
            window.localStorage.setItem(KEY, collapsed ? '1' : '0');
        } catch (e) {
            /* preference simply is not kept */
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebarToggle');

        if (!sidebar) {
            return;
        }

        // Take over from the pre-paint class on <html>. Both render the same,
        // so the swap is invisible.
        if (document.documentElement.classList.contains('sidebar-was-collapsed')) {
            sidebar.classList.add('collapsed');
            document.documentElement.classList.remove('sidebar-was-collapsed');
            toggle?.setAttribute('aria-expanded', 'false');
        }

        function paintArrow() {
            const icon = toggle?.querySelector('i');
            if (!icon) return;

            const collapsed = sidebar.classList.contains('collapsed');
            icon.classList.toggle('fa-chevron-right', collapsed);
            icon.classList.toggle('fa-chevron-left', !collapsed);
        }

        paintArrow();

        toggle?.addEventListener('click', () => {
            const collapsed = sidebar.classList.toggle('collapsed');
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            remember(collapsed);
            paintArrow();
        });

        // Keep the current page's link in view when the nav is long enough to
        // scroll — otherwise the active item can sit below the fold.
        const active = sidebar.querySelector('.sidebar-item.active');
        const scroller = sidebar.querySelector('.sidebar-nav-scroll');

        if (active && scroller && scroller.scrollHeight > scroller.clientHeight) {
            const top = active.offsetTop - (scroller.clientHeight / 2) + (active.offsetHeight / 2);
            scroller.scrollTop = Math.max(0, top);
        }
    });
})();
