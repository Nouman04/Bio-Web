@extends('layouts.student')

@section('title', 'Chapter Dashboard – Neural Network Architectures')
@section('meta-description', 'Study video, notes, flashcards and assessments for this chapter')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .glass-panel:hover {
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
    }
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .resource-tab { transition: color 0.2s, border-color 0.2s; }
    .resource-tab.active { color: #4648d4; border-bottom-color: #4648d4; }
</style>
@endpush

@section('content')

<div class="grid grid-cols-12 gap-6 pt-4">

    <!-- ═══════════════════════════════════ LEFT COLUMN (8 cols) ═══════════════════════════════════ -->
    <div class="col-span-12 lg:col-span-8 flex flex-col gap-6">

        <!-- Back breadcrumb -->
        <div>
            <a href="{{ route('student.chapters', ['courseId' => $courseId]) }}"
               class="inline-flex items-center gap-1 text-primary hover:opacity-80 text-xs font-semibold mb-1 transition-opacity">
                <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
                Back to Chapter List
            </a>
        </div>

        <!-- ── Video Player ── -->
        <section class="glass-panel rounded-xl overflow-hidden shadow-sm flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-lowest">
                <div>
                    <h2 class="text-on-surface" style="font-size:24px;line-height:32px;font-weight:600;">Advanced Machine Learning</h2>
                    <p class="text-on-surface-variant text-sm">Chapter {{ $chapterId }}: Neural Network Architectures</p>
                </div>
                <span class="bg-primary/10 text-primary text-xs font-semibold px-3 py-1 rounded-full">In Progress</span>
            </div>

            <!-- Video Area -->
            <div class="relative w-full aspect-video bg-inverse-surface group cursor-pointer flex items-center justify-center overflow-hidden">
                <!-- Thumbnail -->
                <div class="absolute inset-0 bg-cover bg-center opacity-70 group-hover:opacity-60 transition-opacity duration-300"
                     style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDM3YEH15lcERp-BFnFdOsliNpP-peMEnCqVCsHpKGIipL4bYZALbmHQta42p7Gm_a4XErB71y5D08c2pnt3WR_1zf0zSEsf6oA1o4J36iS7uNnuw2hCYU1WPTCU89S6tIFbc000SfvOAiH8eGWGDR4s7PdEYE4NhK-5z-2tmBTcrtXdP2VjLB5Q6JnscpnJAqjkstzfu5xxJ2Vf0YwVlQ3jruKuqtxRtIxcpRX4Em-OYNgARwFtWzG')">
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

                <!-- Play Button -->
                <div class="w-20 h-20 rounded-full bg-primary/90 flex items-center justify-center shadow-[0_0_30px_rgba(70,72,212,0.5)] z-10 group-hover:scale-110 transition-transform duration-200">
                    <span class="material-symbols-outlined text-on-primary" style="font-size:36px;font-variation-settings:'FILL' 1;">play_arrow</span>
                </div>

                <!-- Hover Controls -->
                <div class="absolute bottom-0 left-0 w-full p-4 flex flex-col gap-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <!-- Scrubber -->
                    <div class="w-full h-1 bg-white/30 rounded-full overflow-hidden cursor-pointer">
                        <div class="h-full bg-primary-fixed rounded-full relative" style="width: 33%">
                            <div class="absolute right-0 top-1/2 -translate-y-1/2 w-3 h-3 bg-white rounded-full shadow"></div>
                        </div>
                    </div>
                    <!-- Controls Row -->
                    <div class="flex justify-between items-center text-white text-xs font-medium">
                        <div class="flex items-center gap-4">
                            <span class="material-symbols-outlined cursor-pointer hover:text-primary-fixed" style="font-size:20px;">pause</span>
                            <span class="material-symbols-outlined cursor-pointer hover:text-primary-fixed" style="font-size:20px;">volume_up</span>
                            <span>12:04 / 45:30</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="material-symbols-outlined cursor-pointer hover:text-primary-fixed" style="font-size:20px;">closed_caption</span>
                            <span class="material-symbols-outlined cursor-pointer hover:text-primary-fixed" style="font-size:20px;">settings</span>
                            <span class="material-symbols-outlined cursor-pointer hover:text-primary-fixed" style="font-size:20px;">fullscreen</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Interactive Resources ── -->
        <section class="glass-panel rounded-xl p-6 shadow-sm">
            <h3 class="text-on-surface mb-4" style="font-size:20px;line-height:28px;font-weight:600;">Interactive Resources</h3>

            <!-- Tabs -->
            <div class="flex border-b border-outline-variant/20 mb-6 overflow-x-auto hide-scrollbar gap-0" id="resourceTabs">
                <button onclick="switchResourceTab(this,'studyNotes')"
                        class="resource-tab active font-semibold text-sm border-b-2 pb-2 px-4 whitespace-nowrap">
                    Study Notes
                </button>
                <button onclick="switchResourceTab(this,'flashcards')"
                        class="resource-tab text-on-surface-variant hover:text-on-surface text-sm border-b-2 border-transparent pb-2 px-4 whitespace-nowrap transition-colors">
                    Flashcards
                </button>
                <button onclick="switchResourceTab(this,'diagrams')"
                        class="resource-tab text-on-surface-variant hover:text-on-surface text-sm border-b-2 border-transparent pb-2 px-4 whitespace-nowrap transition-colors">
                    Diagrams
                </button>
                <button onclick="switchResourceTab(this,'summaries')"
                        class="resource-tab text-on-surface-variant hover:text-on-surface text-sm border-b-2 border-transparent pb-2 px-4 whitespace-nowrap transition-colors">
                    Summaries
                </button>
                <button onclick="switchResourceTab(this,'videos')"
                        class="resource-tab text-on-surface-variant hover:text-on-surface text-sm border-b-2 border-transparent pb-2 px-4 whitespace-nowrap transition-colors">
                    Videos
                </button>
                {{-- View All dynamic link --}}
                <span id="viewAllLink" class="ml-auto pl-4 flex items-center">
                    <a id="viewAllAnchor"
                       href="{{ route('student.chapters.notes', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}"
                       class="text-primary text-xs font-semibold hover:underline flex items-center gap-1 whitespace-nowrap">
                        View All <span class="material-symbols-outlined" style="font-size:14px;">open_in_new</span>
                    </a>
                </span>
            </div>

            <!-- Study Notes Panel -->
            <div id="panel-studyNotes" class="space-y-3">
                <div class="p-4 bg-surface-container-lowest rounded-lg border border-surface-container-highest hover:border-primary/30 transition-colors cursor-pointer group">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="text-on-surface text-sm font-semibold group-hover:text-primary transition-colors">Backpropagation Fundamentals</h4>
                        <span class="material-symbols-outlined text-outline-variant group-hover:text-primary" style="font-size:18px;">open_in_new</span>
                    </div>
                    <p class="text-on-surface-variant text-xs line-clamp-2">Detailed mathematical breakdown of the chain rule as applied to multilayer perceptrons, including gradient descent optimizations...</p>
                </div>
                <div class="p-4 bg-surface-container-lowest rounded-lg border border-surface-container-highest hover:border-primary/30 transition-colors cursor-pointer group">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="text-on-surface text-sm font-semibold group-hover:text-primary transition-colors">Activation Functions Comparison</h4>
                        <span class="material-symbols-outlined text-outline-variant group-hover:text-primary" style="font-size:18px;">open_in_new</span>
                    </div>
                    <p class="text-on-surface-variant text-xs line-clamp-2">Key differences between Sigmoid, Tanh, ReLU, and Leaky ReLU, and when to use each based on specific architectural needs.</p>
                </div>
            </div>

            <!-- Flashcards Panel -->
            <div id="panel-flashcards" class="hidden">
                <div class="grid grid-cols-2 gap-4">
                    <div class="border border-outline-variant/30 rounded-xl p-5 cursor-pointer hover:border-primary/40 hover:shadow-sm transition-all bg-surface-container-lowest">
                        <h4 class="text-on-surface text-sm font-semibold mb-1">Neural Net Vocab</h4>
                        <p class="text-on-surface-variant text-xs">36 Cards</p>
                    </div>
                    <div class="border border-outline-variant/30 rounded-xl p-5 cursor-pointer hover:border-primary/40 hover:shadow-sm transition-all bg-surface-container-lowest">
                        <h4 class="text-on-surface text-sm font-semibold mb-1">Math Formulas</h4>
                        <p class="text-on-surface-variant text-xs">22 Cards</p>
                    </div>
                </div>
            </div>

            <!-- Diagrams Panel -->
            <div id="panel-diagrams" class="hidden">
                <div class="grid grid-cols-2 gap-4">
                    <div class="border border-outline-variant/30 rounded-xl overflow-hidden cursor-pointer hover:border-primary/40 transition-all bg-surface-container-lowest">
                        <div class="h-28 bg-surface-dim flex items-center justify-center">
                            <span class="material-symbols-outlined text-3xl text-outline-variant">account_tree</span>
                        </div>
                        <div class="p-3"><h4 class="text-on-surface text-xs font-semibold">Network Architecture Diagram</h4></div>
                    </div>
                    <div class="border border-outline-variant/30 rounded-xl overflow-hidden cursor-pointer hover:border-primary/40 transition-all bg-surface-container-lowest">
                        <div class="h-28 bg-surface-dim flex items-center justify-center">
                            <span class="material-symbols-outlined text-3xl text-outline-variant">schema</span>
                        </div>
                        <div class="p-3"><h4 class="text-on-surface text-xs font-semibold">Gradient Descent Flow</h4></div>
                    </div>
                </div>
            </div>

            <!-- Summaries Panel -->
            <div id="panel-summaries" class="hidden">
                <div class="space-y-3">
                    <div class="p-4 bg-surface-container-lowest rounded-lg border border-outline-variant/20 flex items-center gap-4 cursor-pointer hover:border-primary/30 transition-colors">
                        <span class="material-symbols-outlined text-error text-xl">picture_as_pdf</span>
                        <div>
                            <h4 class="text-on-surface text-sm font-semibold">Ch.{{ $chapterId }} Full Summary</h4>
                            <p class="text-on-surface-variant text-xs">4 pages • Updated Oct 2023</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Videos Panel -->
            <div id="panel-videos" class="hidden">
                <div class="grid grid-cols-2 gap-4">
                    <div class="border border-outline-variant/30 rounded-xl overflow-hidden cursor-pointer hover:border-primary/40 transition-all bg-surface-container-lowest flex items-center gap-3 p-3">
                        <div class="w-16 h-16 bg-surface-container rounded-md flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-outline-variant text-2xl">play_circle</span>
                        </div>
                        <div>
                            <h4 class="text-on-surface text-sm font-semibold line-clamp-1">Intro to Architecture</h4>
                            <p class="text-on-surface-variant text-xs">12:45 • Watch Now</p>
                        </div>
                    </div>
                    <div class="border border-outline-variant/30 rounded-xl overflow-hidden cursor-pointer hover:border-primary/40 transition-all bg-surface-container-lowest flex items-center gap-3 p-3">
                        <div class="w-16 h-16 bg-surface-container rounded-md flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-outline-variant text-2xl">play_circle</span>
                        </div>
                        <div>
                            <h4 class="text-on-surface text-sm font-semibold line-clamp-1">Advanced Optimization</h4>
                            <p class="text-on-surface-variant text-xs">18:20 • Watch Now</p>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>

    <!-- ═══════════════════════════════════ RIGHT COLUMN (4 cols) ═══════════════════════════════════ -->
    <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">

        <!-- ── Chapter Assessment ── -->
        <section class="glass-panel rounded-xl p-6 shadow-sm border-t-4 border-t-tertiary-container relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-tertiary/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-5 relative z-10">
                <div class="w-10 h-10 rounded-lg bg-tertiary/10 flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined">quiz</span>
                </div>
                <h3 class="text-on-surface" style="font-size:20px;line-height:28px;font-weight:600;">Chapter Assessment</h3>
                <a href="{{ route('student.resources') }}" class="ml-auto text-primary text-xs font-semibold hover:underline">View All</a>
            </div>
            <div class="space-y-4 relative z-10">
                <!-- Quiz 1 -->
                <div class="bg-surface-container-lowest p-4 rounded-lg border border-surface-container-highest">
                    <h4 class="text-on-surface text-sm font-semibold mb-1">Knowledge Check Quiz</h4>
                    <p class="text-on-surface-variant text-xs mb-4">15 multiple choice questions covering architecture types.</p>
                    <button class="w-full bg-surface-container hover:bg-surface-variant text-on-surface text-xs font-semibold py-2 rounded-full transition-colors flex items-center justify-center gap-2">
                        Start Quiz
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>
                </div>
                <!-- Quiz 2 -->
                <div class="bg-surface-container-lowest p-4 rounded-lg border border-surface-container-highest">
                    <h4 class="text-on-surface text-sm font-semibold mb-1">Practice Questions</h4>
                    <p class="text-on-surface-variant text-xs mb-4">5 open-ended conceptual problems to solve.</p>
                    <button class="w-full bg-surface-container hover:bg-surface-variant text-on-surface text-xs font-semibold py-2 rounded-full transition-colors flex items-center justify-center gap-2">
                        View Questions
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- ── Course Outline Timeline ── -->
        <section class="glass-panel rounded-xl p-6 shadow-sm">
            <h3 class="text-on-surface mb-5" style="font-size:20px;line-height:28px;font-weight:600;">Course Outline</h3>
            <div class="relative pl-6 border-l-2 border-surface-container-highest space-y-6">

                <!-- Completed -->
                <div class="relative">
                    <div class="absolute -left-[29px] top-1 w-4 h-4 rounded-full bg-tertiary-container border-4 border-surface-container-lowest"></div>
                    <p class="text-tertiary text-xs font-semibold mb-1">Completed</p>
                    <h4 class="text-on-surface text-sm">Chapter {{ max(1, $chapterId - 1) }}: Introduction to Variables</h4>
                </div>

                <!-- Current -->
                <div class="relative">
                    <div class="absolute -left-[31px] top-1 w-5 h-5 rounded-full bg-primary border-4 border-surface-container-lowest ring-2 ring-primary/30"></div>
                    <p class="text-primary text-xs font-semibold mb-1">Current</p>
                    <h4 class="text-on-surface text-sm font-semibold">Chapter {{ $chapterId }}: Neural Network Architectures</h4>
                </div>

                <!-- Up Next -->
                <div class="relative">
                    <div class="absolute -left-[29px] top-1 w-4 h-4 rounded-full bg-surface-container-highest border-4 border-surface-container-lowest"></div>
                    <p class="text-on-surface-variant text-xs font-semibold mb-1">Up Next</p>
                    <h4 class="text-on-surface-variant text-sm">Chapter {{ $chapterId + 1 }}: Convolutional Neural Networks</h4>
                </div>

            </div>

            <!-- Navigate Chapters -->
            <div class="flex items-center justify-between mt-8 pt-5 border-t border-outline-variant/20">
                @if($chapterId > 1)
                <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId - 1]) }}"
                   class="flex items-center gap-2 text-primary text-xs font-semibold hover:underline">
                    <span class="material-symbols-outlined text-sm">arrow_back</span> Previous
                </a>
                @else
                <span></span>
                @endif
                <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId + 1]) }}"
                   class="flex items-center gap-2 text-primary text-xs font-semibold hover:underline">
                    Next <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </section>

    </div>
</div>

@endsection

@push('scripts')
<script>
    const panels = ['studyNotes', 'flashcards', 'diagrams', 'summaries', 'videos'];

    const viewAllUrls = {
        studyNotes:  '{{ route('student.chapters.notes',      ['courseId' => $courseId, 'chapterId' => $chapterId]) }}',
        flashcards:  '{{ route('student.chapters.flashcards', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}',
        diagrams:    '{{ route('student.chapters.diagrams',   ['courseId' => $courseId, 'chapterId' => $chapterId]) }}',
        summaries:   '{{ route('student.chapters.summaries',  ['courseId' => $courseId, 'chapterId' => $chapterId]) }}',
        videos:      '{{ route('student.chapters.videos',     ['courseId' => $courseId, 'chapterId' => $chapterId]) }}',
    };

    function switchResourceTab(btn, id) {
        // Hide all panels
        panels.forEach(p => {
            const el = document.getElementById('panel-' + p);
            if (el) el.classList.add('hidden');
        });
        // Reset all tab styles
        document.querySelectorAll('.resource-tab').forEach(t => {
            t.classList.remove('active', 'text-primary');
            t.classList.add('text-on-surface-variant', 'border-transparent');
        });
        // Show selected panel
        const panel = document.getElementById('panel-' + id);
        if (panel) panel.classList.remove('hidden');
        btn.classList.add('active');
        btn.classList.remove('text-on-surface-variant', 'border-transparent');

        // Update "View All" link
        const anchor = document.getElementById('viewAllAnchor');
        if (anchor && viewAllUrls[id]) anchor.href = viewAllUrls[id];
    }
</script>
@endpush
