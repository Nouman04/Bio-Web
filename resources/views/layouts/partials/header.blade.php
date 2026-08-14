{{-- BEGIN: Top Header --}}
<header class="flex justify-between items-center gap-3 px-4 py-4 sm:px-8 sm:py-6 lg:px-12 z-10 flex-shrink-0">

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

        {{-- Notifications --}}
        <button
            class="w-9 h-9 sm:w-10 sm:h-10 bg-surface-container-lowest dark:bg-slate-800 rounded-full flex items-center justify-center text-on-surface-variant dark:text-slate-300 shadow-sm hover:text-primary hover:scale-105 active:scale-95 dark:hover:text-white transition-all relative border dark:border-slate-700"
            title="Notifications">
            <i class="fa-regular fa-bell"></i>
            <span class="absolute top-2 right-2.5 w-2 h-2 bg-error rounded-full border border-surface-container-lowest dark:border-slate-800"></span>
        </button>

        {{-- User Avatar --}}
        <div class="flex items-center gap-3 pl-2 sm:pl-3 border-l border-outline-variant/30 dark:border-slate-700 ml-1">
            <img src="https://ui-avatars.com/api/?name=Admin+User&background=4648d4&color=fff&size=128"
                alt="User Avatar"
                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full shadow-sm border-2 border-surface-container-lowest dark:border-slate-800 cursor-pointer hover:opacity-90 hover:scale-105 active:scale-95 transition-all" />
            <div class="hidden md:block">
                <p class="text-sm font-semibold text-on-background dark:text-white leading-tight">Admin User</p>
                <p class="text-xs text-on-surface-variant dark:text-slate-400">Administrator</p>
            </div>
        </div>

    </div>
</header>
{{-- END: Top Header --}}
