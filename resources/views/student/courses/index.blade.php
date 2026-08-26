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
        box-shadow: 0px 10px 30px rgba(0, 19, 48, 0.08);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    .ambient-shadow {
        box-shadow: 0px 10px 30px rgba(0, 19, 48, 0.08);
    }
    .btn-primary-gradient {
        background: linear-gradient(135deg, #001330, #4f46e5);
        box-shadow: 0px 4px 15px rgba(0, 19, 48, 0.2);
    }
    .btn-primary-gradient:hover {
        background: linear-gradient(135deg, #4f46e5, #001330);
        box-shadow: 0px 6px 20px rgba(0, 19, 48, 0.3);
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
                Enrolled Courses ({{ $courses->count() }})
            </button>
            <button onclick="switchTab('trial')" id="tab-trial"
                class="pb-3 text-sm font-semibold text-on-surface-variant hover:text-primary border-b-2 border-transparent transition-all">
                Free / Trial Courses ({{ $trial->count() }})
            </button>
        </div>
        {{-- Categories come from the courses on the page, so the filter never
             offers something that would return nothing. --}}
        <form method="GET" class="flex items-center gap-3 pb-3">
            <div class="relative">
                <select name="category" onchange="this.form.submit()"
                    class="appearance-none bg-surface-container-lowest border border-outline-variant/30 text-on-surface text-sm py-2 pl-4 pr-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 cursor-pointer ambient-shadow">
                    <option value="">All Categories</option>
                    @foreach($categories as $title)
                        <option value="{{ $title }}" @selected($category === $title)>{{ $title }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-sm">expand_more</span>
            </div>
            @if($category)
                <a href="{{ route('student.courses') }}"
                    class="bg-surface-container-lowest border border-outline-variant/30 p-2 rounded-lg text-on-surface-variant hover:text-primary hover:border-primary/50 transition-colors ambient-shadow flex items-center"
                    title="Clear filter">
                    <span class="material-symbols-outlined text-sm">filter_list_off</span>
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Enrolled Courses Grid -->
<div id="panel-enrolled" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 flex-1">
    @forelse($courses as $course)
        @include('student.courses._card', ['course' => $course, 'progress' => $progress])
    @empty
        <div class="col-span-full py-20 flex flex-col items-center justify-center text-center glass-panel rounded-2xl">
            <div class="w-32 h-32 bg-primary/5 rounded-full flex items-center justify-center mb-6">
                <span class="material-symbols-outlined text-6xl text-primary" style="font-variation-settings: 'FILL' 1;">school</span>
            </div>
            <h3 class="text-on-surface mb-3" style="font-size:24px;line-height:32px;font-weight:600;">
                {{ $category ? 'No courses in ' . $category : 'No courses yet' }}
            </h3>
            <p class="text-on-surface-variant max-w-lg mx-auto mb-8 text-lg leading-relaxed">
                {{ $category
                    ? 'Nothing here under that category — try another, or clear the filter.'
                    : 'Nothing has been published yet. Check the catalog for what is coming.' }}
            </p>
            <a href="{{ $category ? route('student.courses') : route('student.catalog') }}"
                class="btn-primary-gradient text-on-primary text-sm font-semibold py-3.5 px-8 rounded-xl transition-all duration-200 inline-flex items-center gap-2">
                {{ $category ? 'Clear the filter' : 'Browse the catalog' }}
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>
    @endforelse
</div>

<!-- Free / Trial Courses -->
<div id="panel-trial" class="hidden">
    @if($trial->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($trial as $course)
                @include('student.courses._card', ['course' => $course, 'progress' => $progress])
            @endforeach
        </div>
    @else
        <div class="py-20 flex flex-col items-center justify-center text-center glass-panel rounded-2xl">
            <div class="w-32 h-32 bg-primary/5 rounded-full flex items-center justify-center mb-6 relative">
                <div class="absolute inset-0 bg-primary/10 rounded-full animate-ping opacity-20"></div>
                <span class="material-symbols-outlined text-6xl text-primary" style="font-variation-settings: 'FILL' 1;">explore</span>
            </div>
            <h3 class="text-on-surface mb-3" style="font-size:24px;line-height:32px;font-weight:600;">No trial courses yet</h3>
            <p class="text-on-surface-variant max-w-lg mx-auto mb-8 text-lg leading-relaxed">Every course here is on a subscription plan. Discover our introductory materials in the catalog to get started.</p>
            <a href="{{ route('student.catalog') }}" class="btn-primary-gradient text-on-primary text-sm font-semibold py-3.5 px-8 rounded-xl transition-all duration-200 inline-flex items-center gap-2">
                Explore Free Courses
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </a>
        </div>
    @endif
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
            enrolled.classList.add('hidden');
            tabT.classList.add('border-primary','text-primary');
            tabT.classList.remove('border-transparent','text-on-surface-variant');
            tabE.classList.add('border-transparent','text-on-surface-variant');
            tabE.classList.remove('border-primary','text-primary');
        }
    }
</script>
@endpush
