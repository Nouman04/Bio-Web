{{-- Public site header — the home mockup's glass nav, with the site's own links.
     The active page is underlined the way the mockup marks the current one. --}}
@php
    $navLinks = [
        ['label' => 'Home', 'url' => route('public.home'), 'active' => request()->routeIs('public.home')],
        ['label' => 'About', 'url' => route('public.about'), 'active' => request()->routeIs('public.about')],
        ['label' => 'Courses', 'url' => route('public.courses'), 'active' => request()->routeIs('public.courses')],
    ];
@endphp
<nav class="glass-nav font-body-md text-body-md docked full-width top-0 sticky z-50 transition-all duration-300">
<div class="flex justify-between items-center w-full px-md md:px-lg max-w-container-max mx-auto h-[72px]">
<!-- Brand -->
<a class="font-headline-md text-headline-md font-bold text-primary dark:text-inverse-primary hover:opacity-80 transition-opacity flex items-center gap-2" href="{{ route('public.home') }}">
<img src="{{ asset('images/logo.png') }}" alt="Lumina LMS"
    class="w-9 h-9 rounded-lg object-contain shadow-sm bg-white">
                Lumina LMS
            </a>
<!-- Navigation Links (Desktop) -->
<div class="hidden md:flex space-x-lg items-center">
@foreach($navLinks as $link)
    @if($link['active'])
        <a class="text-primary dark:text-inverse-primary font-label-md font-bold relative after:absolute after:bottom-[-24px] after:left-0 after:w-full after:h-[2px] after:bg-primary transition-colors duration-200" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
    @else
        <a class="text-on-surface-variant dark:text-surface-variant font-label-md hover:text-primary dark:hover:text-inverse-primary transition-colors duration-200" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
    @endif
@endforeach
</div>
<!-- Actions -->
<div class="flex items-center space-x-md">
<a class="text-on-surface hover:text-primary font-label-md transition-colors duration-200 hidden sm:block" href="{{ route('student.login') }}">Login</a>
<a class="bg-primary text-on-primary px-6 py-2.5 rounded-full font-label-md hover:bg-primary-container transition-all duration-300 transform active:scale-95 shadow-[0_4px_14px_0_rgba(0, 19, 48, 0.39)] hover:shadow-[0_6px_20px_rgba(0, 19, 48, 0.23)] hover:-translate-y-0.5" href="{{ route('student.login') }}">Get Started</a>
</div>
</div>
<!-- Navigation Links (Mobile) -->
<div class="md:hidden border-t border-outline-variant/20 bg-white/60">
<div class="flex items-center justify-center gap-6 px-md py-3 max-w-container-max mx-auto">
@foreach($navLinks as $link)
    <a class="font-label-md {{ $link['active'] ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }} transition-colors duration-200" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
@endforeach
<a class="font-label-md text-on-surface-variant hover:text-primary transition-colors duration-200 sm:hidden" href="{{ route('student.login') }}">Login</a>
</div>
</div>
</nav>
