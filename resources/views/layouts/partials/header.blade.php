{{-- BEGIN: Top Header --}}
<header class="relative z-40 flex justify-between items-center gap-3 px-4 py-4 sm:px-8 sm:py-6 lg:px-12 flex-shrink-0">

    <div class="flex items-center gap-3 sm:gap-4 min-w-0">
        {{-- Mobile menu toggle --}}
        <button id="mobileSidebarToggle"
            class="lg:hidden shrink-0 w-9 h-9 sm:w-10 sm:h-10 bg-surface-container-lowest dark:bg-slate-800 rounded-full flex items-center justify-center text-on-surface-variant dark:text-slate-300 shadow-sm border dark:border-slate-700 hover:text-primary hover:scale-105 active:scale-95 transition-all">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="min-w-0">
            <h2 class="text-lg sm:text-2xl font-semibold text-on-background dark:text-white tracking-tight truncate">
                @yield('page-title', 'Dashboard')
            </h2>
            <p class="text-xs sm:text-sm text-on-surface-variant dark:text-slate-400 truncate">
                @yield('page-subtitle', 'Here\'s what\'s happening with your platform today.')
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2 sm:gap-3 shrink-0">

        {{-- Theme Toggle --}}
        <button id="themeToggle"
            class="w-9 h-9 sm:w-10 sm:h-10 bg-surface-container-lowest dark:bg-slate-800 rounded-full flex items-center justify-center text-on-surface-variant dark:text-slate-300 shadow-sm hover:text-primary hover:scale-105 active:scale-95 dark:hover:text-white transition-all border dark:border-slate-700"
            title="Toggle Theme">
            <i class="fa-solid fa-moon dark:hidden"></i>
            <i class="fa-solid fa-sun hidden dark:block text-primary-fixed-dim"></i>
        </button>

        {{-- Search --}}
        <button id="searchTrigger" type="button"
            class="flex w-9 h-9 sm:w-10 sm:h-10 bg-surface-container-lowest dark:bg-slate-800 rounded-full items-center justify-center text-on-surface-variant dark:text-slate-300 shadow-sm hover:text-primary hover:scale-105 active:scale-95 dark:hover:text-white transition-all border dark:border-slate-700"
            title="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>

        {{-- Notifications. The dot here used to be painted on whether or
             not anything had happened; it is a real count now. --}}
        <x-notification-bell />

        {{-- Who is signed in. This was hardcoded to "Admin User" with an avatar
             generated for that literal name, so every account — students
             included — saw somebody else's details here. --}}
        @php
            $me = auth()->user();
            $initials = \Illuminate\Support\Str::of($me?->name ?? '')
                ->explode(' ')
                ->take(2)
                ->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))
                ->implode('');
            $role = $me?->roles->pluck('name')->implode(', ');
        @endphp

        @guest
            <script>
                window.location.replace("{{ route('student.login') }}");
            </script>
        @endguest

        <div class="relative" id="userMenuDropdown">
            <button type="button" id="userMenuTrigger" aria-haspopup="true" aria-expanded="false"
                class="flex items-center gap-2.5 sm:gap-3 pl-2 sm:pl-3 border-l border-outline-variant/30 dark:border-slate-700 ml-1 group focus:outline-none cursor-pointer"
                title="{{ $me?->email }}">
                <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-full shadow-sm border-2 border-surface-container-lowest dark:border-slate-800 bg-primary text-on-primary flex items-center justify-center text-sm font-bold uppercase group-hover:opacity-90 group-hover:scale-105 active:scale-95 transition-all">
                    {{ $initials ?: '?' }}
                </span>
                <div class="hidden md:block min-w-0 text-left">
                    <p class="text-sm font-semibold text-on-background dark:text-white leading-tight truncate max-w-[10rem]">
                        {{ $me?->name ?? 'Signed out' }}
                    </p>
                    <p class="text-xs text-on-surface-variant dark:text-slate-400 truncate max-w-[10rem]">
                        {{ $role ?: $me?->email }}
                    </p>
                </div>
                <i id="userMenuChevron" class="fa-solid fa-chevron-down text-[10px] text-on-surface-variant/70 dark:text-slate-400 transition-transform duration-200 group-hover:text-primary"></i>
            </button>

            {{-- Dropdown Panel --}}
            <div id="userMenuPanel"
                class="hidden absolute right-0 mt-2 w-52 rounded-2xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 shadow-2xl z-50 overflow-hidden py-1 transition-all">

                {{-- User Info Header --}}
                <div class="px-4 py-3 border-b border-outline-variant/20 dark:border-slate-700 bg-surface-container-low/40 dark:bg-slate-900/40">
                    <p class="text-sm font-bold text-on-surface dark:text-white truncate">
                        {{ $me?->name ?? 'Signed out' }}
                    </p>
                    <p class="text-xs text-on-surface-variant dark:text-slate-400 truncate">
                        {{ $me?->email }}
                    </p>
                </div>

                {{-- Options --}}
                <div class="py-1">
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-on-surface dark:text-slate-200 hover:bg-primary/5 dark:hover:bg-slate-700/50 hover:text-primary dark:hover:text-white transition-colors">
                        <i class="fa-solid fa-gear text-sm text-outline-variant dark:text-slate-400 w-4 text-center"></i>
                        <span>Settings</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-error dark:text-red-400 hover:bg-error/10 dark:hover:bg-red-500/10 transition-colors text-left cursor-pointer">
                            <i class="fa-solid fa-power-off text-sm text-error dark:text-red-400 w-4 text-center"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>
{{-- END: Top Header --}}

<script>
    (function () {
        function initUserMenu() {
            const dropdown = document.getElementById('userMenuDropdown');
            if (!dropdown || dropdown.dataset.initialized) return;
            dropdown.dataset.initialized = 'true';

            const trigger = document.getElementById('userMenuTrigger');
            const panel = document.getElementById('userMenuPanel');
            const chevron = document.getElementById('userMenuChevron');

            function openMenu() {
                panel.classList.remove('hidden');
                trigger.setAttribute('aria-expanded', 'true');
                if (chevron) chevron.classList.add('rotate-180');
            }

            function closeMenu() {
                panel.classList.add('hidden');
                trigger.setAttribute('aria-expanded', 'false');
                if (chevron) chevron.classList.remove('rotate-180');
            }

            trigger?.addEventListener('click', function (e) {
                e.stopPropagation();
                panel.classList.contains('hidden') ? openMenu() : closeMenu();
            });

            document.addEventListener('click', function (e) {
                if (!dropdown.contains(e.target)) closeMenu();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeMenu();
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initUserMenu);
        } else {
            initUserMenu();
        }
    })();
</script>
