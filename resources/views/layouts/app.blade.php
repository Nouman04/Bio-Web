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

    {{-- Global Search --}}
    @include('partials.search-modal', [
        'searchPlaceholder' => 'Search modules, content, or students…',
        'searchLinks' => [
            ['label' => 'Dashboard', 'icon' => 'fa-solid fa-border-all'],
            ['label' => 'Courses', 'icon' => 'fa-solid fa-graduation-cap'],
            ['label' => 'Categories', 'icon' => 'fa-solid fa-book-open'],
            ['label' => 'Topics', 'icon' => 'fa-solid fa-tags'],
            ['label' => 'Quizzes', 'icon' => 'fa-solid fa-clipboard-question'],
            ['label' => 'Questions', 'icon' => 'fa-regular fa-circle-question'],
            ['label' => 'Flashcards', 'icon' => 'fa-solid fa-layer-group'],
            ['label' => 'Video Lessons', 'icon' => 'fa-solid fa-circle-play'],
            ['label' => 'Notes', 'icon' => 'fa-regular fa-note-sticky'],
            ['label' => 'Guides', 'icon' => 'fa-solid fa-book-open'],
            ['label' => 'Diagrams', 'icon' => 'fa-regular fa-image'],
            ['label' => 'Summaries', 'icon' => 'fa-solid fa-list-check'],
            ['label' => 'Students', 'icon' => 'fa-solid fa-users'],
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
            document.querySelectorAll('.action-dropdown').forEach(dropdown => {
                const trigger = dropdown.querySelector('.action-dropdown-trigger');
                const menu = dropdown.querySelector('.action-dropdown-menu');

                trigger?.addEventListener('click', (e) => {
                    e.stopPropagation();
                    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.add('hidden');
                    });
                    menu?.classList.toggle('hidden');
                });
            });

            document.addEventListener('click', () => {
                document.querySelectorAll('.action-dropdown-menu').forEach(menu => {
                    menu.classList.add('hidden');
                });
            });

            // ── Question Widget (multi-add existing or newly-written questions) ────
            document.querySelectorAll('.question-widget').forEach(widget => {
                const fieldName = widget.dataset.fieldName || 'question_ids';
                const newFieldName = widget.dataset.newFieldName || 'new_questions';
                const select = widget.querySelector('.question-widget-select');
                const addBtn = widget.querySelector('.question-widget-add');
                const newInput = widget.querySelector('.question-widget-new-input');
                const addNewBtn = widget.querySelector('.question-widget-add-new');
                const list = widget.querySelector('.question-widget-list');
                const emptyMsg = widget.querySelector('.question-widget-empty');
                const added = new Set();
                let newCounter = 0;

                function refreshEmpty() {
                    emptyMsg.classList.toggle('hidden', list.children.length > 0);
                }

                function buildRow(key, text, { isNew = false, name, value } = {}) {
                    const li = document.createElement('li');
                    li.className = 'flex items-center justify-between gap-3 bg-white dark:bg-slate-800 border border-outline-variant/40 dark:border-slate-700 rounded-lg px-3 py-2';
                    li.dataset.key = key;

                    const left = document.createElement('div');
                    left.className = 'flex items-center gap-2 min-w-0';

                    if (isNew) {
                        const badge = document.createElement('span');
                        badge.className = 'shrink-0 text-[10px] font-semibold uppercase tracking-wide px-1.5 py-0.5 rounded bg-tertiary/10 text-tertiary';
                        badge.textContent = 'New';
                        left.appendChild(badge);
                    }

                    const span = document.createElement('span');
                    span.className = 'text-sm text-on-surface dark:text-slate-200 line-clamp-1';
                    span.textContent = text;
                    left.appendChild(span);

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'question-widget-remove shrink-0 text-on-surface-variant hover:text-error transition-colors';
                    removeBtn.innerHTML = '<i class="fa-solid fa-xmark text-sm"></i>';

                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = name;
                    hidden.value = value;

                    li.appendChild(left);
                    li.appendChild(removeBtn);
                    li.appendChild(hidden);
                    list.appendChild(li);
                    refreshEmpty();
                }

                function addExisting(id, text) {
                    const key = 'existing:' + id;
                    if (added.has(key)) return;
                    added.add(key);
                    buildRow(key, text, { name: fieldName + '[]', value: id });
                }

                function addNew(text) {
                    const key = 'new:' + (++newCounter);
                    added.add(key);
                    buildRow(key, text, { isNew: true, name: newFieldName + '[]', value: text });
                }

                addBtn?.addEventListener('click', () => {
                    const opt = select.options[select.selectedIndex];
                    if (!opt || !opt.value) return;
                    addExisting(opt.value, opt.dataset.text || opt.textContent);
                    select.selectedIndex = 0;
                });

                function submitNewQuestion() {
                    const text = newInput.value.trim();
                    if (!text) return;
                    addNew(text);
                    newInput.value = '';
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
                    const btn = e.target.closest('.question-widget-remove');
                    if (!btn) return;
                    const li = btn.closest('li');
                    added.delete(li.dataset.key);
                    li.remove();
                    refreshEmpty();
                });

                refreshEmpty();
            });
        });
    </script>

    @stack('scripts')

</body>
</html>
