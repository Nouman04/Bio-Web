@extends('public.layouts.app')

@section('title', 'Disclaimer | Your Biology')

@push('styles')
<style>
body {
            background-color: theme('colors.background');
            color: theme('colors.on-background');
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 19, 48, 0.05);
        }
        .content-card {
            background-color: #ffffff;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 19, 48, 0.05);
        }
</style>
@endpush

@section('content')
<main class="flex-grow flex items-center justify-center py-xl px-md md:px-lg relative overflow-hidden">
<!-- Decorative Background Elements -->
<div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
<div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-primary-fixed-dim/20 blur-[100px]"></div>
<div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-secondary-container/30 blur-[120px]"></div>
</div>
<div class="w-full max-w-[800px] mx-auto content-card rounded-xl p-[40px] md:p-[64px] relative z-10">
<div class="text-center mb-xl">
<div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-surface-tint/10 text-primary mb-md">
<span class="material-symbols-outlined text-[32px]" data-icon="gavel" style="font-variation-settings: 'FILL' 1;">gavel</span>
</div>
<h1 class="font-headline-lg text-headline-lg md:font-display-lg md:text-display-lg text-on-surface mb-sm">DISCLAIMER</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant">Last updated: October 2024</p>
</div>
<div class="space-y-lg text-on-surface-variant">
<section class="flex gap-md items-start">
<span class="material-symbols-outlined text-primary mt-1" data-icon="info">info</span>
<div>
<h2 class="font-headline-md text-headline-md text-on-surface mb-sm">Educational Purposes Only</h2>
<p class="font-body-md text-body-md leading-relaxed">
                            The information and resources on FATBIO are provided for educational purposes only.
                        </p>
</div>
</section>
<hr class="border-outline-variant/30"/>
<section class="flex gap-md items-start">
<span class="material-symbols-outlined text-primary mt-1" data-icon="verified_user">verified_user</span>
<div>
<h2 class="font-headline-md text-headline-md text-on-surface mb-sm">Accuracy of Content</h2>
<p class="font-body-md text-body-md leading-relaxed">
                            We aim to keep our content accurate and aligned with the Cambridge IGCSE Biology syllabus, but we cannot guarantee that all information will always be complete, accurate, or up to date.
                        </p>
</div>
</section>
<hr class="border-outline-variant/30"/>
<section class="flex gap-md items-start">
<span class="material-symbols-outlined text-primary mt-1" data-icon="account_balance">account_balance</span>
<div>
<h2 class="font-headline-md text-headline-md text-on-surface mb-sm">Independence</h2>
<p class="font-body-md text-body-md leading-relaxed">
                            FATBIO is an independent educational website and is not affiliated with, endorsed by, or sponsored by Cambridge International.
                        </p>
</div>
</section>
<hr class="border-outline-variant/30"/>
<section class="flex gap-md items-start">
<span class="material-symbols-outlined text-primary mt-1" data-icon="menu_book">menu_book</span>
<div>
<h2 class="font-headline-md text-headline-md text-on-surface mb-sm">Authoritative Sources</h2>
<p class="font-body-md text-body-md leading-relaxed">
                            Students should always refer to the latest official Cambridge syllabus and examination materials for authoritative information.
                        </p>
</div>
</section>
<hr class="border-outline-variant/30"/>
<section class="flex gap-md items-start">
<span class="material-symbols-outlined text-primary mt-1" data-icon="rate_review">rate_review</span>
<div>
<h2 class="font-headline-md text-headline-md text-on-surface mb-sm">Feedback and Corrections</h2>
<p class="font-body-md text-body-md leading-relaxed">
                            If you notice an error or outdated information, please contact us so we can review it.
                        </p>
<button class="mt-md px-lg py-sm rounded-full font-label-md text-label-md text-primary border border-primary hover:bg-primary/5 transition-colors duration-200 active:scale-[0.98]">
                            Contact Support
                        </button>
</div>
</section>
</div>
</div>
</main>
@endsection
