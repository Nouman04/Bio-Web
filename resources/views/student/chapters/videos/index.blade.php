@extends('layouts.student')

@section('title', 'Chapter Videos')

@push('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    
    .hover-lift {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
    }
</style>
@endpush

@section('content')
<div class="py-8 max-w-[1600px] w-full mx-auto flex-1 flex flex-col">
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-on-surface-variant text-xs mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('student.courses') }}">My Courses</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <a class="hover:text-primary transition-colors" href="{{ route('student.courses.show', ['id' => $courseId]) }}">Course {{ $courseId }}</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <a class="hover:text-primary transition-colors" href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}">Chapter {{ $chapterId }}</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span class="text-on-surface font-semibold">Videos</span>
    </nav>

    <!-- Header & Controls -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-on-surface mb-2">Chapter Videos</h1>
            <p class="text-sm text-on-surface-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">video_library</span>
                12 Video Lessons Available
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <div class="relative w-full sm:w-64 glass-panel rounded-full flex items-center px-4 py-2 focus-within:ring-2 focus-within:ring-primary/20 transition-all border-outline-variant/30">
                <span class="material-symbols-outlined text-outline mr-2 text-sm">search</span>
                <input class="bg-transparent border-none focus:ring-0 w-full text-sm text-on-surface placeholder-outline outline-none" placeholder="Find a specific lesson..." type="text"/>
            </div>
            <button class="glass-panel flex items-center gap-2 px-4 py-2 rounded-full text-sm text-on-surface hover:bg-surface-container-high transition-colors border-outline-variant/30">
                <span class="material-symbols-outlined text-sm">sort</span>
                Newest First
                <span class="material-symbols-outlined text-sm ml-1">expand_more</span>
            </button>
        </div>
    </div>

    <!-- Video Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <a href="{{ route('student.chapters.videos.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'videoId' => 1]) }}" class="glass-panel rounded-xl overflow-hidden flex flex-col hover-lift group border-outline-variant/30 shadow-sm relative block">
            <div class="relative h-48 w-full overflow-hidden bg-surface-container">
                <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC-d-akkHIE1BvxZcNfvkvL130r7vS4kYqzYXWxXtx8kXlkFdbGFiwzy5JFHrt0AynTJJQjYI8Qdnjw69kBeHZfCm2Ti9Av2obbLmya7JYJPrxm02ds38tEF447D_EStS-cmpBsxUKSpRibc-UFpd7AInxaiHJv4kcvDsdEmDyLiN1eemk8BQbNr-tRa2L13pbTMxwtb4TLuPWF2ITlnwrUPbGKIoC7pzOZ4Ac5ZPWhUaU01HqCiu2F" alt="Thumbnail"/>
                <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center transform scale-90 group-hover:scale-100 transition-all shadow-lg border border-white/30">
                        <span class="material-symbols-outlined text-white text-3xl ml-1" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                    </div>
                </div>
                <div class="absolute bottom-3 right-3 bg-inverse-surface/80 backdrop-blur-sm text-inverse-on-surface text-[10px] px-2 py-1 rounded-md">
                    45:20
                </div>
                <div class="absolute top-3 left-3 bg-tertiary-container/90 text-on-tertiary-container text-[10px] px-2 py-1 rounded-md shadow-sm border border-tertiary/20 font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[12px]">check_circle</span>
                    Completed
                </div>
            </div>
            <div class="p-5 flex flex-col flex-1">
                <h3 class="text-lg font-semibold text-on-surface mb-2 line-clamp-2 leading-tight group-hover:text-primary transition-colors">Introduction to Neural Networks &amp; Deep Learning</h3>
                <p class="text-sm text-on-surface-variant mb-4 line-clamp-2">Explore the foundational concepts of artificial neural networks, perceptrons, and backpropagation in modern ML.</p>
                <div class="mt-auto">
                    <div class="flex justify-between items-center mb-4 text-xs text-outline">
                        <span>Dr. Alan Turing</span>
                        <span>Added 2 days ago</span>
                    </div>
                    <div class="w-full bg-surface-container-high text-on-surface text-sm font-semibold py-2.5 rounded-full flex justify-center items-center gap-2 group-hover:bg-primary group-hover:text-white transition-all">
                        Watch Again
                    </div>
                </div>
            </div>
        </a>

        <!-- Card 2 -->
        <a href="{{ route('student.chapters.videos.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'videoId' => 2]) }}" class="glass-panel rounded-xl overflow-hidden flex flex-col hover-lift group border-outline-variant/30 shadow-sm relative block">
            <div class="relative h-48 w-full overflow-hidden bg-surface-container">
                <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC2bjuljmgpEKepekC_hmVmecTHcec9CuVbVXPOBRPZFPyv4jLabpMV39j28-pBB8tBAkG1KiRJq4gFrSWPesyl__xLVUCULoDiwF8cZynhzabEpd7foPNIe1kBIHOR_t9JIetAwtJl1IhGKp6Ag3sHib3AznuMsZdzR2MqIXpXUnYqr8tt-FgvIx0lxHCVHdFLfxvkuMmbo7lp9Tsgy9pBHmvXJKBi1Xrlg-CLYSm1rwuhR1buTETe" alt="Thumbnail"/>
                <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center transform scale-90 group-hover:scale-100 transition-all shadow-lg border border-white/30">
                        <span class="material-symbols-outlined text-white text-3xl ml-1" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 h-1 bg-surface-container w-full z-20">
                    <div class="h-full bg-secondary-container w-[45%]"></div>
                </div>
                <div class="absolute bottom-3 right-3 bg-inverse-surface/80 backdrop-blur-sm text-inverse-on-surface text-[10px] px-2 py-1 rounded-md z-20">
                    1:12:05
                </div>
                <div class="absolute top-3 left-3 bg-secondary-container/90 text-on-secondary-container text-[10px] px-2 py-1 rounded-md shadow-sm border border-secondary/20 font-semibold flex items-center gap-1 z-20">
                    <span class="material-symbols-outlined text-[12px]">schedule</span>
                    In Progress (45%)
                </div>
            </div>
            <div class="p-5 flex flex-col flex-1">
                <h3 class="text-lg font-semibold text-on-surface mb-2 line-clamp-2 leading-tight group-hover:text-primary transition-colors">Convolutional Neural Networks (CNNs) Explained</h3>
                <p class="text-sm text-on-surface-variant mb-4 line-clamp-2">Deep dive into image processing architectures. We cover pooling, stride, and filter design.</p>
                <div class="mt-auto">
                    <div class="flex justify-between items-center mb-4 text-xs text-outline">
                        <span>Dr. Alan Turing</span>
                        <span>Added 5 days ago</span>
                    </div>
                    <div class="w-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold py-2.5 rounded-full flex justify-center items-center gap-2 group-hover:shadow-md transition-all">
                        Resume Video
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
