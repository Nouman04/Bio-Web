@extends('public.layouts.app')

@section('title', 'Contact Us | Your Biology')

@section('content')
<main class="flex-grow w-full max-w-container-max mx-auto px-md lg:px-lg py-xl">
<!-- Hero Section -->
<section class="text-center mb-xl max-w-3xl mx-auto">
<h1 class="font-display-lg text-display-lg text-on-surface mb-6">Have a question?</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant">Whether you’ve spotted an error, have a suggestion, or simply need help finding a resource, we’d love to hear from you.</p>
</section>
<!-- Two Column Layout -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12">
<!-- Left Column: Info & Suggestion -->
<div class="md:col-span-5 flex flex-col gap-8">
<div class="glass-panel p-8">
<h2 class="font-headline-md text-headline-md text-on-surface mb-4 flex items-center gap-2">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">mail</span>
                        Contact Information
                    </h2>
<p class="font-body-md text-body-md text-on-surface-variant flex items-center gap-3">
<span class="text-on-surface font-semibold">Email:</span>
<a class="text-primary hover:underline" href="mailto:abc@luminalms.com">abc@luminalms.com</a>
</p>
</div>
<div class="glass-panel p-8 bg-surface-container-low/50">
<h3 class="font-headline-md text-headline-md text-on-surface mb-4 flex items-center gap-2">
<span class="material-symbols-outlined text-tertiary" style="font-variation-settings: 'FILL' 1;">lightbulb</span>
                        Have a suggestion?
                    </h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                        Missing a topic? Want a particular diagram, question type, or revision resource? Tell us what would help you learn Biology better.
                    </p>
</div>
<div class="glass-panel p-8 relative overflow-hidden group min-h-[200px] flex items-end">
<div class="absolute inset-0 z-0">

{{-- Photography brief: A serene, minimalist 3D rendering of a stylized biology laboratory desk. Soft, high-key lighting illuminates glass beakers and a microscope resting on... --}}
<div class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-700 ease-out bg-primary/20" role="img" aria-label="A serene, minimalist 3D rendering of a stylized biology laboratory desk. Soft, high-key li..."></div>
</div>
<div class="absolute inset-0 bg-surface-container-lowest/70 z-10"></div>
<p class="relative z-20 font-label-md text-label-md text-on-surface-variant italic">
                        "Biology is the study of complex things that appear to have been designed for a purpose."
                    </p>
</div>
</div>
<!-- Right Column: Form -->
<div class="md:col-span-7">
<div class="glass-panel p-8 md:p-12 glass-shadow">
<h2 class="font-headline-md text-headline-md text-on-surface mb-8">Get in touch</h2>
<form class="flex flex-col gap-6">
<div class="flex flex-col gap-2">
<label class="font-label-md text-label-md text-on-surface-variant ml-2" for="name">Name</label>
<input class="w-full bg-surface-container-low border-none rounded-full px-6 py-4 font-body-md text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-all duration-200 outline-none" id="name" placeholder="Your name" type="text"/>
</div>
<div class="flex flex-col gap-2">
<label class="font-label-md text-label-md text-on-surface-variant ml-2" for="email">Email</label>
<input class="w-full bg-surface-container-low border-none rounded-full px-6 py-4 font-body-md text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-all duration-200 outline-none" id="email" placeholder="Your email" type="email"/>
</div>
<div class="flex flex-col gap-2">
<label class="font-label-md text-label-md text-on-surface-variant ml-2" for="subject">Subject</label>
<input class="w-full bg-surface-container-low border-none rounded-full px-6 py-4 font-body-md text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-all duration-200 outline-none" id="subject" placeholder="What is your message about?" type="text"/>
</div>
<div class="flex flex-col gap-2">
<label class="font-label-md text-label-md text-on-surface-variant ml-2" for="message">Message</label>
<textarea class="w-full bg-surface-container-low border-none rounded-xl px-6 py-4 font-body-md text-body-md text-on-surface focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-all duration-200 outline-none resize-none" id="message" placeholder="Write your message here" rows="5"></textarea>
</div>
<button class="mt-4 font-label-md text-label-md font-bold px-8 py-4 bg-primary text-on-primary rounded-full hover:bg-primary/90 transition-all duration-200 shadow-lg shadow-primary/30 active:scale-95 flex items-center justify-center gap-2" type="submit">
                            Send Message
                            <span class="material-symbols-outlined text-[18px]">send</span>
</button>
</form>
</div>
</div>
</div>
</main>
@endsection
