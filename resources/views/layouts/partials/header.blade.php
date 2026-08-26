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

        <a href="{{ route('profile.edit') }}"
            class="flex items-center gap-3 pl-2 sm:pl-3 border-l border-outline-variant/30 dark:border-slate-700 ml-1 group"
            title="{{ $me?->email }}">
            <span class="w-9 h-9 sm:w-10 sm:h-10 rounded-full shadow-sm border-2 border-surface-container-lowest dark:border-slate-800 bg-primary text-on-primary flex items-center justify-center text-sm font-bold uppercase group-hover:opacity-90 group-hover:scale-105 active:scale-95 transition-all">
                {{ $initials ?: '?' }}
            </span>
            <div class="hidden md:block min-w-0">
                <p class="text-sm font-semibold text-on-background dark:text-white leading-tight truncate max-w-[10rem]">
                    {{ $me?->name ?? 'Signed out' }}
                </p>
                <p class="text-xs text-on-surface-variant dark:text-slate-400 truncate max-w-[10rem]">
                    {{ $role ?: $me?->email }}
                </p>
            </div>
        </a>

    </div>
</header>
{{-- END: Top Header --}}
