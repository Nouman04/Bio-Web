{{-- BEGIN: Sidebar --}}
<aside id="sidebar"
    class="fixed inset-y-0 left-0 -translate-x-full lg:static lg:translate-x-0 w-full lg:w-64 bg-surface-container-lowest dark:bg-slate-950 lg:border-r lg:border-outline-variant/30 dark:lg:border-slate-800 flex flex-col items-center lg:items-start py-8 rounded-none lg:rounded-r-3xl z-30 flex-shrink-0 shadow-2xl lg:shadow-md dark:shadow-none">

    {{-- Toggle Button (Desktop collapse) --}}
    <button id="sidebarToggle"
        class="hidden lg:flex absolute -right-4 top-14 w-8 h-8 bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/50 dark:border-slate-700 rounded-full items-center justify-center text-on-surface-variant dark:text-slate-300 hover:text-primary hover:bg-surface-container-low dark:hover:bg-slate-700 shadow-sm z-30 transition-colors cursor-pointer">
        <i class="fa-solid fa-chevron-left text-sm transition-transform duration-300"></i>
    </button>

    {{-- Mobile close button --}}
    <button id="mobileSidebarClose" type="button"
        onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); document.getElementById('sidebar').classList.remove('is-open'); var ov=document.getElementById('sidebarOverlay'); ov.classList.add('opacity-0'); setTimeout(function(){ ov.classList.add('hidden'); }, 400);"
        class="lg:hidden absolute right-5 top-5 w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant dark:text-slate-300 hover:text-primary hover:bg-surface-container-low dark:hover:bg-slate-800 hover:rotate-90 transition-all duration-300 z-40">
        <i class="fa-solid fa-xmark text-xl"></i>
    </button>

    {{-- Logo --}}
    <div class="mobile-fade flex items-center w-full px-8 mb-12 center-on-collapse transition-all duration-300 justify-center lg:justify-start">
        <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center text-on-primary shadow-lg flex-shrink-0">
            <i class="fa-solid fa-graduation-cap text-xl"></i>
        </div>
        <div class="block ml-4 hide-on-collapse">
            <span class="font-bold text-xl text-primary dark:text-primary-fixed-dim">EduAdmin</span>
            <p class="text-xs text-on-surface-variant dark:text-slate-400">Management Suite</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 w-full flex flex-col justify-center lg:justify-start space-y-2 lg:space-y-1 px-6 overflow-y-auto">

        <a href="{{ route('dashboard') }}"
            class="sidebar-item {{ request()->routeIs('dashboard') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-solid fa-border-all text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Dashboard</span>
        </a>

        <a href="{{ route('courses') }}"
            class="sidebar-item {{ request()->routeIs('courses*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-solid fa-graduation-cap text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Courses</span>
        </a>

        <a href="{{ route('categories') }}"
            class="sidebar-item {{ request()->routeIs('categories*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-solid fa-book-open text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Categories</span>
        </a>

        <a href="{{ route('chapters') }}"
            class="sidebar-item {{ request()->routeIs('chapters*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-solid fa-book-bookmark text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Chapters</span>
        </a>

        <a href="{{ route('topics') }}"
            class="sidebar-item {{ request()->routeIs('topics*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-solid fa-tags text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Topics</span>
        </a>

        <a href="{{ route('videos') }}"
            class="sidebar-item {{ request()->routeIs('videos*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-solid fa-circle-play text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Video Lessons</span>
        </a>

        <a href="{{ route('questions') }}"
            class="sidebar-item {{ request()->routeIs('questions*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-regular fa-circle-question text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Questions</span>
        </a>

        <a href="{{ route('quizzes') }}"
            class="sidebar-item {{ request()->routeIs('quizzes*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-solid fa-clipboard-question text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Quizzes</span>
        </a>

        <a href="{{ route('flashcards') }}"
            class="sidebar-item {{ request()->routeIs('flashcards*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-solid fa-layer-group text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Flashcards</span>
        </a>

        <a href="{{ route('notes') }}"
            class="sidebar-item {{ request()->routeIs('notes*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-regular fa-note-sticky text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Study Notes</span>
        </a>

        <a href="{{ route('guides') }}"
            class="sidebar-item {{ request()->routeIs('guides*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-solid fa-book-open text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Guides</span>
        </a>

        <a href="{{ route('diagrams') }}"
            class="sidebar-item {{ request()->routeIs('diagrams*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-regular fa-image text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Diagrams</span>
        </a>

        <a href="{{ route('summaries') }}"
            class="sidebar-item {{ request()->routeIs('summaries*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-solid fa-list-check text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Summaries</span>
        </a>

        <a href="{{ route('students') }}"
            class="sidebar-item {{ request()->routeIs('students*') ? 'active bg-primary-container text-on-primary-container dark:bg-primary dark:text-white font-medium' : 'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' }} flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300">
            <i class="fa-solid fa-users text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Students</span>
        </a>

    </nav>

    {{-- Logout --}}
    <div class="mobile-fade mt-auto px-6 w-full pb-4">
        <a href="#"
            class="flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl text-on-surface-variant dark:text-slate-300 hover:bg-error-container hover:text-on-error-container dark:hover:bg-error-container/20 dark:hover:text-error transition-all duration-300 center-on-collapse">
            <i class="fa-solid fa-power-off text-xl w-6 text-center flex-shrink-0"></i>
            <span class="block ml-4 hide-on-collapse">Logout</span>
        </a>
    </div>

</aside>
{{-- END: Sidebar --}}
