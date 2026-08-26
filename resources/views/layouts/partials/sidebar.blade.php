{{-- BEGIN: Sidebar --}}
@php
    $counts = $sidebarCounts ?? [];
@endphp

<aside id="sidebar"
    class="fixed inset-y-0 left-0 -translate-x-full lg:static lg:translate-x-0 w-full lg:w-64 bg-surface-container-lowest dark:bg-slate-950 lg:border-r lg:border-outline-variant/30 dark:lg:border-slate-800 flex flex-col items-center lg:items-start py-6 rounded-none lg:rounded-r-3xl z-30 flex-shrink-0 shadow-2xl lg:shadow-md dark:shadow-none">

    {{-- Toggle Button (Desktop collapse). The choice is remembered. --}}
    <button id="sidebarToggle" type="button" aria-label="Collapse sidebar" aria-expanded="true"
        class="hidden lg:flex absolute -right-4 top-14 w-8 h-8 bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/50 dark:border-slate-700 rounded-full items-center justify-center text-on-surface-variant dark:text-slate-300 hover:text-primary hover:bg-surface-container-low dark:hover:bg-slate-700 hover:scale-110 shadow-sm z-40 transition-all duration-200 cursor-pointer">
        <i class="fa-solid fa-chevron-left text-sm transition-transform duration-300"></i>
    </button>

    {{-- Mobile close button --}}
    <button id="mobileSidebarClose" type="button"
        onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); document.getElementById('sidebar').classList.remove('is-open'); var ov=document.getElementById('sidebarOverlay'); ov.classList.add('opacity-0'); setTimeout(function(){ ov.classList.add('hidden'); }, 400);"
        class="lg:hidden absolute right-5 top-5 w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant dark:text-slate-300 hover:text-primary hover:bg-surface-container-low dark:hover:bg-slate-800 hover:rotate-90 transition-all duration-300 z-40">
        <i class="fa-solid fa-xmark text-xl"></i>
    </button>

    {{-- Logo --}}
    <a href="{{ route('dashboard') }}"
        class="mobile-fade flex items-center w-full px-8 mb-6 center-on-collapse transition-all duration-300 justify-center lg:justify-start group">
        <img src="{{ asset('images/logo.png') }}" alt="EduAdmin"
            class="w-12 h-12 rounded-xl object-contain shadow-lg flex-shrink-0 bg-white group-hover:scale-105 transition-transform duration-300">
        <div class="block ml-4 hide-on-collapse">
            <span class="font-bold text-xl text-primary dark:text-primary-fixed-dim">EduAdmin</span>
            <p class="text-xs text-on-surface-variant dark:text-slate-400">Your Biology Exam Simplified</p>
        </div>
    </a>

    {{-- Navigation --}}
    <nav class="sidebar-nav-scroll flex-1 w-full flex flex-col px-6 pb-2">

        <x-sidebar.group>
            <x-sidebar.item :href="route('dashboard')" icon="fa-solid fa-border-all"
                label="Dashboard" :active="request()->routeIs('dashboard')" />

            <x-sidebar.item :href="route('students')" icon="fa-solid fa-users"
                label="Students" :active="request()->routeIs('students*')"
                :badge="$counts['students'] ?? null" urgent />
        </x-sidebar.group>

        <x-sidebar.group label="Content">
            @can('view course')
                <x-sidebar.item :href="route('courses')" icon="fa-solid fa-graduation-cap"
                    label="Courses" :active="request()->routeIs('courses*')" />
            @endcan

            <x-sidebar.item :href="route('questions')" icon="fa-regular fa-circle-question"
                label="Question Bank"
                :active="request()->routeIs('questions*', 'courses.chapters.questions*')" />
        </x-sidebar.group>

        <x-sidebar.group label="Assessment">
            <x-sidebar.item :href="route('quizzes')" icon="fa-solid fa-clipboard-question"
                label="Quizzes" :active="request()->routeIs('quizzes') || request()->routeIs('quizzes.show') || request()->routeIs('quizzes.create') || request()->routeIs('quizzes.edit')" />

            {{-- Marking had no way in from the nav until now. --}}
            <x-sidebar.item :href="route('quizzes.review')" icon="fa-solid fa-pen-to-square"
                label="Marking" :active="request()->routeIs('quizzes.review*')"
                :badge="$counts['review'] ?? null" urgent />
        </x-sidebar.group>

    </nav>

    {{-- Who is signed in, and the way out --}}
    <div class="mobile-fade mt-auto px-6 w-full pt-3">
        <div class="border-t border-outline-variant/30 dark:border-slate-800 pt-3">
            <div class="sidebar-user flex items-center gap-3 px-3 py-2 rounded-2xl center-on-collapse justify-center lg:justify-start">
                <span class="w-9 h-9 shrink-0 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold uppercase">
                    {{ Str::of(auth()->user()?->name ?? '')->explode(' ')->take(2)->map(fn ($part) => Str::substr($part, 0, 1))->implode('') ?: '?' }}
                </span>
                <div class="hide-on-collapse min-w-0 flex-1">
                    <p class="text-sm font-semibold text-on-surface dark:text-white truncate">{{ auth()->user()?->name }}</p>
                    <p class="text-[11px] text-on-surface-variant dark:text-slate-400 truncate">{{ auth()->user()?->roles->pluck('name')->implode(', ') ?: 'Staff' }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" data-tip="Logout"
                    class="sidebar-logout w-full flex items-center justify-center lg:justify-start px-4 py-2.5 rounded-2xl text-on-surface-variant dark:text-slate-300 hover:bg-error-container hover:text-on-error-container dark:hover:bg-error-container/20 dark:hover:text-error center-on-collapse">
                    <i class="fa-solid fa-power-off text-lg w-6 text-center flex-shrink-0"></i>
                    <span class="block ml-4 hide-on-collapse text-sm font-medium">Logout</span>
                </button>
            </form>
        </div>
    </div>

</aside>
{{-- END: Sidebar --}}
