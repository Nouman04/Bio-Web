{{-- Public site footer — the home mockup's footer, carrying the site's own
     link set: contact, FAQ, disclaimer, privacy policy, terms. --}}
@php
    $footerLinks = [
        ['label' => 'Contact Us', 'route' => 'public.contact'],
        ['label' => 'FAQ', 'route' => 'public.faq'],
        ['label' => 'Disclaimer', 'route' => 'public.disclaimer'],
        ['label' => 'Privacy Policy', 'route' => 'public.privacy'],
        ['label' => 'Terms and Conditions', 'route' => 'public.terms'],
    ];
@endphp
<footer class="bg-white border-t border-glass-stroke pt-16 pb-8 relative z-10">
<div class="max-w-container-max mx-auto px-md md:px-lg">
<div class="grid grid-cols-1 md:grid-cols-12 gap-8 mb-12">
<!-- Brand Info -->
<div class="col-span-1 md:col-span-5">
<a class="font-headline-md text-headline-md font-bold text-primary flex items-center gap-2 mb-4" href="{{ route('public.home') }}">
<img src="{{ asset('images/logo.png') }}" alt="Lumina LMS"
    class="w-9 h-9 rounded-lg object-contain bg-white">
                        Lumina LMS
                    </a>
<p class="text-on-surface-variant text-body-md mb-6 max-w-sm">Empowering IGCSE students worldwide with premium biology learning resources.</p>
<div class="flex gap-4">
<a class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-white transition-colors" href="{{ route('public.contact') }}">
<span class="material-symbols-outlined text-[20px]">share</span>
</a>
<a class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-white transition-colors" href="{{ route('public.contact') }}">
<span class="material-symbols-outlined text-[20px]">mail</span>
</a>
</div>
</div>
<!-- Links Grid -->
<div class="col-span-1 md:col-span-7 grid grid-cols-2 sm:grid-cols-2 gap-8">
<div>
<h4 class="font-bold text-on-surface mb-4">Platform</h4>
<ul class="space-y-3">
<li class=""><a class="text-on-surface-variant hover:text-primary transition-colors text-sm" href="{{ route('public.home') }}">Home</a></li>
<li class=""><a class="text-on-surface-variant hover:text-primary transition-colors text-sm" href="{{ route('public.about') }}">About</a></li>
<li class=""><a class="text-on-surface-variant hover:text-primary transition-colors text-sm" href="{{ route('public.courses') }}">Courses</a></li>
<li class=""><a class="text-on-surface-variant hover:text-primary transition-colors text-sm" href="{{ route('student.login') }}">Login</a></li>
</ul>
</div>
<div>
<h4 class="font-bold text-on-surface mb-4">Support &amp; Legal</h4>
<ul class="space-y-3">
@foreach($footerLinks as $link)
    <li class=""><a class="text-on-surface-variant hover:text-primary transition-colors text-sm {{ request()->routeIs($link['route']) ? 'text-primary font-semibold' : '' }}" href="{{ route($link['route']) }}">{{ $link['label'] }}</a></li>
@endforeach
</ul>
</div>
</div>
</div>
<div class="border-t border-outline-variant/20 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
<p class="text-sm text-on-surface-variant">© {{ date('Y') }} Lumina LMS. All rights reserved.</p>
<div class="flex flex-wrap gap-x-6 gap-y-2 justify-center">
@foreach($footerLinks as $link)
    <a class="text-sm text-on-surface-variant hover:text-primary transition-colors" href="{{ route($link['route']) }}">{{ $link['label'] }}</a>
@endforeach
</div>
</div>
</div>
</footer>
