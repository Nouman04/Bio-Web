@extends('layouts.app')

@section('title', 'Edit Diagram')
@section('meta-description', 'Update a diagram asset, its classification and linked questions.')

@section('page-title', 'Edit Diagram')
@section('page-subtitle', 'Update a diagram asset, its classification and linked questions.')

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
            <a href="{{ route('diagrams') }}" class="hover:text-primary transition-colors">Media Library</a>
            <i class="fa-solid fa-chevron-right text-[10px]"></i>
            <span class="text-primary font-semibold">Edit Diagram</span>
        </div>
        <h2 class="text-2xl md:text-3xl font-bold text-on-surface dark:text-white">Edit Diagram</h2>
        <p class="text-sm text-on-surface-variant dark:text-slate-400">{{ $diagram['title'] }}</p>
    </header>

    {{-- Form Layout (Bento Grid Style) --}}
    <form action="{{ route('diagrams.update', $diagram['id']) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        @method('PUT')

        {{-- Left Column: Primary Content (8 cols) --}}
        <div class="lg:col-span-8 flex flex-col gap-6">
            {{-- Current Image + Replace Card --}}
            <div class="bg-surface-container-lowest/80 dark:bg-slate-800/80 backdrop-blur-md border border-white/40 dark:border-slate-700 shadow-sm rounded-xl p-6 flex flex-col gap-4">
                <label class="text-sm font-semibold text-on-surface dark:text-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-image text-primary"></i>
                    Diagram Source
                </label>

                {{-- Current file preview --}}
                <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-xl bg-surface-container-low/60 dark:bg-slate-900/50 border border-outline-variant/40 dark:border-slate-700">
                    <div class="w-28 h-28 rounded-lg overflow-hidden bg-surface-container dark:bg-slate-800 flex items-center justify-center shrink-0">
                        @if($diagram['has_image'] && $diagram['url'])
                            <img id="diagram-preview" src="{{ $diagram['url'] }}" alt="{{ $diagram['title'] }}" class="w-full h-full object-cover">
                        @else
                            <i class="fa-regular fa-image text-3xl text-outline dark:text-slate-500"></i>
                        @endif
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <p class="text-sm font-semibold text-on-surface dark:text-slate-200">Current file</p>
                        <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-1" id="diagram-file-meta">{{ $diagram['meta'] }}</p>
                        <p class="text-xs text-outline dark:text-slate-500 mt-2">Leave the upload area empty to keep this file.</p>
                    </div>
                </div>

                {{-- Drag and Drop Zone (optional replacement) --}}
                <div class="border-2 border-dashed border-outline-variant dark:border-slate-600 bg-surface-container-low/50 dark:bg-slate-900/50 hover:border-primary dark:hover:border-primary hover:bg-primary/5 transition-colors rounded-xl flex flex-col items-center justify-center p-10 cursor-pointer relative overflow-hidden group">
                    <div class="w-14 h-14 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-arrows-rotate text-2xl"></i>
                    </div>
                    <h3 class="text-base font-bold text-on-surface dark:text-white mb-1 text-center">Click or drag a file here to replace the diagram</h3>
                    <p class="text-xs font-medium text-outline dark:text-slate-500 mt-2">JPG, PNG, GIF up to 10MB</p>
                    <input id="diagram-image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" type="file" name="image"/>
                </div>
            </div>

            {{-- Details Card --}}
            <div class="bg-surface-container-lowest/80 dark:bg-slate-800/80 backdrop-blur-md border border-white/40 dark:border-slate-700 shadow-sm rounded-xl p-6 flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="title">Title <span class="text-error">*</span></label>
                    <input class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] placeholder:text-outline/70 text-on-surface dark:text-slate-200" id="title" name="title" placeholder="Enter a descriptive title for this diagram" type="text" value="{{ old('title', $diagram['title']) }}" required/>
                </div>
                <div class="flex flex-col gap-2 mt-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="description">Content / Description</label>
                    {{-- Faux Rich Text Editor --}}
                    <textarea id="description" name="description" data-quill data-quill-height="180px" placeholder="Add context, alt text, or usage notes here...">{{ old('description', $diagram['description']) }}</textarea>
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
                            <option disabled value="">Select Chapter...</option>
                            <option value="ch1" {{ old('chapter', $diagram['chapter']) === 'ch1' ? 'selected' : '' }}>Chapter 1: Foundations</option>
                            <option value="ch2" {{ old('chapter', $diagram['chapter']) === 'ch2' ? 'selected' : '' }}>Chapter 2: Advanced Mechanics</option>
                            <option value="ch3" {{ old('chapter', $diagram['chapter']) === 'ch3' ? 'selected' : '' }}>Chapter 3: Real-world Applications</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-outline text-xs pointer-events-none"></i>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="topicSelect">Specific Topic</label>
                    <div class="relative">
                        <select class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-3 text-sm appearance-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] cursor-pointer text-on-surface dark:text-slate-200" id="topicSelect" name="topic">
                            <option disabled value="">Select Topic...</option>
                            <option value="t1" {{ old('topic', $diagram['topic']) === 't1' ? 'selected' : '' }}>1.1 Introduction</option>
                            <option value="t2" {{ old('topic', $diagram['topic']) === 't2' ? 'selected' : '' }}>1.2 Core Concepts</option>
                            <option value="t3" {{ old('topic', $diagram['topic']) === 't3' ? 'selected' : '' }}>1.3 Summary</option>
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
                @include('partials.question-widget', ['qwFieldName' => 'question_ids', 'qwLabel' => 'Associate with Questions (Optional)'])
                <p class="text-xs font-medium text-outline dark:text-slate-500 -mt-2">Linking a diagram makes it available as a resource during those assessments.</p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('diagrams') }}" class="px-6 py-2.5 rounded-full border border-outline/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 text-sm font-semibold hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors inline-block text-center">
                    Cancel
                </a>
                <button class="px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md shadow-primary/20 hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 cursor-pointer" type="submit">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    Save Changes
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fileInput = document.getElementById('diagram-image');
            const preview = document.getElementById('diagram-preview');
            const meta = document.getElementById('diagram-file-meta');

            fileInput?.addEventListener('change', () => {
                const file = fileInput.files[0];
                if (!file) return;

                if (preview) preview.src = URL.createObjectURL(file);
                if (meta) meta.textContent = `${file.name} • ${(file.size / 1024 / 1024).toFixed(2)} MB (pending upload)`;
            });
        });
    </script>
@endpush
