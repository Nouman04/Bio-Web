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

        /* Sidebar collapse */
        #sidebar {
            transition: width 0.3s ease-in-out, background-color 0.3s ease, border-color 0.3s ease;
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

        /* Mobile sidebar overlay */
        #sidebarOverlay {
            transition: opacity 0.3s ease;
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(118,117,134,0.3); border-radius: 99px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(118,117,134,0.5); }
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
        <div class="flex-1 overflow-y-auto px-8 pb-8 lg:px-12 custom-scrollbar">
            @yield('content')
        </div>

    </main>

    {{-- Theme & Sidebar Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ── Theme Toggle ──────────────────────────────────────────
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

            // ── Desktop Sidebar Collapse ───────────────────────────────
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

            // ── Mobile Sidebar Toggle ──────────────────────────────────
            const mobileToggle = document.getElementById('mobileSidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');

            function openMobileSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                requestAnimationFrame(() => overlay.classList.remove('opacity-0'));
            }

            function closeMobileSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }

            mobileToggle?.addEventListener('click', openMobileSidebar);
            overlay?.addEventListener('click', closeMobileSidebar);
        });
    </script>

    @stack('scripts')

</body>
</html>
