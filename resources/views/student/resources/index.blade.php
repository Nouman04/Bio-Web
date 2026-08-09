@extends('layouts.student')

@section('title', 'My Resources Central Hub')
@section('meta-description', 'Manage and access all your educational materials — videos, quizzes, flashcards, notes and guides')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
    }
</style>
@endpush

@section('content')
<div class="flex flex-col gap-6 pt-4">

    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-on-surface mb-2" style="font-size:32px;line-height:40px;letter-spacing:-0.01em;font-weight:600;">My Resources Central Hub</h2>
            <p class="text-on-surface-variant" style="font-size:18px;line-height:28px;">Manage and access all your educational materials.</p>
        </div>
    </div>

    <!-- Bento Grid: Video Lessons + Quizzes -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- Video Lessons -->
        <div class="bg-surface-container-lowest rounded-xl p-6 hover-lift border border-outline-variant/30 flex flex-col h-full shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">play_circle</span>
                    </div>
                    <h3 class="text-on-surface" style="font-size:20px;line-height:28px;font-weight:600;">Video Lessons</h3>
                </div>
                <a class="text-primary hover:underline text-sm font-semibold" href="#">View All</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
                <!-- Video 1 -->
                <div class="group relative rounded-lg overflow-hidden border border-outline-variant/30 cursor-pointer">
                    <div class="aspect-video bg-surface-dim relative">
                        <img alt="Advanced Calculus"
                             class="w-full h-full object-cover"
                             src="https://lh3.googleusercontent.com/aida-public/AB6AXuDbnPng2FzyYZoHZx6jEIFJ20EmlCMD4kri0e3VzikBupLuFMs8wj_lSqyDbQEW5Cu8LihGO43q1fTNDHyWJZxoZvoTbcu_t2pzF1dqlLChTFGUGPum-y3XN1stRTnviCD0lSa1SMgTXWncwTUbbensAYZfTs37cWGkSFNYXTxRE_73Xjugdo9ZCxElA_vapGb0ADh-YUbMsPx3Z2hkSq7EXWkw1BYesgTwm6nO6ru-Csht1LiG331q"/>
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-4xl opacity-80 group-hover:opacity-100 transition-opacity" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                        </div>
                        <div class="absolute bottom-2 right-2 bg-black/70 text-white text-xs px-2 py-1 rounded font-medium">12:45</div>
                    </div>
                    <div class="p-4 bg-surface-container-lowest">
                        <h4 class="text-on-surface text-sm font-semibold line-clamp-1 mb-1">Advanced Calculus: Integration</h4>
                        <p class="text-on-surface-variant text-xs line-clamp-1">Module 4 • Prof. Davis</p>
                    </div>
                </div>
                <!-- Video 2 -->
                <div class="group relative rounded-lg overflow-hidden border border-outline-variant/30 cursor-pointer hidden sm:block">
                    <div class="aspect-video bg-surface-dim relative">
                        <img alt="Chemical Reactions"
                             class="w-full h-full object-cover"
                             src="https://lh3.googleusercontent.com/aida-public/AB6AXuCHbcPuYpRgdjbb6lrR5f1XSIZNc_TOY3whe_borKHWyhYJYXjuPnd6dFxVMUWXEP40tcdGhWv5-bbKg1jPtoCPwsaYQ5HhqHq1CU1NnRQ6haIZlr9ffqwYmkQztCtGXYyRhHndvKqvqU8uAsrSnAztxtDeryEgoinhEoqphG3OGC6gtQ3K4faoA_OvihwtTmDhy9bCwIpTJCZjqJWukDFLI-r5UzB5VzuOoT-B_zHGTz3dNfXBBHRX"/>
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-4xl opacity-80 group-hover:opacity-100 transition-opacity" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                        </div>
                        <div class="absolute bottom-2 right-2 bg-black/70 text-white text-xs px-2 py-1 rounded font-medium">08:20</div>
                    </div>
                    <div class="p-4 bg-surface-container-lowest">
                        <h4 class="text-on-surface text-sm font-semibold line-clamp-1 mb-1">Chemical Reactions Basics</h4>
                        <p class="text-on-surface-variant text-xs line-clamp-1">Module 2 • Dr. Chen</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quizzes -->
        <div class="bg-surface-container-lowest rounded-xl p-6 hover-lift border border-outline-variant/30 flex flex-col h-full shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-tertiary/10 flex items-center justify-center text-tertiary">
                        <span class="material-symbols-outlined">quiz</span>
                    </div>
                    <h3 class="text-on-surface" style="font-size:20px;line-height:28px;font-weight:600;">Quizzes</h3>
                </div>
                <a class="text-primary hover:underline text-sm font-semibold" href="#">View All</a>
            </div>
            <div class="flex flex-col gap-4 flex-1">
                <!-- Quiz 1 -->
                <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/30 flex justify-between items-center hover:bg-surface-container-low transition-colors cursor-pointer shadow-sm">
                    <div>
                        <h4 class="text-on-surface text-sm font-semibold mb-1">Midterm Review Qz</h4>
                        <p class="text-on-surface-variant text-xs">25 Questions</p>
                    </div>
                    <div class="bg-tertiary/10 text-tertiary text-xs font-medium px-3 py-1.5 rounded-full">92%</div>
                </div>
                <!-- Quiz 2 -->
                <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/30 flex justify-between items-center hover:bg-surface-container-low transition-colors cursor-pointer shadow-sm">
                    <div>
                        <h4 class="text-on-surface text-sm font-semibold mb-1">Chapter 5 Self-Test</h4>
                        <p class="text-on-surface-variant text-xs">10 Questions</p>
                    </div>
                    <div class="bg-secondary-container/30 text-on-secondary-container text-xs font-medium px-3 py-1.5 rounded-full">Pending</div>
                </div>
                <!-- Quiz 3 -->
                <div class="bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/30 flex justify-between items-center hover:bg-surface-container-low transition-colors cursor-pointer opacity-70 shadow-sm">
                    <div>
                        <h4 class="text-on-surface text-sm font-semibold mb-1">Intro Quiz</h4>
                        <p class="text-on-surface-variant text-xs">5 Questions</p>
                    </div>
                    <div class="bg-surface-variant text-on-surface-variant text-xs font-medium px-3 py-1.5 rounded-full">100%</div>
                </div>
            </div>
        </div>

        <!-- Flashcards -->
        <div class="bg-surface-container-lowest rounded-xl p-6 hover-lift border border-outline-variant/30 flex flex-col h-full shadow-sm xl:col-span-1">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-secondary-container/40 flex items-center justify-center text-on-secondary-container">
                        <span class="material-symbols-outlined">style</span>
                    </div>
                    <h3 class="text-on-surface" style="font-size:20px;line-height:28px;font-weight:600;">Flashcards</h3>
                </div>
                <a class="text-primary hover:underline text-sm font-semibold" href="#">View All</a>
            </div>
            <div class="grid grid-cols-2 gap-4 flex-1">
                <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 p-5 cursor-pointer hover:border-secondary-container transition-colors shadow-sm">
                    <h4 class="text-on-surface text-sm font-semibold mb-2">Historical Dates</h4>
                    <p class="text-on-surface-variant text-xs">42 Cards</p>
                </div>
                <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 p-5 cursor-pointer hover:border-secondary-container transition-colors shadow-sm">
                    <h4 class="text-on-surface text-sm font-semibold mb-2">Vocab List 3</h4>
                    <p class="text-on-surface-variant text-xs">105 Cards</p>
                </div>
                <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/30 p-5 cursor-pointer hover:border-secondary-container transition-colors shadow-sm">
                    <h4 class="text-on-surface text-sm font-semibold mb-2">Biology Terms</h4>
                    <p class="text-on-surface-variant text-xs">78 Cards</p>
                </div>
                <div class="bg-surface-bright rounded-xl border border-dashed border-outline-variant/50 p-5 cursor-pointer hover:border-secondary-container hover:bg-surface-container-low transition-colors flex flex-col items-center justify-center text-center">
                    <span class="material-symbols-outlined text-outline-variant mb-2">add_circle</span>
                    <span class="text-outline text-xs font-medium">New Deck</span>
                </div>
            </div>
        </div>

        <!-- Study Notes & Summaries -->
        <div class="bg-surface-container-lowest rounded-xl p-6 hover-lift border border-outline-variant/30 flex flex-col h-full shadow-sm xl:col-span-1">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-surface-variant flex items-center justify-center text-on-surface-variant">
                        <span class="material-symbols-outlined">description</span>
                    </div>
                    <h3 class="text-on-surface" style="font-size:20px;line-height:28px;font-weight:600;">Study Notes &amp; Summaries</h3>
                </div>
                <a class="text-primary hover:underline text-sm font-semibold" href="#">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/20 text-on-surface-variant text-xs font-medium">
                            <th class="py-3 px-2">Document Name</th>
                            <th class="py-3 px-2 hidden sm:table-cell">Course</th>
                            <th class="py-3 px-2">Last Updated</th>
                            <th class="py-3 px-2 text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-outline-variant/10 hover:bg-surface-container-low transition-colors">
                            <td class="py-4 px-2">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-error text-xl">picture_as_pdf</span>
                                    <span class="text-on-surface text-sm font-medium">Week 1-4 Summary</span>
                                </div>
                            </td>
                            <td class="py-4 px-2 text-on-surface-variant text-sm hidden sm:table-cell">Psychology 101</td>
                            <td class="py-4 px-2 text-on-surface-variant text-sm">Oct 12, 2023</td>
                            <td class="py-4 px-2 text-right">
                                <button class="text-on-surface-variant hover:text-primary hover:bg-primary-container/10 p-2 rounded-full transition-colors inline-flex">
                                    <span class="material-symbols-outlined text-sm">more_vert</span>
                                </button>
                            </td>
                        </tr>
                        <tr class="border-b border-outline-variant/10 hover:bg-surface-container-low transition-colors">
                            <td class="py-4 px-2">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-primary text-xl">article</span>
                                    <span class="text-on-surface text-sm font-medium">Lecture Notes: AI Ethics</span>
                                </div>
                            </td>
                            <td class="py-4 px-2 text-on-surface-variant text-sm hidden sm:table-cell">CompSci 402</td>
                            <td class="py-4 px-2 text-on-surface-variant text-sm">Oct 10, 2023</td>
                            <td class="py-4 px-2 text-right">
                                <button class="text-on-surface-variant hover:text-primary hover:bg-primary-container/10 p-2 rounded-full transition-colors inline-flex">
                                    <span class="material-symbols-outlined text-sm">more_vert</span>
                                </button>
                            </td>
                        </tr>
                        <tr class="border-b border-outline-variant/10 hover:bg-surface-container-low transition-colors">
                            <td class="py-4 px-2">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-error text-xl">picture_as_pdf</span>
                                    <span class="text-on-surface text-sm font-medium">Equation Cheat Sheet</span>
                                </div>
                            </td>
                            <td class="py-4 px-2 text-on-surface-variant text-sm hidden sm:table-cell">Physics II</td>
                            <td class="py-4 px-2 text-on-surface-variant text-sm">Sep 28, 2023</td>
                            <td class="py-4 px-2 text-right">
                                <button class="text-on-surface-variant hover:text-primary hover:bg-primary-container/10 p-2 rounded-full transition-colors inline-flex">
                                    <span class="material-symbols-outlined text-sm">more_vert</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Last Viewed + Guides & Diagrams -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Last Viewed -->
        <div class="bg-surface-container-lowest rounded-xl p-6 border border-outline-variant/30 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
                <span class="material-symbols-outlined text-primary">history</span>
                <h3 class="text-on-surface" style="font-size:20px;line-height:28px;font-weight:600;">Last Viewed</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="flex gap-4 items-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-lg bg-surface-dim shrink-0 overflow-hidden relative border border-outline-variant/20">
                        <img alt="Cellular Biology" class="w-full h-full object-cover"
                             src="https://lh3.googleusercontent.com/aida-public/AB6AXuB_AxqsieRnk73AwifK6_cDBF6uEQYuFp0ehYIgahLNeCY_FTzSjz9enSDsesMVCQV4l49Ohw5_BcSVIMpn-tuKdUs__rK42bDe3byb656uxwHePcjX82K7tk4NHcEtl8kJCdAffV6LNX3Qs5jS_FaD47X76TuiGELOwptyYXDsB0fq1HAy58tSgWENx9_viRb9tpYyHY5mr3UL8THgvB6HI0s4haIieMSh6C957KYKoqrJIfJC7jRB"/>
                    </div>
                    <div>
                        <h4 class="text-on-surface text-xs font-semibold group-hover:text-primary transition-colors line-clamp-1">Cellular Biology Diagram</h4>
                        <p class="text-on-surface-variant text-xs mt-1">2 hrs ago</p>
                    </div>
                </div>
                <div class="flex gap-4 items-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-lg bg-tertiary/10 flex items-center justify-center shrink-0 text-tertiary border border-tertiary/20">
                        <span class="material-symbols-outlined">quiz</span>
                    </div>
                    <div>
                        <h4 class="text-on-surface text-xs font-semibold group-hover:text-primary transition-colors line-clamp-1">Midterm Review Qz</h4>
                        <p class="text-on-surface-variant text-xs mt-1">Yesterday</p>
                    </div>
                </div>
                <div class="flex gap-4 items-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-lg bg-surface-dim shrink-0 overflow-hidden relative border border-outline-variant/20">
                        <img alt="Advanced Calculus" class="w-full h-full object-cover"
                             src="https://lh3.googleusercontent.com/aida-public/AB6AXuDGTcWys3Atp6RmxF1msHC3C7WiXSS8G3fQp9i4nwvfQlbVF41CVH0E2h-m78NONKV--9L_HJJeecADTxEkztMNADXfiutbIeWCD-2TJZfSyCAJ82s_rrGmVrUBzL-WO6k5p-aIk2zbAVn1IRrBJaucpJ9ESwTba4Eho0Yl6JaQNPfoAVM2FmwGtWAJirjQJEWv-G9g0R6m3jGg9dOGtEta9rvncWiuZNHp1gRP5PT4petlU2Um8UZ3"/>
                        <div class="absolute inset-0 bg-black/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-sm">play_arrow</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-on-surface text-xs font-semibold group-hover:text-primary transition-colors line-clamp-1">Advanced Calculus</h4>
                        <p class="text-on-surface-variant text-xs mt-1">Oct 12</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guides & Diagrams -->
        <div class="bg-surface-container-lowest rounded-xl p-6 border border-outline-variant/30 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-secondary-container">schema</span>
                    <h3 class="text-on-surface" style="font-size:20px;line-height:28px;font-weight:600;">Guides &amp; Diagrams</h3>
                </div>
                <a class="text-primary hover:underline text-xs font-semibold" href="#">All</a>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="group relative rounded-lg overflow-hidden border border-outline-variant/30 cursor-pointer">
                    <div class="h-24 bg-surface-dim w-full">
                        <img alt="Water Cycle" class="w-full h-full object-cover"
                             src="https://lh3.googleusercontent.com/aida-public/AB6AXuBzxygWG9TVBDBhEttt5ApR_asAZ9YF9IydLJCiPoL-ip2XLENOGuHna0_8nZ--aVqDOBWLyc7fQUvvyBvrcCia4m_sAsocOUAvav3eaUQfbSV2FLvrzg1DqqLFJ5RXuCe3Y0fEWSTyezaZYFm_l4VNWf709QloaO72O4vgpucTuAKeFveI5eQD9fjgSSVfVk80laHgXfk1I2oQmBGWCEoG11DxwQDYztYn9GaW_hOk5Etp6Sg3X3eq"/>
                    </div>
                    <div class="p-2 bg-surface-container-lowest">
                        <h4 class="text-on-surface text-xs font-semibold line-clamp-1">Water Cycle</h4>
                    </div>
                </div>
                <div class="group relative rounded-lg overflow-hidden border border-outline-variant/30 cursor-pointer">
                    <div class="h-24 bg-surface-container-low flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl text-outline-variant">account_tree</span>
                    </div>
                    <div class="p-2 bg-surface-container-lowest">
                        <h4 class="text-on-surface text-xs font-semibold line-clamp-1">Mindmap Guide</h4>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
