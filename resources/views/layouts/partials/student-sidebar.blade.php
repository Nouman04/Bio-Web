{{-- BEGIN: Student Sidebar --}}
@php
    $counts = $sidebarCounts ?? [];
@endphp

<aside id="sidebar"
    class="sidebar-rail fixed inset-y-0 left-0 -translate-x-full lg:static lg:translate-x-0 w-full lg:w-64 flex flex-col items-center lg:items-start py-6 rounded-none lg:rounded-r-3xl z-30 flex-shrink-0 shadow-2xl lg:shadow-md">

    {{-- Toggle Button (Desktop collapse). The choice is remembered. --}}
    <button id="sidebarToggle" type="button" aria-label="Collapse sidebar" aria-expanded="true"
        class="sidebar-toggle hidden lg:flex absolute -right-4 top-14 w-8 h-8 rounded-full items-center justify-center hover:scale-110 shadow-sm z-40 transition-all duration-200 cursor-pointer">
        <i class="fa-solid fa-chevron-left text-sm transition-transform duration-300"></i>
    </button>

    {{-- Mobile close button --}}
    <button id="mobileSidebarClose" type="button"
        onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); document.getElementById('sidebar').classList.remove('is-open'); var ov=document.getElementById('sidebarOverlay'); ov.classList.add('opacity-0'); setTimeout(function(){ ov.classList.add('hidden'); }, 400);"
        class="sidebar-close lg:hidden absolute right-5 top-5 w-10 h-10 flex items-center justify-center rounded-full hover:rotate-90 transition-all duration-300 z-40">
        <i class="fa-solid fa-xmark text-xl"></i>
    </button>

    {{-- Logo --}}
    <a href="{{ route('student.dashboard') }}"
        class="mobile-fade flex items-center w-full px-8 mb-6 center-on-collapse transition-all duration-300 justify-center lg:justify-start group">
        <img src="{{ asset('images/logo.png') }}" alt="Your Biology Exam Simplified"
            class="w-12 h-12 object-contain flex-shrink-0 group-hover:scale-105 transition-transform duration-300">
        <div class="block ml-4 hide-on-collapse">
            <span class="sidebar-brand font-bold text-xl">Your Biology</span>
            <p class="sidebar-brand-sub text-xs">Exam Simplified</p>
        </div>
    </a>

    {{-- Navigation --}}
    <nav class="sidebar-nav-scroll flex-1 w-full flex flex-col px-6 pb-2">

        <x-sidebar.group>
            <x-sidebar.item :href="route('student.dashboard')" icon="fa-solid fa-border-all"
                label="Dashboard" :active="request()->routeIs('student.dashboard')" />
        </x-sidebar.group>

        <x-sidebar.group label="Studying">
            <x-sidebar.item :href="route('student.courses')" icon="fa-solid fa-graduation-cap"
                label="My Courses"
                :active="request()->routeIs('student.courses*') || request()->routeIs('student.chapters*')" />

            {{-- Sitting quizzes had no way in from the nav until now. --}}
            <x-sidebar.item :href="route('student.quizzes')" icon="fa-solid fa-clipboard-question"
                label="My Quizzes" :active="request()->routeIs('student.quizzes*')"
                :badge="$counts['quizzes'] ?? null" />

            {{-- Practice papers the student builds for themselves. --}}
            <x-sidebar.item :href="route('student.worksheets')" icon="fa-solid fa-file-lines"
                label="Worksheets" :active="request()->routeIs('student.worksheets*')" />

            <x-sidebar.item :href="route('student.resources')" icon="fa-solid fa-bookmark"
                label="Saved" :active="request()->routeIs('student.resources*')"
                :badge="$counts['resources'] ?? null" />
        </x-sidebar.group>

        <x-sidebar.group label="Discover">
            <x-sidebar.item :href="route('student.catalog')" icon="fa-solid fa-compass"
                label="Catalog" :active="request()->routeIs('student.catalog*')" />
        </x-sidebar.group>

    </nav>

    {{-- Who is signed in, and the way out --}}
    <div class="mobile-fade mt-auto px-6 w-full pt-3">
        <div class="sidebar-divider pt-3">
            <div class="sidebar-user flex items-center gap-3 px-3 py-2 rounded-2xl center-on-collapse justify-center lg:justify-start">
                <span class="sidebar-avatar w-9 h-9 shrink-0 rounded-full flex items-center justify-center text-xs font-bold uppercase">
                    {{ Str::of(auth()->user()?->name ?? '')->explode(' ')->take(2)->map(fn ($part) => Str::substr($part, 0, 1))->implode('') ?: '?' }}
                </span>
                <div class="hide-on-collapse min-w-0 flex-1">
                    <p class="sidebar-user-name text-sm font-semibold truncate">{{ auth()->user()?->name }}</p>
                    <p class="sidebar-user-sub text-[11px] truncate">{{ auth()->user()?->email }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" data-tip="Logout"
                    class="sidebar-logout w-full flex items-center justify-center lg:justify-start px-4 py-2.5 rounded-2xl center-on-collapse">
                    <i class="fa-solid fa-power-off text-lg w-6 text-center flex-shrink-0"></i>
                    <span class="block ml-4 hide-on-collapse text-sm font-medium">Logout</span>
                </button>
            </form>
        </div>
    </div>

</aside>
{{-- END: Student Sidebar --}}
