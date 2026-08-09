@extends('layouts.student')

@section('title', 'My Courses Hub')
@section('meta-description', 'Manage your learning journey and explore new materials')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .glass-panel {
        background-color: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .glass-panel-hover:hover {
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    .ambient-shadow {
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
    }
    .btn-primary-gradient {
        background: linear-gradient(135deg, #4648d4, #4f46e5);
        box-shadow: 0px 4px 15px rgba(99, 102, 241, 0.2);
    }
    .btn-primary-gradient:hover {
        background: linear-gradient(135deg, #4f46e5, #4648d4);
        box-shadow: 0px 6px 20px rgba(99, 102, 241, 0.3);
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
<!-- Page Header & Tabs -->
<div class="mb-8 pt-4">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-6">
        <div>
            <h2 class="text-on-background mb-2" style="font-size:48px;line-height:56px;letter-spacing:-0.02em;font-weight:700;">My Courses Hub</h2>
            <p class="text-on-surface-variant" style="font-size:18px;line-height:28px;">Manage your learning journey and explore new materials.</p>
        </div>
    </div>

    <!-- Tabs + Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-outline-variant/20 pb-0.5">
        <!-- Tabs -->
        <div class="flex space-x-8" id="courseTabs">
            <button onclick="switchTab('enrolled')" id="tab-enrolled"
                class="pb-3 text-sm font-bold border-b-2 border-primary text-primary transition-all">
                Enrolled Courses (3)
            </button>
            <button onclick="switchTab('trial')" id="tab-trial"
                class="pb-3 text-sm font-semibold text-on-surface-variant hover:text-primary border-b-2 border-transparent transition-all">
                Free / Trial Courses
            </button>
        </div>
        <!-- Filters -->
        <div class="flex items-center gap-3 pb-3">
            <div class="relative">
                <select class="appearance-none bg-surface-container-lowest border border-outline-variant/30 text-on-surface text-sm py-2 pl-4 pr-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 cursor-pointer ambient-shadow">
                    <option>All Categories</option>
                    <option>Data Science</option>
                    <option>Design</option>
                    <option>Development</option>
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-sm">expand_more</span>
            </div>
            <button class="bg-surface-container-lowest border border-outline-variant/30 p-2 rounded-lg text-on-surface-variant hover:text-primary hover:border-primary/50 transition-colors ambient-shadow">
                <span class="material-symbols-outlined text-sm">filter_list</span>
            </button>
        </div>
    </div>
</div>

<!-- Enrolled Courses Grid -->
<div id="panel-enrolled" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 flex-1">

    <!-- Course Card 1 -->
    <div class="glass-panel glass-panel-hover rounded-2xl overflow-hidden flex flex-col h-full bg-surface-container-lowest">
        <div class="relative h-48 w-full shrink-0">
            <img alt="Data Science Fundamentals"
                 class="w-full h-full object-cover"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuA2FNzrWpPO-RRak3gCR120k-gE4X_DPoIZFl4ZJh-AfOoCvZ51mhvYEq4mw7YVZkiluRbaDhiP8JE7_FYeoos6mVJO9KYWXw6xMsHY1_ZBTcip-l3SwFs_wngG7t7lS7Rme6wMb1Ol_AhgjmtPYetYLZPdxb-25Zz1Si-CZYWTtc2dSou8_ebYkP-vvht5eJBXIq3oYJPQ0M-Lx987hArXRMZIOEvqkhHbh8n3AIsAF4zUjUdv8ltJ"/>
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-60"></div>
            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-lg text-xs text-primary font-semibold tracking-wide shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined" style="font-size:16px;">bar_chart</span>
                Data Science
            </div>
        </div>
        <div class="p-6 flex flex-col flex-1">
            <h3 class="text-on-surface mb-3 leading-tight line-clamp-2" style="font-size:20px;line-height:28px;font-weight:600;">Advanced Machine Learning &amp; AI Integration</h3>
            <p class="text-on-surface-variant mb-6 line-clamp-2 flex-1 text-sm leading-relaxed">Master the principles of machine learning algorithms and learn how to integrate AI seamlessly into enterprise applications.</p>
            <div class="mt-auto">
                <div class="flex justify-between items-center mb-2.5">
                    <span class="text-outline text-xs font-medium">Course Progress</span>
                    <span class="text-primary text-xs font-bold">45%</span>
                </div>
                <div class="w-full bg-surface-variant/50 rounded-full h-2 mb-6 overflow-hidden">
                    <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: 45%"></div>
                </div>
                <a href="{{ route('student.chapters', ['courseId' => 1]) }}"
                   class="w-full btn-primary-gradient text-on-primary text-sm font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                    Continue Learning
                    <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Course Card 2 -->
    <div class="glass-panel glass-panel-hover rounded-2xl overflow-hidden flex flex-col h-full bg-surface-container-lowest">
        <div class="relative h-48 w-full shrink-0">
            <img alt="UI/UX Masterclass"
                 class="w-full h-full object-cover"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuBw3-LfauZXpQFPRBw0Ug5MXWr62WnkrxelPrnRnWbzBW1OHIh4yFI9m_QNYtnCr0ujcQzssV5FIiKUgTWTv5yXuKwJHA6nHNVHyyfwJjqjaadzXd-Wriw_pIu50SzoaXqAanUYelFPsAI6MvuOtTewpYxbt6uxnqy3Pqhl0empT4SUZgNcHbkfezZvcOBhGea7fMB1f-3OwruaYeCzt7XHzrQ6Vz2VK-KMjiq4tVBY7mUryyx6XqVk"/>
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-60"></div>
            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-lg text-xs text-on-secondary-container font-semibold tracking-wide shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined" style="font-size:16px;">design_services</span>
                Design
            </div>
        </div>
        <div class="p-6 flex flex-col flex-1">
            <h3 class="text-on-surface mb-3 leading-tight line-clamp-2" style="font-size:20px;line-height:28px;font-weight:600;">UI/UX Masterclass: From Concept to Prototype</h3>
            <p class="text-on-surface-variant mb-6 line-clamp-2 flex-1 text-sm leading-relaxed">A comprehensive guide to modern interface design, user psychology, and high-fidelity prototyping using industry-standard tools.</p>
            <div class="mt-auto">
                <div class="flex justify-between items-center mb-2.5">
                    <span class="text-outline text-xs font-medium">Course Progress</span>
                    <span class="text-primary text-xs font-bold">82%</span>
                </div>
                <div class="w-full bg-surface-variant/50 rounded-full h-2 mb-6 overflow-hidden">
                    <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: 82%"></div>
                </div>
                <a href="{{ route('student.chapters', ['courseId' => 2]) }}"
                   class="w-full btn-primary-gradient text-on-primary text-sm font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                    Continue Learning
                    <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Course Card 3 -->
    <div class="glass-panel glass-panel-hover rounded-2xl overflow-hidden flex flex-col h-full bg-surface-container-lowest">
        <div class="relative h-48 w-full shrink-0">
            <img alt="Full-Stack Cloud Architecture"
                 class="w-full h-full object-cover"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuBYRrMkbiNo09_aDUoeuDMfRyT5mCCVZYLps2m72JZVVOLhkMbRkPrWnLvNO7__D8wif2eIOAp3QSm7FPZGd__VWa80MOU6jzGWIYB_dvccJgRAwtx6sDqWTJtLOAzFzLwgDsOmjWFRuBmsgqUh1gcavXx-oQeFrQt2ru13NnyLr9e9HU-PbZ7-Q1eGxUEMgou1y0hZ9KFEB2BEubf0jeyiZYRPOQr6kKQDTGsE0PQAO8dWJFyUseeC"/>
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-60"></div>
            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-lg text-xs text-on-tertiary-container font-semibold tracking-wide shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined" style="font-size:16px;">code</span>
                Development
            </div>
        </div>
        <div class="p-6 flex flex-col flex-1">
            <h3 class="text-on-surface mb-3 leading-tight line-clamp-2" style="font-size:20px;line-height:28px;font-weight:600;">Full-Stack Cloud Architecture</h3>
            <p class="text-on-surface-variant mb-6 line-clamp-2 flex-1 text-sm leading-relaxed">Build scalable, resilient web applications utilizing modern cloud infrastructure, microservices, and serverless technologies.</p>
            <div class="mt-auto">
                <div class="flex justify-between items-center mb-2.5">
                    <span class="text-outline text-xs font-medium">Course Progress</span>
                    <span class="text-primary text-xs font-bold">15%</span>
                </div>
                <div class="w-full bg-surface-variant/50 rounded-full h-2 mb-6 overflow-hidden">
                    <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: 15%"></div>
                </div>
                <a href="{{ route('student.chapters', ['courseId' => 3]) }}"
                   class="w-full btn-primary-gradient text-on-primary text-sm font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                    Continue Learning
                    <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Free / Trial Courses (hidden by default) -->
<div id="panel-trial" class="hidden col-span-full py-20 flex flex-col items-center justify-center text-center glass-panel rounded-2xl">
    <div class="w-32 h-32 bg-primary/5 rounded-full flex items-center justify-center mb-6 relative">
        <div class="absolute inset-0 bg-primary/10 rounded-full animate-ping opacity-20"></div>
        <span class="material-symbols-outlined text-6xl text-primary" style="font-variation-settings: 'FILL' 1;">explore</span>
    </div>
    <h3 class="text-on-surface mb-3" style="font-size:24px;line-height:32px;font-weight:600;">No trial courses yet</h3>
    <p class="text-on-surface-variant max-w-lg mx-auto mb-8 text-lg leading-relaxed">Looks like you haven't enrolled in any free or trial courses. Discover our wide range of introductory materials to get started.</p>
    <a href="{{ route('student.catalog') }}" class="btn-primary-gradient text-on-primary text-sm font-semibold py-3.5 px-8 rounded-xl transition-all duration-200 inline-flex items-center gap-2">
        Explore Free Courses
        <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
    </a>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(tab) {
        const enrolled = document.getElementById('panel-enrolled');
        const trial    = document.getElementById('panel-trial');
        const tabE     = document.getElementById('tab-enrolled');
        const tabT     = document.getElementById('tab-trial');

        if (tab === 'enrolled') {
            enrolled.classList.remove('hidden');
            trial.classList.add('hidden');
            tabE.classList.add('border-primary','text-primary');
            tabE.classList.remove('border-transparent','text-on-surface-variant');
            tabT.classList.add('border-transparent','text-on-surface-variant');
            tabT.classList.remove('border-primary','text-primary');
        } else {
            trial.classList.remove('hidden');
            trial.classList.add('flex');
            enrolled.classList.add('hidden');
            tabT.classList.add('border-primary','text-primary');
            tabT.classList.remove('border-transparent','text-on-surface-variant');
            tabE.classList.add('border-transparent','text-on-surface-variant');
            tabE.classList.remove('border-primary','text-primary');
        }
    }
</script>
@endpush
