@extends('public.layouts.app')

@section('title', 'Terms & Conditions | Lumina LMS')

@push('styles')
<style>
body { font-family: 'Geist', sans-serif; }
</style>
@endpush

@section('content')
<main class="flex-grow flex justify-center py-xl px-md md:px-lg relative">
<!-- Background subtle glow -->
<div class="absolute inset-0 overflow-hidden pointer-events-none">
<div class="absolute -top-[300px] -right-[300px] w-[600px] h-[600px] rounded-full bg-primary-fixed/20 blur-[100px]"></div>
<div class="absolute top-[40%] -left-[200px] w-[500px] h-[500px] rounded-full bg-secondary-fixed/30 blur-[120px]"></div>
</div>
<article class="w-full max-w-[800px] relative z-10 bg-surface-container-lowest rounded-xl p-lg shadow-sm border border-black/5">
<header class="mb-lg border-b border-outline-variant/30 pb-md">
<h1 class="font-display-lg text-display-lg text-on-surface mb-sm">TERMS &amp; CONDITIONS</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant">By using FATBIO, you agree to these terms.</p>
</header>
<section class="space-y-lg">
<div class="group">
<h2 class="font-headline-md text-headline-md text-primary mb-sm flex items-center gap-base">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">menu_book</span>
                        1. Use of Resources
                    </h2>
<p class="font-body-md text-body-md text-on-surface leading-relaxed">
                        Our resources are provided for personal, non-commercial educational use. You may not copy, sell, redistribute, or upload our content elsewhere without permission.
                    </p>
</div>
<div class="group">
<h2 class="font-headline-md text-headline-md text-primary mb-sm flex items-center gap-base">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">copyright</span>
                        2. Copyright
                    </h2>
<p class="font-body-md text-body-md text-on-surface leading-relaxed">
                        All original notes, diagrams, graphics, videos, and other content belong to FATBIO, unless otherwise stated.
                    </p>
</div>
<div class="group">
<h2 class="font-headline-md text-headline-md text-primary mb-sm flex items-center gap-base">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">warning</span>
                        3. Educational Disclaimer
                    </h2>
<p class="font-body-md text-body-md text-on-surface leading-relaxed">
                        Our resources are intended to support your studies and do not replace official Cambridge materials, your teacher, or the latest Cambridge syllabus.
                    </p>
</div>
<div class="group">
<h2 class="font-headline-md text-headline-md text-primary mb-sm flex items-center gap-base">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">hub</span>
                        4. Third-Party Services
                    </h2>
<p class="font-body-md text-body-md text-on-surface leading-relaxed">
                        We may use third-party services such as advertising, analytics, or external links. These services may have their own terms and privacy policies.
                    </p>
</div>
<div class="group">
<h2 class="font-headline-md text-headline-md text-primary mb-sm flex items-center gap-base">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">update</span>
                        5. Changes
                    </h2>
<p class="font-body-md text-body-md text-on-surface leading-relaxed">
                        We may update these terms when necessary. Changes will be posted on this page.
                    </p>
</div>
<div class="group">
<h2 class="font-headline-md text-headline-md text-primary mb-sm flex items-center gap-base">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">mail</span>
                        6. Contact
                    </h2>
<p class="font-body-md text-body-md text-on-surface leading-relaxed">
                        For questions, please visit our Contact page.
                    </p>
</div>
</section>
</article>
</main>
@endsection
