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
<a class="font-headline-md text-headline-md font-bold text-white hover:opacity-80 transition-opacity flex items-center gap-2" href="{{ route('public.home') }}">
<img src="{{ asset('images/logo.png') }}" alt="Your Biology"
    class="w-9 h-9 rounded-lg object-contain">
                Your Biology
            </a>
<!-- Navigation Links (Desktop) -->
<div class="hidden md:flex space-x-lg items-center">
@foreach($navLinks as $link)
    @if($link['active'])
        <a class="text-white font-label-md font-bold relative after:absolute after:bottom-[-24px] after:left-0 after:w-full after:h-[2px] after:bg-white transition-colors duration-200" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
    @else
        <a class="text-white/70 font-label-md hover:text-white transition-colors duration-200" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
    @endif
@endforeach
</div>
<!-- Actions -->
<div class="flex items-center space-x-md">
<a class="text-white/80 hover:text-white font-label-md transition-colors duration-200 hidden sm:block" href="{{ route('student.login') }}">Login</a>
<a class="bg-white text-[#001330] px-6 py-2.5 rounded-full font-label-md font-semibold hover:bg-primary-fixed transition-all duration-300 transform active:scale-95 shadow-lg hover:-translate-y-0.5" href="{{ route('student.login') }}">Get Started</a>
</div>
</div>
<!-- Navigation Links (Mobile) -->
<div class="md:hidden border-t border-white/10">
<div class="flex items-center justify-center gap-6 px-md py-3 max-w-container-max mx-auto">
@foreach($navLinks as $link)
    <a class="font-label-md {{ $link['active'] ? 'text-white font-bold' : 'text-white/70 hover:text-white' }} transition-colors duration-200" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
@endforeach
<a class="font-label-md text-white/70 hover:text-white transition-colors duration-200 sm:hidden" href="{{ route('student.login') }}">Login</a>
</div>
</div>
</nav>
