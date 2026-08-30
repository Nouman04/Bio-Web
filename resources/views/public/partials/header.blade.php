{{-- Public site header — the home mockup's glass nav, with the site's own links.
     The active page is underlined the way the mockup marks the current one.

     Everything in the top row is sized twice: the mockup's proportions from the
     md breakpoint up, and a tighter set below it. The logo used to be 80px tall
     inside a 72px bar at every width, which is what pushed the brand text out of
     the header on a phone. --}}
@php
    $navLinks = [
        ['label' => 'Home', 'url' => route('public.home'), 'active' => request()->routeIs('public.home')],
        ['label' => 'About', 'url' => route('public.about'), 'active' => request()->routeIs('public.about')],
        ['label' => 'Courses', 'url' => route('public.courses'), 'active' => request()->routeIs('public.courses')],
    ];
@endphp
<nav class="glass-nav font-body-md text-body-md docked full-width top-0 sticky z-50 transition-all duration-300">
<div class="flex justify-between items-center gap-3 w-full px-sm md:px-lg max-w-container-max mx-auto h-16 md:h-[72px]">
<!-- Brand -->
<a class="text-white hover:opacity-80 transition-opacity flex items-center gap-2.5 md:gap-3 min-w-0"
   href="{{ route('public.home') }}">

    <!-- Logo. Always shorter than the bar it sits in. -->
    <img src="{{ asset('images/logo.png') }}"
         alt="Your Biology"
         class="w-11 h-11 md:w-16 md:h-16 shrink-0 rounded-lg object-contain">

    <!-- Brand Text -->
    <div class="flex flex-col justify-center min-w-0">
        <span class="text-lg md:font-headline-md md:text-headline-md font-bold leading-tight truncate">
            Your Biology
        </span>

        {{-- Kept to one line: wrapped, it was tall enough to break the bar. --}}
        <span class="text-[11px] md:text-sm font-medium text-gray-300 leading-tight mt-0.5 md:mt-1 truncate">
            Examination Simplified
        </span>
    </div>

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
<div class="flex items-center gap-3 md:gap-md shrink-0">
<a class="text-white/80 hover:text-white font-label-md transition-colors duration-200 hidden sm:block" href="{{ route('student.login') }}">Login</a>
<a class="bg-white text-[#001330] px-4 py-2 text-sm md:px-6 md:py-2.5 md:font-label-md md:text-label-md rounded-full font-semibold whitespace-nowrap hover:bg-primary-fixed transition-all duration-300 transform active:scale-95 shadow-lg hover:-translate-y-0.5" href="{{ route('student.login') }}">Get Started</a>
</div>
</div>
<!-- Navigation Links (Mobile) -->
<div class="md:hidden border-t border-white/10">
<div class="flex items-center justify-center gap-5 px-sm py-2.5 max-w-container-max mx-auto">
@foreach($navLinks as $link)
    <a class="font-label-md {{ $link['active'] ? 'text-white font-bold' : 'text-white/70 hover:text-white' }} transition-colors duration-200" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
@endforeach
<a class="font-label-md text-white/70 hover:text-white transition-colors duration-200 sm:hidden" href="{{ route('student.login') }}">Login</a>
</div>
</div>
</nav>
