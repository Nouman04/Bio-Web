<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
<head>
    <meta charset="utf-8"/>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Portal') | EduAdmin LMS</title>
    <meta name="description" content="@yield('meta-description', 'EduStudent – Your Biology Exam Simplified')">

    {{-- Tailwind CSS --}}
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    {{-- The built stylesheet. Was cdn.tailwindcss.com, which compiles in
         the browser on every load — 3.6s of it on the chapter page. --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- Google Fonts --}}
    <link rel="stylesheet" href="{{ asset('fonts/geist.css') }}">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="{{ asset('cdn/font-awesome/all.min.css') }}">

    {{-- Material Symbols --}}
    <link rel="stylesheet" href="{{ asset('fonts/symbols.css') }}">
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>

    {{-- Tailwind Config (shared design tokens) --}}

    {{-- Global Styles --}}
    <style>
        body {
            transition: background-color 0.3s, color 0.3s;
        }

        /* Sidebar item icon animation */
        .sidebar-icon {
            transition: all 0.2s ease;
        }
        .sidebar-item:hover .sidebar-icon,
        .sidebar-item.active .sidebar-icon {
            transform: scale(1.1);
        }

        /* Stat card hover animation */
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }

        /* Icon gradient backgrounds */
        .icon-bg-indigo { background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%); }
        .icon-bg-orange { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); }
        .icon-bg-teal   { background: linear-gradient(135deg, #34d399 0%, #10b981 100%); }
        .icon-bg-blue   { background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%); }
        .icon-bg-rose   { background: linear-gradient(135deg, #fb7185 0%, #f43f5e 100%); }
        .icon-bg-amber  { background: linear-gradient(135deg, #fcd34d 0%, #d97706 100%); }

        /* Sidebar collapse / mobile drawer */
        #sidebar {
            transition: width 0.3s ease-in-out, transform 0.45s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.3s ease, border-color 0.3s ease;
            will-change: transform;
        }
        .hide-on-collapse {
            transition: opacity 0.2s ease, width 0.3s ease, margin 0.3s ease;
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
        }
        @media (min-width: 1024px) {
            #sidebar.collapsed {
                width: 6rem;
            }
            #sidebar.collapsed .hide-on-collapse {
                opacity: 0;
                width: 0;
                margin-left: 0;
            }
            #sidebar.collapsed .center-on-collapse {
                justify-content: center !important;
            }
            #sidebar.collapsed .px-8.center-on-collapse {
                padding-left: 0;
                padding-right: 0;
            }
        }

        /* Mobile full-screen drawer: staggered fade/rise for nav content on open */
        @media (max-width: 1023.98px) {
            #sidebar .mobile-fade,
            #sidebar .sidebar-item {
                opacity: 0;
                transform: translateY(10px);
                transition: opacity 0.35s ease, transform 0.35s ease;
            }
            #sidebar.is-open .mobile-fade,
            #sidebar.is-open .sidebar-item {
                opacity: 1;
                transform: translateY(0);
            }
            #sidebar.is-open .mobile-fade { transition-delay: 0.05s; }
            #sidebar.is-open .sidebar-item:nth-of-type(1) { transition-delay: 0.1s; }
            #sidebar.is-open .sidebar-item:nth-of-type(2) { transition-delay: 0.15s; }
            #sidebar.is-open .sidebar-item:nth-of-type(3) { transition-delay: 0.2s; }
            #sidebar.is-open .sidebar-item:nth-of-type(4) { transition-delay: 0.25s; }
        }

        /* Mobile sidebar overlay */
        #sidebarOverlay {
            transition: opacity 0.4s ease;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(118,117,134,0.3); border-radius: 99px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(118,117,134,0.5); }
    </style>

    @stack('styles')
    {{-- Sidebar chrome, shared by the admin and student rails. --}}
    <link href="{{ asset('cdn/tom-select/tomSelect.css') }}" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <script>
        // Applied before the first paint so a remembered collapse does not
        // snap shut a frame after the page appears.
        try {
            if (localStorage.getItem('sidebar:collapsed') === '1') {
                document.documentElement.classList.add('sidebar-was-collapsed');
            }
        } catch (e) { /* private windows simply start expanded */ }
    </script>
</head>


<body class="font-sans antialiased flex h-screen overflow-hidden bg-background text-on-background dark:bg-slate-900 dark:text-slate-200">

    {{-- Mobile Sidebar Overlay --}}
    <div id="sidebarOverlay"
        class="fixed inset-0 bg-black/40 z-10 hidden opacity-0 lg:hidden"></div>

    {{-- Student Sidebar --}}
    @include('layouts.partials.student-sidebar')

    {{-- Main Content Area --}}
    <main class="flex-1 flex flex-col h-full overflow-hidden relative transition-all duration-300">

        {{-- Background Decor --}}
        <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-primary/10 dark:bg-primary/5 rounded-full blur-3xl opacity-50 -z-10 translate-x-1/3 -translate-y-1/3 pointer-events-none"></div>

        {{-- Header --}}
        @include('layouts.partials.header')

        {{-- Page Content --}}
        <div class="flex-1 overflow-y-auto px-4 pb-6 sm:px-8 sm:pb-8 lg:px-12 custom-scrollbar">
            @hasSection('content')
                @yield('content')
            @elseif(isset($slot))
                {{ $slot }}
            @endif
        </div>

    </main>

    {{-- Global Search. Only what the catalogue actually exposes to a student
         is offered, so every hit leads somewhere they can open. --}}
    @include('partials.search-modal', [
        'searchUrl' => route('student.search'),
        'searchPlaceholder' => 'Search your courses and resources…',
        'searchTypes' => [
            'courses' => ['label' => 'Courses', 'icon' => 'fa-solid fa-graduation-cap'],
            'chapters' => ['label' => 'Chapters', 'icon' => 'fa-solid fa-book-bookmark'],
            'notes' => ['label' => 'Study Notes', 'icon' => 'fa-regular fa-note-sticky'],
            'flashcards' => ['label' => 'Flashcards', 'icon' => 'fa-solid fa-layer-group'],
            'quizzes' => ['label' => 'Quizzes', 'icon' => 'fa-solid fa-clipboard-question'],
        ],
    ])

    {{-- Save / Bookmark Toggle --}}
    <script>
        function toggleSaveIcon(btn) {
            const icon = btn.querySelector('.material-symbols-outlined');
            const label = btn.querySelector('[data-save-label]');
            const saved = btn.classList.toggle('is-saved');

            icon.textContent = saved ? 'bookmark' : 'bookmark_border';
            icon.style.fontVariationSettings = saved ? "'FILL' 1" : "'FILL' 0";
            icon.style.color = saved ? '#001330' : '';

            if (label) {
                label.textContent = saved ? 'Saved' : label.dataset.saveLabel;
            }
        }
    </script>

    {{-- Theme & Sidebar Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ── Theme Toggle ──────────────────────────────────────────────────────────
            const html = document.documentElement;
            const themeToggleBtn = document.getElementById('themeToggle');

            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }

            themeToggleBtn?.addEventListener('click', () => {
                html.classList.toggle('dark');
                localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
            });

            // ── Desktop Sidebar Collapse ───────────────────────────────────────────
            const sidebar = document.getElementById('sidebar');
            // Collapsing now lives in public/js/sidebar.js, which also remembers
            // the choice between pages. `sidebar` above is still used below.

            // ── Mobile Sidebar Toggle ──────────────────────────────────────────────
            const mobileToggle = document.getElementById('mobileSidebarToggle');
            const mobileClose = document.getElementById('mobileSidebarClose');
            const overlay = document.getElementById('sidebarOverlay');

            function openMobileSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                requestAnimationFrame(() => {
                    overlay.classList.remove('opacity-0');
                    sidebar.classList.add('is-open');
                });
            }

            function closeMobileSidebar() {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('is-open');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 400);
            }

            mobileToggle?.addEventListener('click', openMobileSidebar);
            mobileClose?.addEventListener('click', closeMobileSidebar);
            overlay?.addEventListener('click', closeMobileSidebar);

            // Close the mobile drawer automatically if the viewport grows into the desktop breakpoint
            window.matchMedia('(min-width: 1024px)').addEventListener('change', (e) => {
                if (e.matches) closeMobileSidebar();
            });
        });
    </script>

    {{-- SweetAlert2 and the shared AJAX helpers, the same pair the admin layout
         loads. Student pages use App.toast / App.setButtonLoading / App.request,
         so this has to come before @stack('scripts') below. --}}
    <script src="{{ asset('cdn/sweet-alert/sweetAlert2.min.js') }}"></script>
    <script src="{{ asset('cdn/tom-select/tomSelect.min.js') }}"></script>
    <script src="{{ asset('js/app-ajax.js') }}"></script>
    <script src="{{ asset('js/sidebar.js') }}"></script>

    {{-- Notifications: Pusher for live delivery, with the bell falling back
         to polling when the socket is unavailable. --}}
    <script src="{{ asset('cdn/pusher/pusher.min.js') }}"></script>
    <script>
        window.pusherConfig = @json([
            'key' => config('broadcasting.connections.pusher.key'),
            'cluster' => config('broadcasting.connections.pusher.options.cluster'),
        ]);
    </script>
    <script src="{{ asset('js/notifications.js') }}"></script>

    @stack('scripts')

</body>
</html>
