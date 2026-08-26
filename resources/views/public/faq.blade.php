@extends('public.layouts.app')

@section('title', 'FAQs | Lumina LMS')

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&display=swap');
        body { font-family: 'Geist', sans-serif; background-color: #f7f9fb; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 19, 48, 0.05);
        }
        .accordion-content {
            transition: max-height 0.3s ease-out, opacity 0.3s ease-out, padding 0.3s ease;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }
        .accordion-item.active .accordion-content {
            max-height: 500px;
            opacity: 1;
            padding-bottom: 24px;
        }
        .accordion-icon {
            transition: transform 0.3s ease;
        }
        .accordion-item.active .accordion-icon {
            transform: rotate(180deg);
        }
</style>
@endpush

@section('content')
<main class="flex-grow w-full max-w-container-max mx-auto px-md lg:px-lg py-xl">
<!-- Header Section -->
<section class="text-center max-w-3xl mx-auto mb-xl">
<h1 class="font-display-lg text-display-lg text-on-surface mb-sm">FAQs</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant">Find answers to common questions about our Cambridge IGCSE Biology platform.</p>
</section>
<!-- FAQs Grid / Accordion -->
<section class="max-w-4xl mx-auto flex flex-col gap-sm">
<!-- Item 1 -->
<div class="accordion-item glass-panel rounded-lg overflow-hidden cursor-pointer" onclick="this.classList.toggle('active')">
<div class="flex justify-between items-center px-lg py-md">
<h3 class="font-headline-md text-headline-md text-on-surface">What syllabus does this website cover?</h3>
<span class="material-symbols-outlined accordion-icon text-primary" data-icon="expand_more">expand_more</span>
</div>
<div class="accordion-content px-lg font-body-md text-body-md text-on-surface-variant">
<p class="">The website is designed specifically for Cambridge IGCSE Biology (0610) and Cambridge GCE Biology (5090) with resources aligned with the 2026–2028 syllabus.</p>
</div>
</div>
<!-- Item 2 -->
<div class="accordion-item glass-panel rounded-lg overflow-hidden cursor-pointer" onclick="this.classList.toggle('active')">
<div class="flex justify-between items-center px-lg py-md">
<h3 class="font-headline-md text-headline-md text-on-surface">Is everything on the website free?</h3>
<span class="material-symbols-outlined accordion-icon text-primary" data-icon="expand_more">expand_more</span>
</div>
<div class="accordion-content px-lg font-body-md text-body-md text-on-surface-variant">
<p class="">Most core learning resources are designed to be freely accessible. Some additional features or resources may be introduced in the future.</p>
</div>
</div>
<!-- Item 3 -->
<div class="accordion-item glass-panel rounded-lg overflow-hidden cursor-pointer" onclick="this.classList.toggle('active')">
<div class="flex justify-between items-center px-lg py-md">
<h3 class="font-headline-md text-headline-md text-on-surface">Who is this website for?</h3>
<span class="material-symbols-outlined accordion-icon text-primary" data-icon="expand_more">expand_more</span>
</div>
<div class="accordion-content px-lg font-body-md text-body-md text-on-surface-variant">
<p class="">It is primarily designed for IGCSE and GCE Biology students, whether you are learning a topic for the first time, revising for exams, or practising past-paper questions.</p>
</div>
</div>
<!-- Item 4 -->
<div class="accordion-item glass-panel rounded-lg overflow-hidden cursor-pointer" onclick="this.classList.toggle('active')">
<div class="flex justify-between items-center px-lg py-md">
<h3 class="font-headline-md text-headline-md text-on-surface">Are the notes exam-focused?</h3>
<span class="material-symbols-outlined accordion-icon text-primary" data-icon="expand_more">expand_more</span>
</div>
<div class="accordion-content px-lg font-body-md text-body-md text-on-surface-variant">
<p class="">Yes. The resources focus on the knowledge, terminology, diagrams, and application skills you need for your Cambridge IGCSE Biology exams.</p>
</div>
</div>
<!-- Item 5 -->
<div class="accordion-item glass-panel rounded-lg overflow-hidden cursor-pointer" onclick="this.classList.toggle('active')">
<div class="flex justify-between items-center px-lg py-md">
<h3 class="font-headline-md text-headline-md text-on-surface">Can I use the website on my phone?</h3>
<span class="material-symbols-outlined accordion-icon text-primary" data-icon="expand_more">expand_more</span>
</div>
<div class="accordion-content px-lg font-body-md text-body-md text-on-surface-variant">
<p class="">Yes. The website is designed to work across phones, tablets, and computers.</p>
</div>
</div>
<!-- Item 6 -->
<div class="accordion-item glass-panel rounded-lg overflow-hidden cursor-pointer" onclick="this.classList.toggle('active')">
<div class="flex justify-between items-center px-lg py-md">
<h3 class="font-headline-md text-headline-md text-on-surface">How should I use the website for revision?</h3>
<span class="material-symbols-outlined accordion-icon text-primary" data-icon="expand_more">expand_more</span>
</div>
<div class="accordion-content px-lg font-body-md text-body-md text-on-surface-variant">
<p class="">Start with the chapter notes, test yourself using MCQs and questions, then use the exam tips and exam traps to strengthen your exam technique.</p>
</div>
</div>
<!-- Item 7 -->
<div class="accordion-item glass-panel rounded-lg overflow-hidden cursor-pointer" onclick="this.classList.toggle('active')">
<div class="flex justify-between items-center px-lg py-md">
<h3 class="font-headline-md text-headline-md text-on-surface">Are the diagrams available separately?</h3>
<span class="material-symbols-outlined accordion-icon text-primary" data-icon="expand_more">expand_more</span>
</div>
<div class="accordion-content px-lg font-body-md text-body-md text-on-surface-variant">
<p class="">Yes. Important Biology diagrams are organised so you can easily find and revise them without going through an entire chapter.</p>
</div>
</div>
<!-- Item 8 -->
<div class="accordion-item glass-panel rounded-lg overflow-hidden cursor-pointer" onclick="this.classList.toggle('active')">
<div class="flex justify-between items-center px-lg py-md">
<h3 class="font-headline-md text-headline-md text-on-surface">How often are new resources added?</h3>
<span class="material-symbols-outlined accordion-icon text-primary" data-icon="expand_more">expand_more</span>
</div>
<div class="accordion-content px-lg font-body-md text-body-md text-on-surface-variant">
<p class="">New resources will be added and existing materials will be improved as the website grows.</p>
</div>
</div>
<!-- Item 9 -->
<div class="accordion-item glass-panel rounded-lg overflow-hidden cursor-pointer" onclick="this.classList.toggle('active')">
<div class="flex justify-between items-center px-lg py-md">
<h3 class="font-headline-md text-headline-md text-on-surface">I found an error. What should I do?</h3>
<span class="material-symbols-outlined accordion-icon text-primary" data-icon="expand_more">expand_more</span>
</div>
<div class="accordion-content px-lg font-body-md text-body-md text-on-surface-variant">
<p class="">Please let us know through the Contact page. Corrections and feedback are always welcome.</p>
</div>
</div>
<!-- Item 10 -->
<div class="accordion-item glass-panel rounded-lg overflow-hidden cursor-pointer" onclick="this.classList.toggle('active')">
<div class="flex justify-between items-center px-lg py-md">
<h3 class="font-headline-md text-headline-md text-on-surface">Can I suggest a topic or resource?</h3>
<span class="material-symbols-outlined accordion-icon text-primary" data-icon="expand_more">expand_more</span>
</div>
<div class="accordion-content px-lg font-body-md text-body-md text-on-surface-variant">
<p class="">Absolutely. If there is a resource you would like to see added, send your suggestion through the Contact page.</p>
</div>
</div></section>
<!-- Still have questions CTA -->
<section class="mt-xl text-center glass-panel rounded-xl p-lg max-w-3xl mx-auto">
<h2 class="font-headline-lg text-headline-lg text-on-surface mb-sm">Still have questions?</h2>
<p class="font-body-md text-body-md text-on-surface-variant mb-md">Can't find the answer you're looking for? Reach out to our support team.</p>
<a class="inline-flex items-center gap-xs px-lg py-sm bg-primary text-on-primary font-label-md rounded-xl hover:bg-primary/90 hover:shadow-[0_0_15px_rgba(0, 19, 48, 0.4)] transition-all duration-200 active:scale-95" href="#">
                Contact Support
                <span class="material-symbols-outlined text-sm" data-icon="arrow_forward">arrow_forward</span>
</a>
</section>
</main>
@endsection
