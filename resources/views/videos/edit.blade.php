@extends('layouts.app')

@section('title', 'Edit Video Lesson')
@section('meta-description', 'Update an existing video module in the curriculum.')

@section('page-title', 'Edit Video Lesson')
@section('page-subtitle', 'Update an existing video module in the curriculum.')

@push('styles')
<style>
    .glass-panel {
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
    .hover-ambient-shadow:hover {
        box-shadow: 0px 10px 30px rgba(0, 19, 48, 0.08);
    }
</style>
@endpush

@section('content')
    {{-- Header Section --}}
    <div class="flex justify-between items-center z-20 mb-6">
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2 text-xs font-medium text-outline dark:text-slate-400">
                <a href="{{ route('videos') }}" class="hover:text-primary transition-colors flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    Back to Video Lessons
                </a>
            </div>
            <h2 class="text-2xl md:text-3xl font-bold text-on-surface dark:text-white">Edit Video Lesson</h2>
            <p class="text-sm text-on-surface-variant dark:text-slate-400">{{ $video['title'] }}</p>
        </div>
    </div>

    {{-- Form Layout --}}
    <div class="glass-panel dark:bg-slate-800/80 rounded-xl p-8 shadow-sm hover-ambient-shadow transition-all duration-300 bg-surface-container-lowest dark:border-slate-700">
        <form action="{{ route('videos.update', $video['id']) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            {{-- Lesson Details Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2 flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="title">Lesson Title <span class="text-error">*</span></label>
                    <input class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] text-on-surface dark:text-slate-200 placeholder:text-outline/70 transition-all" id="title" name="title" placeholder="e.g., Introduction to Advanced Calculus" type="text" value="{{ old('title', $video['title']) }}" required/>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="chapter">Chapter</label>
                    <div class="relative">
                        <select class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm appearance-none focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] text-on-surface dark:text-slate-200 transition-all" id="chapter" name="chapter">
                            <option disabled value="">Select a chapter</option>
                            <option value="ch1" {{ old('chapter', $video['chapter']) === 'ch1' ? 'selected' : '' }}>Chapter 1: Foundations</option>
                            <option value="ch2" {{ old('chapter', $video['chapter']) === 'ch2' ? 'selected' : '' }}>Chapter 2: Core Concepts</option>
                            <option value="ch3" {{ old('chapter', $video['chapter']) === 'ch3' ? 'selected' : '' }}>Chapter 3: Advanced Applications</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-on-surface-variant">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="topic">Topic</label>
                    <div class="relative">
                        <select class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm appearance-none focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] text-on-surface dark:text-slate-200 transition-all" id="topic" name="topic">
                            <option disabled value="">Select a topic</option>
                            <option value="t1" {{ old('topic', $video['topic']) === 't1' ? 'selected' : '' }}>1.1 Introduction</option>
                            <option value="t2" {{ old('topic', $video['topic']) === 't2' ? 'selected' : '' }}>1.2 Basic Theories</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-on-surface-variant">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Video Source Section --}}
            <div class="flex flex-col gap-4 mt-4">
                <h3 class="text-base font-bold text-on-surface dark:text-white border-b border-surface-variant dark:border-slate-700 pb-2">Video Source</h3>

                {{-- Current file --}}
                <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-xl bg-surface-container-low/60 dark:bg-slate-900/50 border border-outline-variant/40 dark:border-slate-700">
                    <div class="relative w-40 h-24 rounded-lg overflow-hidden bg-surface-container dark:bg-slate-800 flex items-center justify-center shrink-0">
                        @if($video['thumbnail'])
                            <img src="{{ $video['thumbnail'] }}" alt="{{ $video['title'] }}" class="w-full h-full object-cover">
                            <span class="absolute inset-0 flex items-center justify-center bg-black/30">
                                <i class="fa-solid fa-play text-white text-xl"></i>
                            </span>
                        @else
                            <i class="fa-solid fa-film text-3xl text-outline dark:text-slate-500"></i>
                        @endif
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <p class="text-sm font-semibold text-on-surface dark:text-slate-200">Current video</p>
                        <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-1" id="video-file-meta">{{ $video['meta'] }}</p>
                        <p class="text-xs text-outline dark:text-slate-500 mt-2">Leave both fields below untouched to keep this video.</p>
                    </div>
                </div>

                <div class="border-2 border-dashed border-outline-variant/60 dark:border-slate-600 bg-surface-container-low/50 dark:bg-slate-900/50 hover:border-primary dark:hover:border-primary hover:bg-primary/5 transition-colors rounded-xl flex flex-col items-center justify-center p-10 cursor-pointer relative overflow-hidden group min-h-[200px]">
                    <div class="w-14 h-14 rounded-full bg-primary-container/20 text-primary flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-arrows-rotate text-2xl"></i>
                    </div>
                    <h4 class="text-base font-bold text-on-surface dark:text-white mb-1">Click to browse or drag a video here to replace it</h4>
                    <p class="text-sm text-on-surface-variant dark:text-slate-400 max-w-md text-center">Supports MP4, WebM, or Ogg up to 500MB.</p>
                    <input id="video-file" accept="video/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" type="file" name="video_file"/>
                </div>

                <div class="flex items-center gap-4 py-2">
                    <div class="h-px bg-outline-variant/40 dark:border-slate-700 flex-1"></div>
                    <span class="text-xs font-medium text-on-surface-variant dark:text-slate-500 uppercase tracking-wider">OR ENTER URL</span>
                    <div class="h-px bg-outline-variant/40 dark:border-slate-700 flex-1"></div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="video_url">External Video Link</label>
                    <div class="relative">
                        <i class="fa-solid fa-link absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm"></i>
                        <input class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] text-on-surface dark:text-slate-200 placeholder:text-outline/70 transition-all" id="video_url" name="video_url" placeholder="https://youtube.com/... or https://vimeo.com/..." type="url" value="{{ old('video_url', $video['video_url']) }}"/>
                    </div>
                </div>
            </div>

            {{-- Supplementary Info --}}
            <div class="flex flex-col gap-2 mt-4">
                <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="description">Lesson Description / Transcript (Optional)</label>
                <textarea id="description" name="description" data-quill data-quill-height="180px" placeholder="Provide context, key takeaways, or paste transcript here...">{{ old('description', $video['description']) }}</textarea>
            </div>

            {{-- Linked Questions --}}
            <div class="flex flex-col gap-2 mt-2">
                @include('partials.question-widget', ['qwFieldName' => 'question_ids', 'qwLabel' => 'Linked Questions (Optional)'])
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-surface-variant dark:border-slate-700 mt-2">
                <a href="{{ route('videos') }}" class="px-6 py-2.5 rounded-full border border-outline/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 text-sm font-semibold hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </a>
                <button class="px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md shadow-primary/20 hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 cursor-pointer" type="submit">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fileInput = document.getElementById('video-file');
            const meta = document.getElementById('video-file-meta');

            fileInput?.addEventListener('change', () => {
                const file = fileInput.files[0];
                if (!file || !meta) return;

                meta.textContent = `${file.name} • ${(file.size / 1024 / 1024).toFixed(1)} MB (pending upload)`;
            });
        });
    </script>
@endpush
