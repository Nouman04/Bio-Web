@extends('layouts.app')

@section('title', 'Add New Image')
@section('meta-description', 'Upload and configure a new image asset for curriculum content or question banks.')

@section('page-title', 'Add New Image')
@section('page-subtitle', 'Upload and configure a new image asset for curriculum content or question banks.')

@push('styles')
<style>
    .glass-panel {
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
</style>
@endpush

@section('content')
    {{-- Header Section --}}
    <header class="flex flex-col gap-2 mb-6">
        <div class="flex items-center gap-2 text-xs font-medium text-outline dark:text-slate-400">
            <span>Content</span>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <span>Media Library</span>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <span class="text-primary font-semibold">Add New</span>
        </div>
        <h2 class="text-2xl md:text-3xl font-bold text-on-surface dark:text-white">Add New Image</h2>
        <p class="text-sm text-on-surface-variant dark:text-slate-400">Upload and configure a new image asset for curriculum content or question banks.</p>
    </header>

    {{-- Form Layout (Bento Grid Style) --}}
    <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        
        {{-- Left Column: Primary Content (8 cols) --}}
        <div class="lg:col-span-8 flex flex-col gap-6">
            {{-- Upload Area Card --}}
            <div class="bg-surface-container-lowest/80 dark:bg-slate-800/80 backdrop-blur-md border border-white/40 dark:border-slate-700 shadow-sm rounded-xl p-6 flex flex-col gap-4">
                <label class="text-sm font-semibold text-on-surface dark:text-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-image text-primary"></i>
                    Image Source <span class="text-error">*</span>
                </label>
                {{-- Drag and Drop Zone --}}
                <div class="border-2 border-dashed border-outline-variant dark:border-slate-600 bg-surface-container-low/50 dark:bg-slate-900/50 hover:border-primary dark:hover:border-primary hover:bg-primary/5 transition-colors rounded-xl flex flex-col items-center justify-center p-12 min-h-[320px] cursor-pointer relative overflow-hidden group">
                    <div class="w-16 h-16 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-on-surface dark:text-white mb-2 text-center">Click or drag file to this area to upload</h3>
                    <p class="text-sm text-on-surface-variant dark:text-slate-400 text-center max-w-sm">Support for a single or bulk upload. Strictly prohibit from uploading company data or other band files.</p>
                    <p class="text-xs font-medium text-outline dark:text-slate-500 mt-4">JPG, PNG, GIF up to 10MB</p>
                    <input accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" type="file" name="image" required/>
                </div>
            </div>

            {{-- Details Card --}}
            <div class="bg-surface-container-lowest/80 dark:bg-slate-800/80 backdrop-blur-md border border-white/40 dark:border-slate-700 shadow-sm rounded-xl p-6 flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="title">Title <span class="text-error">*</span></label>
                    <input class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] placeholder:text-outline/70 text-on-surface dark:text-slate-200" id="title" name="title" placeholder="Enter a descriptive title for this image" type="text" required/>
                </div>
                <div class="flex flex-col gap-2 mt-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="description">Content / Description</label>
                    <textarea id="description" name="description" data-quill data-quill-height="180px" placeholder="Add context, alt text, or usage notes here..."></textarea>
                </div>
            </div>
        </div>

        {{-- Right Column: Metadata & Actions (4 cols) --}}
        <div class="lg:col-span-4 flex flex-col gap-6">
            {{-- Classification Card --}}
            <div class="bg-surface-container-lowest/80 dark:bg-slate-800/80 backdrop-blur-md border border-white/40 dark:border-slate-700 shadow-sm rounded-xl p-6 flex flex-col gap-4">
                <h3 class="text-lg font-bold text-on-surface dark:text-white border-b border-surface-variant dark:border-slate-700 pb-3 mb-1">Classification</h3>
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="chapterSelect">Curriculum Chapter</label>
                    <div class="relative">
                        <select class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-3 text-sm appearance-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] cursor-pointer text-on-surface dark:text-slate-200" id="chapterSelect" name="chapter">
                            <option disabled selected value="">Select Chapter...</option>
                            <option value="ch1">Chapter 1: Foundations</option>
                            <option value="ch2">Chapter 2: Advanced Mechanics</option>
                            <option value="ch3">Chapter 3: Real-world Applications</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-outline text-xs pointer-events-none"></i>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="topicSelect">Specific Topic</label>
                    <div class="relative">
                        <select class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-3 text-sm appearance-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] cursor-pointer text-on-surface dark:text-slate-200" id="topicSelect" name="topic">
                            <option disabled selected value="">Select Topic...</option>
                            <option value="t1">1.1 Introduction</option>
                            <option value="t2">1.2 Core Concepts</option>
                            <option value="t3">1.3 Summary</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-outline text-xs pointer-events-none"></i>
                    </div>
                </div>
            </div>

            {{-- Integration Card --}}
            <div class="bg-surface-container-lowest/80 dark:bg-slate-800/80 backdrop-blur-md border border-white/40 dark:border-slate-700 shadow-sm rounded-xl p-6 flex flex-col gap-4">
                <h3 class="text-lg font-bold text-on-surface dark:text-white border-b border-surface-variant dark:border-slate-700 pb-3 mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-link text-tertiary-container"></i>
                    Question Bank
                </h3>
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="questionSearch">Associate with Question (Optional)</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm"></i>
                        <input class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg pl-10 pr-4 py-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] placeholder:text-outline/70 text-on-surface dark:text-slate-200" id="questionSearch" name="question_id" placeholder="Search ID or keywords..." type="text"/>
                    </div>
                    <p class="text-xs font-medium text-outline dark:text-slate-500 mt-1">Linking an image makes it available as a resource during assessments.</p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('images') }}" class="px-6 py-2.5 rounded-full border border-outline/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 text-sm font-semibold hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors inline-block text-center">
                    Cancel
                </a>
                <button class="px-6 py-2.5 rounded-full bg-primary text-white text-sm font-semibold shadow-md shadow-primary/20 hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 cursor-pointer" type="submit">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    Save Image
                </button>
            </div>
        </div>
    </form>
@endsection
