<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EduAdmin') | EduAdmin LMS</title>
    <meta name="description" content="@yield('meta-description', 'EduAdmin - Learning Management System Administration Panel')">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>

    {{-- Quill rich text editor (self-hosted) --}}
    <link href="{{ asset('cdn/quill/quill.css') }}" rel="stylesheet"/>

    {{-- SweetAlert2 (self-hosted) --}}
    <link href="{{ asset('cdn/sweet-alert/sweetAlert2.css') }}" rel="stylesheet"/>

    {{-- DataTables + the panel's shared table/shimmer styling --}}
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css" rel="stylesheet"/>
    <link href="{{ asset('css/app-datatable.css') }}" rel="stylesheet"/>

    {{-- Tom Select (self-hosted) — type-ahead pickers --}}
    <link href="{{ asset('cdn/tom-select/tomSelect.css') }}" rel="stylesheet"/>

    {{-- Tailwind Config --}}
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-secondary-fixed": "#2a1700",
                        "on-error": "#ffffff",
                        "tertiary-fixed-dim": "#4edea3",
                        "on-tertiary-fixed-variant": "#005236",
                        "surface-container-lowest": "#ffffff",
                        "surface": "#f7f9fb",
                        "on-primary-fixed": "#07006c",
                        "surface-bright": "#f7f9fb",
                        "on-tertiary-fixed": "#002113",
                        "inverse-primary": "#c0c1ff",
                        "background": "#f7f9fb",
                        "outline": "#767586",
                        "surface-container-low": "#f2f4f6",
                        "inverse-on-surface": "#eff1f3",
                        "surface-variant": "#e0e3e5",
                        "on-error-container": "#93000a",
                        "secondary-fixed-dim": "#ffb95f",
                        "outline-variant": "#c7c4d7",
                        "on-secondary-container": "#684000",
                        "tertiary": "#006c49",
                        "surface-container-high": "#e6e8ea",
                        "primary-container": "#6063ee",
                        "error-container": "#ffdad6",
                        "primary-fixed": "#e1e0ff",
                        "on-tertiary-container": "#000703",
                        "secondary-container": "#fea619",
                        "error": "#ba1a1a",
                        "surface-container": "#eceef0",
                        "on-primary-container": "#fffbff",
                        "inverse-surface": "#2d3133",
                        "on-secondary-fixed-variant": "#653e00",
                        "surface-container-highest": "#e0e3e5",
                        "tertiary-container": "#00885d",
                        "tertiary-fixed": "#6ffbbe",
                        "on-primary-fixed-variant": "#2f2ebe",
                        "on-primary": "#ffffff",
                        "on-surface": "#191c1e",
                        "surface-dim": "#d8dadc",
                        "secondary": "#855300",
                        "on-tertiary": "#ffffff",
                        "primary-fixed-dim": "#c0c1ff",
                        "on-surface-variant": "#464554",
                        "surface-tint": "#494bd6",
                        "primary": "#4648d4",
                        "on-secondary": "#ffffff",
                        "secondary-fixed": "#ffddb8",
                        "on-background": "#191c1e"
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    spacing: {
                        "stack_sm": "8px",
                        "stack_md": "16px",
                        "stack_lg": "24px",
                        "container_padding": "32px",
                        "sidebar_width": "260px",
                        "sidebar_collapsed": "80px",
                        "gutter": "24px",
                        "base": "8px"
                    },
                    fontFamily: {
                        "sans": ["Geist", "sans-serif"]
                    }
                }
            }
        }
    </script>

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

        /* Chart Styles */
        .bar-chart-bar {
            transition: height 1s ease-out, background-color 0.3s ease;
            border-radius: 6px 6px 0 0;
        }
        .line-chart-path {
            stroke-dasharray: 1000;
            stroke-dashoffset: 1000;
            animation: drawLine 2s forwards;
        }
        @keyframes drawLine {
            to { stroke-dashoffset: 0; }
        }

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
            #sidebar.is-open .sidebar-item:nth-of-type(1) { transition-delay: 0.08s; }
            #sidebar.is-open .sidebar-item:nth-of-type(2) { transition-delay: 0.11s; }
            #sidebar.is-open .sidebar-item:nth-of-type(3) { transition-delay: 0.14s; }
            #sidebar.is-open .sidebar-item:nth-of-type(4) { transition-delay: 0.17s; }
            #sidebar.is-open .sidebar-item:nth-of-type(5) { transition-delay: 0.2s; }
            #sidebar.is-open .sidebar-item:nth-of-type(6) { transition-delay: 0.23s; }
            #sidebar.is-open .sidebar-item:nth-of-type(7) { transition-delay: 0.26s; }
            #sidebar.is-open .sidebar-item:nth-of-type(8) { transition-delay: 0.29s; }
            #sidebar.is-open .sidebar-item:nth-of-type(9) { transition-delay: 0.32s; }
            #sidebar.is-open .sidebar-item:nth-of-type(10) { transition-delay: 0.35s; }
            #sidebar.is-open .sidebar-item:nth-of-type(11) { transition-delay: 0.38s; }
            #sidebar.is-open .sidebar-item:nth-of-type(12) { transition-delay: 0.41s; }
            #sidebar.is-open .sidebar-item:nth-of-type(13) { transition-delay: 0.44s; }
            #sidebar.is-open .sidebar-item:nth-of-type(14) { transition-delay: 0.47s; }
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

        /* Sidebar nav: fade the top/bottom edges as a scroll affordance */
        .sidebar-nav-scroll {
            -webkit-mask-image: linear-gradient(to bottom, transparent 0, black 16px, black calc(100% - 16px), transparent 100%);
            mask-image: linear-gradient(to bottom, transparent 0, black 16px, black calc(100% - 16px), transparent 100%);
        }
        .sidebar-nav-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-nav-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav-scroll::-webkit-scrollbar-thumb { background: rgba(118,117,134,0.25); border-radius: 99px; }
        .sidebar-nav-scroll::-webkit-scrollbar-thumb:hover { background: rgba(118,117,134,0.45); }

        /* Section labels above each nav group */
        .sidebar-group-label {
            transition: opacity 0.2s ease, height 0.2s ease, padding 0.2s ease, margin 0.2s ease;
        }
        @media (min-width: 1024px) {
            #sidebar.collapsed .sidebar-group-label {
                opacity: 0;
                height: 0;
                margin: 0;
                padding-top: 0;
                padding-bottom: 0;
                overflow: hidden;
            }
        }
        /* ── AJAX feedback: button spinners, inline errors, toasts ─────────── */
        .app-spinner {
            display: inline-block;
            width: 0.875rem;
            height: 0.875rem;
            margin-right: 0.5rem;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 9999px;
            vertical-align: -2px;
            animation: app-spin 0.65s linear infinite;
        }
        @keyframes app-spin { to { transform: rotate(360deg); } }
        button.is-loading { opacity: 0.75; cursor: progress; }
        button:disabled { cursor: not-allowed; }

        .app-field-error {
            margin-top: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #ba1a1a;
        }
        .app-field-invalid {
            border-color: #ba1a1a !important;
            box-shadow: 0 0 0 1px rgba(186, 26, 26, 0.25);
        }

        /* Compact toast, anchored top-centre */
        .swal2-container.swal2-top { padding-top: 1rem; }
        .app-toast.swal2-popup {
            padding: 0.75rem 1rem;
            border-radius: 0.875rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.14);
        }
        .app-toast .swal2-title {
            font-size: 0.875rem !important;
            font-weight: 600;
            padding: 0;
            margin: 0;
        }
        .app-toast .swal2-icon { width: 1.5rem; height: 1.5rem; margin: 0 0.625rem 0 0; }
        .dark .app-toast.swal2-popup { background: rgb(30, 41, 59); color: rgb(226, 232, 240); }
        .dark .swal2-popup { background: rgb(30, 41, 59); color: rgb(226, 232, 240); }
        .dark .swal2-popup .swal2-title,
        .dark .swal2-popup .swal2-html-container { color: rgb(226, 232, 240); }

        /* Confirm dialog buttons, styled like the panel's own */
        .app-swal-confirm,
        .app-swal-cancel {
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            margin: 0 0.25rem;
            cursor: pointer;
        }
        .app-swal-confirm { background: #ba1a1a; color: #ffffff; }
        .app-swal-confirm:hover { background: #a31616; }
        .app-swal-cancel {
            background: transparent;
            color: #767586;
            border: 1px solid rgba(118, 117, 134, 0.4);
        }
        .app-swal-cancel:hover { background: rgba(118, 117, 134, 0.08); }

        /* ── Tom Select (question picker) ──────────────────────────────────── */
        .question-widget .ts-wrapper { margin: 0; }
        .question-widget .ts-control {
            background: #ffffff;
            border: 1px solid rgba(118, 117, 134, 0.35);
            border-radius: 0.5rem;
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
            min-height: 2.5rem;
            box-shadow: none;
            /* Linked questions scroll instead of stretching the modal */
            max-height: 11rem;
            overflow-y: auto;
        }
        .question-widget .ts-control::-webkit-scrollbar { width: 6px; }
        .question-widget .ts-control::-webkit-scrollbar-track { background: transparent; }
        .question-widget .ts-control::-webkit-scrollbar-thumb {
            background: rgba(118, 117, 134, 0.28);
            border-radius: 9999px;
        }
        .question-widget .ts-control::-webkit-scrollbar-thumb:hover { background: rgba(118, 117, 134, 0.45); }
        .dark .question-widget .ts-control::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.3); }
        .question-widget .ts-wrapper.focus .ts-control {
            border-color: #4648d4;
            box-shadow: 0 0 0 1px rgba(70, 72, 212, 0.35);
        }
        .question-widget .ts-control > .item {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            max-width: 100%;
            background: rgba(70, 72, 212, 0.08);
            color: #4648d4;
            border: none;
            border-radius: 9999px;
            padding: 0.1875rem 0.5rem 0.1875rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .question-widget .ts-control > .item > div {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 22rem;
        }
        .question-widget .ts-control > .item .remove {
            border-left: none;
            padding: 0 0.125rem;
            opacity: 0.7;
        }
        .question-widget .ts-control > .item .remove:hover { background: transparent; opacity: 1; }
        /* Dropdowns render on <body> (dropdownParent), so they sit outside
           .question-widget and must clear the modal's z-index. */
        .ts-dropdown {
            z-index: 200;
            border: 1px solid rgba(118, 117, 134, 0.25);
            border-radius: 0.75rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
            overflow: hidden;
            margin-top: 0.25rem;
            font-size: 0.875rem;
        }
        .ts-dropdown .active { background: rgba(70, 72, 212, 0.08); }
        .dark .question-widget .ts-control {
            background: rgb(30, 41, 59);
            border-color: rgb(51, 65, 85);
            color: rgb(226, 232, 240);
        }
        .dark .question-widget .ts-control > .item {
            background: rgba(70, 72, 212, 0.28);
            color: #c0c1ff;
        }
        .dark .ts-dropdown {
            background: rgb(30, 41, 59);
            border-color: rgb(51, 65, 85);
            color: rgb(226, 232, 240);
        }
        .dark .ts-dropdown .active { background: rgba(70, 72, 212, 0.3); }
        .question-widget .ts-control input::placeholder { color: #767586; }

        /* ── Quill editor ──────────────────────────────────────────────────── */
        .quill-wrapper .ql-toolbar.ql-snow,
        .quill-wrapper .ql-container.ql-snow {
            border-color: rgba(118, 117, 134, 0.35);
        }
        .quill-wrapper .ql-toolbar.ql-snow {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            background: #f2f4f6;
        }
        .quill-wrapper .ql-container.ql-snow {
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
            background: #ffffff;
            font-family: inherit;
            font-size: 0.875rem;
        }
        .quill-wrapper .ql-editor {
            min-height: var(--quill-min-height, 180px);
        }
        .quill-wrapper .ql-editor.ql-blank::before {
            font-style: normal;
            color: #767586;
        }
        .quill-wrapper.is-invalid .ql-toolbar.ql-snow,
        .quill-wrapper.is-invalid .ql-container.ql-snow {
            border-color: #ba1a1a;
        }
        .quill-error {
            display: none;
            margin-top: 0.375rem;
            font-size: 0.75rem;
            color: #ba1a1a;
        }
        .quill-wrapper.is-invalid + .quill-error { display: block; }

        /* Dark mode — Quill's snow theme only ships light styling */
        .dark .quill-wrapper .ql-toolbar.ql-snow,
        .dark .quill-wrapper .ql-container.ql-snow {
            border-color: rgb(51, 65, 85);
        }
        .dark .quill-wrapper .ql-toolbar.ql-snow { background: rgb(15, 23, 42); }
        .dark .quill-wrapper .ql-container.ql-snow {
            background: rgb(30, 41, 59);
            color: rgb(226, 232, 240);
        }
        .dark .quill-wrapper .ql-editor.ql-blank::before { color: rgb(100, 116, 139); }
        .dark .quill-wrapper .ql-snow .ql-stroke { stroke: rgb(203, 213, 225); }
        .dark .quill-wrapper .ql-snow .ql-fill { fill: rgb(203, 213, 225); }
        .dark .quill-wrapper .ql-snow .ql-picker { color: rgb(203, 213, 225); }
        .dark .quill-wrapper .ql-snow .ql-picker-options {
            background: rgb(30, 41, 59);
            border-color: rgb(51, 65, 85);
        }
    </style>

    @stack('styles')
</head>

<body class="font-sans antialiased flex h-screen overflow-hidden bg-background text-on-background dark:bg-slate-900 dark:text-slate-200">

    {{-- Mobile Sidebar Overlay --}}
    <div id="sidebarOverlay"
        class="fixed inset-0 bg-black/40 z-10 hidden opacity-0 lg:hidden"></div>

    {{-- Sidebar Partial --}}
    @include('layouts.partials.sidebar')

    {{-- Main Content Area --}}
    <main class="flex-1 flex flex-col h-full overflow-hidden relative transition-all duration-300">

        {{-- Background Decor --}}
        <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-primary/10 dark:bg-primary/5 rounded-full blur-3xl opacity-50 -z-10 translate-x-1/3 -translate-y-1/3 pointer-events-none"></div>

        {{-- Header Partial --}}
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

    {{-- Global Search. The keys match the groups SearchController@admin
         returns, so a pill filters without any mapping on the client. --}}
    @include('partials.search-modal', [
        'searchUrl' => route('search'),
        'searchPlaceholder' => 'Search modules, content, or students…',
        'searchTypes' => [
            'courses' => ['label' => 'Courses', 'icon' => 'fa-solid fa-graduation-cap'],
            'chapters' => ['label' => 'Chapters', 'icon' => 'fa-solid fa-book-bookmark'],
            'topics' => ['label' => 'Topics', 'icon' => 'fa-solid fa-tags'],
            'quizzes' => ['label' => 'Quizzes', 'icon' => 'fa-solid fa-clipboard-question'],
            'questions' => ['label' => 'Questions', 'icon' => 'fa-regular fa-circle-question'],
            'flashcards' => ['label' => 'Flashcards', 'icon' => 'fa-solid fa-layer-group'],
            'videos' => ['label' => 'Video Lessons', 'icon' => 'fa-solid fa-circle-play'],
            'notes' => ['label' => 'Notes', 'icon' => 'fa-regular fa-note-sticky'],
            'guides' => ['label' => 'Guides', 'icon' => 'fa-solid fa-book-open'],
            'diagrams' => ['label' => 'Diagrams', 'icon' => 'fa-regular fa-image'],
            'summaries' => ['label' => 'Summaries', 'icon' => 'fa-solid fa-list-check'],
            'categories' => ['label' => 'Categories', 'icon' => 'fa-solid fa-folder-open'],
            'students' => ['label' => 'Students', 'icon' => 'fa-solid fa-users'],
        ],
    ])

    {{-- Theme & Sidebar Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ÔöÇÔöÇ Theme Toggle ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
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

            // ÔöÇÔöÇ Desktop Sidebar Collapse ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');

            toggleBtn?.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                const icon = toggleBtn.querySelector('i');
                if (sidebar.classList.contains('collapsed')) {
                    icon.classList.replace('fa-chevron-left', 'fa-chevron-right');
                } else {
                    icon.classList.replace('fa-chevron-right', 'fa-chevron-left');
                }
            });

            // ÔöÇÔöÇ Mobile Sidebar Toggle ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ
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

            // ── Table Row Action Dropdowns (the "..." menu in Actions columns) ─────
            // The menu is moved onto <body> and positioned with `fixed` while open, so
            // the table card's overflow and the pagination footer can never clip it or
            // paint over it — including for the last row of a table.
            const actionMenu = { element: null, anchor: null, trigger: null };

            function closeActionMenu() {
                const menu = actionMenu.element;
                if (!menu) return;

                menu.classList.add('hidden');
                menu.style.position = '';
                menu.style.top = '';
                menu.style.left = '';
                menu.style.right = '';
                menu.style.margin = '';
                menu.style.zIndex = '';

                // Put it back where it came from so the markup stays intact.
                actionMenu.anchor.replaceWith(menu);
                actionMenu.element = null;
                actionMenu.anchor = null;
                actionMenu.trigger = null;
            }

            function openActionMenu(menu, trigger) {
                closeActionMenu();

                const anchor = document.createComment('action-dropdown-menu');
                menu.replaceWith(anchor);
                document.body.appendChild(menu);
                actionMenu.element = menu;
                actionMenu.anchor = anchor;
                actionMenu.trigger = trigger;

                menu.classList.remove('hidden');
                menu.style.position = 'fixed';
                menu.style.right = 'auto';
                menu.style.margin = '0';
                menu.style.zIndex = '200';

                const triggerRect = trigger.getBoundingClientRect();
                const menuRect = menu.getBoundingClientRect();
                const gap = 4;

                // Right-align to the trigger, then keep it inside the viewport.
                let left = triggerRect.right - menuRect.width;
                left = Math.min(Math.max(left, 8), window.innerWidth - menuRect.width - 8);

                // Drop down by default; flip above the trigger when the bottom would overflow.
                let top = triggerRect.bottom + gap;
                if (top + menuRect.height > window.innerHeight - 8) {
                    top = Math.max(triggerRect.top - menuRect.height - gap, 8);
                }

                menu.style.left = `${left}px`;
                menu.style.top = `${top}px`;
            }

            // Delegated so rows rendered later — DataTables pages, for instance —
            // get working dropdowns without re-binding.
            document.addEventListener('click', (e) => {
                // Clicks inside the open menu are handled by the item itself.
                if (actionMenu.element?.contains(e.target)) return;

                const trigger = e.target.closest('.action-dropdown-trigger');
                const wasOpenForThisTrigger = trigger && actionMenu.trigger === trigger;

                closeActionMenu();
                if (!trigger || wasOpenForThisTrigger) return;

                const menu = trigger.closest('.action-dropdown')?.querySelector('.action-dropdown-menu');
                if (menu) openActionMenu(menu, trigger);
            });

            // A fixed menu would stay behind while the page moves, so dismiss it instead.
            window.addEventListener('resize', closeActionMenu);
            document.addEventListener('scroll', closeActionMenu, true);

            // ── Parent Topic picker (Tom Select over every chapter) ────────────
            document.querySelectorAll('.parent-topic-picker').forEach(picker => {
                const select = picker.querySelector('.parent-topic-select');
                const searchUrl = picker.dataset.searchUrl;
                const fieldName = picker.dataset.fieldName || 'parent_topic_id';

                if (!select) return;

                const control = new TomSelect(select, {
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    maxItems: 1,
                    maxOptions: 20,
                    loadThrottle: 300,
                    // The modal body scrolls, so the dropdown hangs off <body>
                    // rather than being clipped by it.
                    dropdownParent: 'body',
                    onInitialize() { this.input.name = fieldName; },
                    load(query, callback) {
                        const url = new URL(searchUrl, window.location.origin);
                        url.searchParams.set('q', query);
                        if (picker.dataset.excludeUuid) {
                            url.searchParams.set('exclude', picker.dataset.excludeUuid);
                        }

                        fetch(url, {
                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                        })
                            .then(response => response.json())
                            .then(callback)
                            .catch(() => callback());
                    },
                    render: {
                        // The chapter tag is the point of this picker: two
                        // chapters often name a topic the same way.
                        option: (data, escape) => `<div class="py-2 px-3">
                                <div class="text-sm text-on-surface dark:text-slate-200">${escape(data.text)}</div>
                                ${data.meta ? `<div class="text-[11px] text-primary mt-0.5"><i class="fa-solid fa-book-bookmark text-[9px] mr-1"></i>${escape(data.meta)}</div>` : ''}
                            </div>`,
                        item: (data, escape) => `<div>${escape(data.text)}${data.meta ? ` <span class="text-[11px] text-on-surface-variant">— ${escape(data.meta)}</span>` : ''}</div>`,
                    },
                });

                // Editing: pre-select the saved parent, and keep the topic from
                // being offered as its own parent.
                picker.setParent = (parent, ownUuid) => {
                    picker.dataset.excludeUuid = ownUuid || '';
                    control.clear(true);
                    control.clearOptions();
                    control.loadedSearches = {};

                    if (parent) {
                        control.addOption({ id: parent.id, text: parent.text, meta: parent.meta });
                        control.addItem(parent.id, true);
                    }
                };
            });
            // ── Question Widget (Tom Select search + queued new questions) ─────
            document.querySelectorAll('.question-widget').forEach(widget => {
                const fieldName = widget.dataset.fieldName || 'question_ids';
                const newFieldName = widget.dataset.newFieldName || 'new_questions';
                const searchUrl = widget.dataset.searchUrl;

                const select = widget.querySelector('.question-widget-select');
                const newInput = widget.querySelector('.question-widget-new-input');
                const newType = widget.querySelector('.question-widget-new-type');
                const addNewBtn = widget.querySelector('.question-widget-add-new');
                const optionsBlock = widget.querySelector('.question-widget-new-options');
                const optionList = widget.querySelector('.question-widget-option-list');
                const optionAdd = widget.querySelector('.question-widget-option-add');
                const newWrap = widget.querySelector('.question-widget-new-wrap');
                const list = widget.querySelector('.question-widget-list');
                const count = widget.querySelector('.question-widget-count');
                const clearBtn = widget.querySelector('.question-widget-clear');
                // Optional past-paper citation, only rendered where the host
                // page asked for it.
                const paperToggle = widget.querySelector('.question-widget-paper-toggle');
                const paperBlock = widget.querySelector('.question-widget-paper');

                // ── Existing questions: type-ahead against the question bank ──
                const picker = new TomSelect(select, {
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    plugins: ['remove_button'],
                    maxOptions: 20,
                    loadThrottle: 300,
                    hideSelected: true,
                    // The widget panel scrolls, so the dropdown hangs off <body>
                    // rather than being clipped by it.
                    dropdownParent: 'body',
                    // Values post as question_ids[] alongside the rest of the form.
                    onInitialize() { this.input.name = fieldName + '[]'; },
                    load(query, callback) {
                        // Built through URL so a search url that already carries
                        // params (e.g. exclude_type/exclude_id) keeps them.
                        const url = new URL(searchUrl, window.location.origin);
                        url.searchParams.set('q', query);

                        fetch(url, {
                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                        })
                            .then(response => response.json())
                            .then(callback)
                            .catch(() => callback());
                    },
                    render: {
                        option: (data, escape) => `<div class="py-2 px-3">
                                <div class="text-sm text-on-surface dark:text-slate-200">${escape(data.text)}</div>
                                ${data.meta ? `<div class="text-[11px] text-on-surface-variant mt-0.5">${escape(data.meta)}</div>` : ''}
                            </div>`,
                        item: (data, escape) => `<div>${escape(data.text)}</div>`,
                        no_results: () => '<div class="py-2 px-3 text-sm text-on-surface-variant">No questions found.</div>',
                    },
                });

                // Tom Select caches results per search term. Call this when the
                // set of eligible questions changes server-side (e.g. one was
                // detached) so the next identical search re-queries.
                widget.refreshQuestionSearch = () => {
                    picker.loadedSearches = {};
                };

                // Pre-selecting from JS (e.g. an edit modal) needs the labels too.
                widget.setQuestions = (questions) => {
                    picker.clear(true);
                    picker.clearOptions();
                    (questions || []).forEach(question => {
                        picker.addOption({ id: question.id, text: question.text });
                        picker.addItem(question.id, true);
                    });
                    picker.refreshItems();
                };

                // A widget can be picker-only (no "write a new question" half),
                // as on the flashcard builder — everything below no-ops then.
                const canWriteNew = Boolean(list && newWrap && newInput);

                function refreshNewList() {
                    if (!canWriteNew) return;

                    const total = list.children.length;
                    newWrap.classList.toggle('hidden', total === 0);
                    newWrap.classList.toggle('flex', total > 0);
                    count.textContent = `(${total})`;
                }

                /* ── Composing a new question ───────────────────────────────── */

                const isMcqSelected = () => newType?.selectedOptions[0]?.dataset.type === 'mcqs';

                // The options block only applies to MCQs.
                function applyComposerType() {
                    if (!canWriteNew || !optionsBlock) return;

                    const isMcq = isMcqSelected();
                    optionsBlock.classList.toggle('hidden', !isMcq);
                    optionsBlock.classList.toggle('flex', isMcq);

                    if (isMcq) {
                        while (optionList.children.length < 2) addComposerOption();
                    }
                }

                function addComposerOption() {
                    const item = document.createElement('li');
                    item.className = 'flex items-center gap-2';
                    item.innerHTML = `
                        <input type="radio" class="w-4 h-4 text-primary border-outline-variant focus:ring-primary shrink-0" title="Correct answer">
                        <input type="text" class="flex-1 bg-white dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none" placeholder="Option text…">
                        <button type="button" class="option-remove w-6 h-6 flex items-center justify-center rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors shrink-0">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>`;
                    optionList.appendChild(item);

                    // Radios need a shared name to behave as one group.
                    const group = optionList.dataset.group ??= `qw-${Math.random().toString(36).slice(2)}`;
                    optionList.querySelectorAll('input[type="radio"]').forEach(radio => { radio.name = group; });
                }

                function resetComposer() {
                    newInput.value = '';
                    optionList?.replaceChildren();

                    if (paperToggle) {
                        paperToggle.checked = false;
                        applyPaperVisibility();
                        widget.querySelectorAll('.question-widget-paper input').forEach(i => { i.value = ''; });
                    }

                    applyComposerType();
                }

                function applyPaperVisibility() {
                    if (!paperBlock) return;
                    const on = Boolean(paperToggle?.checked);
                    paperBlock.classList.toggle('hidden', !on);
                    paperBlock.classList.toggle('flex', on);
                }

                paperToggle?.addEventListener('change', applyPaperVisibility);

                optionAdd?.addEventListener('click', () => addComposerOption());

                optionList?.addEventListener('click', (e) => {
                    if (!e.target.closest('.option-remove')) return;
                    e.target.closest('li').remove();
                    while (optionList.children.length < 2) addComposerOption();
                });

                newType?.addEventListener('change', applyComposerType);

                /* ── Queueing it ────────────────────────────────────────────── */

                function addNewQuestion(text, typeId, typeLabel, options, correctIndex, paper) {
                    const li = document.createElement('li');
                    li.className = 'flex items-start justify-between gap-3 bg-white dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700 rounded-lg px-3 py-2';

                    const left = document.createElement('div');
                    left.className = 'flex flex-col gap-1 min-w-0';

                    const heading = document.createElement('div');
                    heading.className = 'flex items-start gap-2 min-w-0';
                    heading.innerHTML = `
                        <span class="shrink-0 mt-0.5 text-[10px] font-semibold uppercase tracking-wide px-1.5 py-0.5 rounded bg-tertiary/10 text-tertiary">${typeLabel}</span>
                        <span class="text-sm text-on-surface dark:text-slate-200 break-words"></span>`;
                    heading.querySelector('span:last-child').textContent = text;
                    left.appendChild(heading);

                    if (options.length) {
                        const optionLine = document.createElement('ul');
                        optionLine.className = 'flex flex-col gap-0.5 pl-1';
                        options.forEach((option, index) => {
                            const row = document.createElement('li');
                            row.className = 'text-[11px] text-on-surface-variant dark:text-slate-400 flex items-center gap-1.5';
                            row.innerHTML = `<i class="fa-${index === correctIndex ? 'solid fa-circle-check text-tertiary' : 'regular fa-circle'} text-[9px]"></i>`;
                            row.appendChild(document.createTextNode(option));
                            optionLine.appendChild(row);
                        });
                        left.appendChild(optionLine);
                    }

                    if (paper) {
                        const cite = document.createElement('div');
                        cite.className = 'text-[11px] text-primary flex items-center gap-1.5 pl-1';
                        cite.innerHTML = '<i class="fa-solid fa-file-lines text-[9px]"></i>';
                        cite.appendChild(document.createTextNode(
                            ['Paper ' + paper.paper_no, paper.date, paper.marks + ' marks', paper.source]
                                .filter(Boolean).join(' \u00b7 ')
                        ));
                        left.appendChild(cite);
                    }

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'question-widget-remove shrink-0 text-on-surface-variant hover:text-error transition-colors';
                    removeBtn.innerHTML = '<i class="fa-solid fa-xmark text-sm"></i>';

                    // Hidden inputs carry the whole question; names are set by
                    // renumber() so the indexes stay contiguous.
                    const fields = document.createElement('span');
                    fields.className = 'question-widget-fields';
                    fields.dataset.question = text;
                    fields.dataset.type = typeId;
                    fields.dataset.options = JSON.stringify(options);
                    fields.dataset.correct = correctIndex ?? '';
                    fields.dataset.paper = paper ? JSON.stringify(paper) : '';

                    li.append(left, removeBtn, fields);
                    list.appendChild(li);
                    renumberNewQuestions();
                }

                // Rebuilds the hidden inputs so every queued question posts as
                // new_questions[i][…] with no gaps.
                function renumberNewQuestions() {
                    if (!canWriteNew) return;

                    list.querySelectorAll('.question-widget-fields').forEach((fields, index) => {
                        const prefix = `${newFieldName}[${index}]`;
                        const options = JSON.parse(fields.dataset.options || '[]');
                        const correct = fields.dataset.correct;

                        fields.replaceChildren();
                        fields.appendChild(hiddenField(`${prefix}[question]`, fields.dataset.question));
                        fields.appendChild(hiddenField(`${prefix}[type]`, fields.dataset.type));

                        // The citation rides along with its question, so the
                        // server can tie it to the link row it creates.
                        if (fields.dataset.paper) {
                            const paper = JSON.parse(fields.dataset.paper);
                            Object.entries(paper).forEach(([key, value]) => {
                                if (value !== '' && value !== null) {
                                    fields.appendChild(hiddenField(`${prefix}[past_paper][${key}]`, value));
                                }
                            });
                        }

                        options.forEach((option, position) => {
                            fields.appendChild(hiddenField(`${prefix}[options][${position}]`, option));
                        });

                        if (correct !== '') {
                            fields.appendChild(hiddenField(`${prefix}[correct_option]`, correct));
                        }
                    });

                    refreshNewList();
                }

                function hiddenField(name, value) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    return input;
                }

                function submitNewQuestion() {
                    const text = newInput.value.trim();
                    if (!text) return;

                    const typeId = newType.value;
                    const typeLabel = newType.selectedOptions[0]?.textContent.trim() ?? '';

                    let options = [];
                    let correctIndex = null;

                    if (isMcqSelected()) {
                        const rows = Array.from(optionList.querySelectorAll('li'));
                        rows.forEach(row => {
                            const value = row.querySelector('input[type="text"]').value.trim();
                            if (!value) return;
                            if (row.querySelector('input[type="radio"]').checked) correctIndex = options.length;
                            options.push(value);
                        });

                        if (options.length < 2) {
                            App.toast('warning', 'An MCQ needs at least two options.');
                            return;
                        }

                        if (correctIndex === null) {
                            App.toast('warning', 'Tick which option is the correct answer.');
                            return;
                        }
                    }

                    let paper = null;

                    if (paperToggle?.checked) {
                        paper = {
                            date: widget.querySelector('.question-widget-paper-date')?.value ?? '',
                            paper_no: widget.querySelector('.question-widget-paper-no')?.value.trim() ?? '',
                            marks: widget.querySelector('.question-widget-paper-marks')?.value ?? '',
                            source: widget.querySelector('.question-widget-paper-source')?.value.trim() ?? '',
                        };

                        // Source is the only optional part of a citation.
                        if (!paper.date || !paper.paper_no || paper.marks === '') {
                            App.toast('warning', 'A past paper reference needs a date, paper number and marks.');
                            return;
                        }
                    }

                    addNewQuestion(text, typeId, typeLabel, options, correctIndex, paper);
                    resetComposer();
                    newInput.focus();
                }

                addNewBtn?.addEventListener('click', submitNewQuestion);
                newInput?.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        submitNewQuestion();
                    }
                });

                list?.addEventListener('click', (e) => {
                    if (!e.target.closest('.question-widget-remove')) return;
                    e.target.closest('li').remove();
                    renumberNewQuestions();
                });

                clearBtn?.addEventListener('click', () => {
                    list.replaceChildren();
                    renumberNewQuestions();
                });

                // Lets a modal wipe the widget between openings.
                widget.resetQuestions = () => {
                    picker.clear(true);
                    picker.clearOptions();
                    if (canWriteNew) {
                        list.replaceChildren();
                        resetComposer();
                    }
                    refreshNewList();
                };

                applyComposerType();
                refreshNewList();
            });
        });
    </script>

    {{-- jQuery + DataTables (server-side grids), then SweetAlert2 and the shared
         AJAX helpers: App.dataTable / App.toast / App.request / … --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="{{ asset('cdn/sweet-alert/sweetAlert2.min.js') }}"></script>
    <script src="{{ asset('cdn/tom-select/tomSelect.min.js') }}"></script>
    <script src="{{ asset('js/app-ajax.js') }}"></script>

    {{-- Quill rich text editor (self-hosted) --}}
    <script src="{{ asset('cdn/quill/quill.js') }}"></script>
    <script>
        // Upgrades every <textarea data-quill> into a Quill editor. The original
        // textarea stays in the form (hidden) and receives the editor's HTML, so
        // nothing about how these forms submit has to change.
        document.addEventListener('DOMContentLoaded', () => {
            const Delta = Quill.import('delta');

            const toolbarFor = (allowAttachments) => [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ script: 'sub' }, { script: 'super' }],
                ['blockquote', 'code-block'],
                allowAttachments ? ['link', 'image'] : ['link'],
                ['clean'],
            ];

            document.querySelectorAll('textarea[data-quill]').forEach(textarea => {
                const wrapper = document.createElement('div');
                wrapper.className = 'quill-wrapper';
                if (textarea.dataset.quillHeight) {
                    wrapper.style.setProperty('--quill-min-height', textarea.dataset.quillHeight);
                }

                const editor = document.createElement('div');
                wrapper.appendChild(editor);
                textarea.parentNode.insertBefore(wrapper, textarea);

                // `required` on a hidden field makes the browser refuse to submit with
                // an unfocusable-control error, so enforce it ourselves instead.
                const isRequired = textarea.hasAttribute('required');
                textarea.removeAttribute('required');
                textarea.classList.add('hidden');

                let error = null;
                if (isRequired) {
                    error = document.createElement('p');
                    error.className = 'quill-error';
                    error.textContent = 'This field is required.';
                    wrapper.insertAdjacentElement('afterend', error);
                }

                // Fields marked data-quill-no-attachments lose the image button, and
                // reject images arriving by paste or drag-and-drop as well.
                const allowAttachments = !textarea.hasAttribute('data-quill-no-attachments');

                const quill = new Quill(editor, {
                    theme: 'snow',
                    placeholder: textarea.getAttribute('placeholder') || '',
                    modules: { toolbar: toolbarFor(allowAttachments) },
                });

                if (!allowAttachments) {
                    quill.clipboard.addMatcher('IMG', () => new Delta());
                    quill.root.addEventListener('drop', (e) => {
                        if (e.dataTransfer?.files?.length) e.preventDefault();
                    });
                }

                if (textarea.value.trim() !== '') {
                    quill.clipboard.dangerouslyPasteHTML(textarea.value);
                }

                const isEmpty = () => quill.getText().trim() === '' && !quill.root.querySelector('img');
                const sync = () => { textarea.value = isEmpty() ? '' : quill.root.innerHTML; };

                quill.on('text-change', () => {
                    sync();
                    if (!isEmpty()) wrapper.classList.remove('is-invalid');
                });
                sync();

                // Handles for pages that need to drive the editor — e.g. a modal that
                // is reused across table rows and has to swap its content on open.
                textarea.quillInstance = quill;
                textarea.setQuillContent = (html) => {
                    if (html && html.trim() !== '') {
                        quill.clipboard.dangerouslyPasteHTML(html);
                    } else {
                        quill.setText('');
                    }
                    wrapper.classList.remove('is-invalid');
                    sync();
                };

                textarea.form?.addEventListener('submit', (e) => {
                    sync();
                    if (isRequired && isEmpty()) {
                        e.preventDefault();
                        wrapper.classList.add('is-invalid');
                        quill.focus();
                    }
                });
            });
        });
    </script>

    {{-- Arriving from a search hit: the listing is already filtered down to the
         record, so open it as soon as its row is drawn. Resources managed in a
         modal have no page of their own, so this stands in for one. --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const target = new URLSearchParams(window.location.search).get('open');
            if (!target) return;

            const started = Date.now();

            function tryOpen() {
                // The edit trigger carries the record's uuid.
                const trigger = document.querySelector(`[onclick^="openEdit"][data-id="${CSS.escape(target)}"]`);

                if (trigger) {
                    trigger.click();
                    // Leave the URL clean so a refresh does not reopen it.
                    const url = new URL(window.location.href);
                    url.searchParams.delete('open');
                    window.history.replaceState({}, '', url);
                    return;
                }

                // The table draws over AJAX, so wait for the row — briefly.
                if (Date.now() - started < 6000) setTimeout(tryOpen, 150);
            }

            tryOpen();
        });
    </script>

    @stack('scripts')


</body>
</html>
