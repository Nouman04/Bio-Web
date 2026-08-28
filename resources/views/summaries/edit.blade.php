@extends('layouts.app')

@section('title', 'Edit Summary')
@section('meta-description', 'Update an existing learning summary.')

@section('page-title', 'Edit Summary')
@section('page-subtitle', 'Update an existing learning summary.')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
</style>
@endpush

@section('content')
    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('summaries') }}">Summaries</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Edit</span>
    </div>

    {{-- Page Header --}}
    <div class="mb-8">
        <h2 class="text-2xl md:text-3xl font-bold text-on-surface dark:text-white">Edit Summary</h2>
        <p class="text-on-surface-variant dark:text-slate-400 mt-2">{{ $summary['title'] }}</p>
    </div>

    <div class="glass-card dark:bg-slate-800/80 rounded-2xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
        <form action="{{ route('summaries.update', $summary['id']) }}" method="POST" class="p-6 md:p-8 flex flex-col gap-6">
            @csrf
            @method('PUT')

            {{-- Metadata Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="chapter">Chapter</label>
                    <select id="chapter" name="chapter" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        <option value="">Select Chapter</option>
                        <option value="ch1" {{ old('chapter', $summary['chapter']) === 'ch1' ? 'selected' : '' }}>Chapter 1: Introduction to Biology</option>
                        <option value="ch2" {{ old('chapter', $summary['chapter']) === 'ch2' ? 'selected' : '' }}>Chapter 2: Cell Structure</option>
                        <option value="ch3" {{ old('chapter', $summary['chapter']) === 'ch3' ? 'selected' : '' }}>Chapter 3: Genetics</option>
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="topic">Topic</label>
                    <select id="topic" name="topic" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                        <option value="">Select Topic</option>
                        <option value="t1" {{ old('topic', $summary['topic']) === 't1' ? 'selected' : '' }}>1.1 What is Life?</option>
                        <option value="t2" {{ old('topic', $summary['topic']) === 't2' ? 'selected' : '' }}>1.2 Scientific Method</option>
                        <option value="t3" {{ old('topic', $summary['topic']) === 't3' ? 'selected' : '' }}>2.1 Organelles</option>
                    </select>
                </div>
            </div>

            {{-- Title --}}
            <div class="flex flex-col gap-2">
                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="title">Summary Title</label>
                <input id="title" name="title" type="text" value="{{ old('title', $summary['title']) }}" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 dark:border-slate-700 rounded-lg py-2 px-3 text-sm text-on-surface dark:text-slate-200 placeholder:text-outline focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all" placeholder="Enter summary title..." required>
            </div>

            {{-- Slug --}}
            <div class="flex flex-col gap-2">
                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="slug">Slug</label>
                <div class="flex items-stretch rounded-lg border border-outline-variant/50 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
                    <span class="hidden sm:flex items-center px-3 text-sm text-on-surface-variant dark:text-slate-400 bg-surface-container-low dark:bg-slate-900 border-r border-outline-variant/50 dark:border-slate-700 select-none">/summaries/</span>
                    <input id="slug" name="slug" type="text" value="{{ old('slug', $summary['slug']) }}" class="flex-1 bg-transparent px-3 py-2 text-sm text-on-surface dark:text-slate-200 outline-none border-none focus:ring-0" placeholder="newtonian-mechanics-summary" required>
                </div>
                <p class="text-xs text-on-surface-variant dark:text-slate-400">Changing the slug changes the summary's public URL and breaks existing links.</p>
            </div>

            {{-- Linked Questions --}}
            @include('partials.question-widget', ['qwFieldName' => 'question_ids', 'qwLabel' => 'Linked Questions (Optional)'])

            {{-- Content Editor --}}
            <div class="flex flex-col gap-2">
                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Content</label>
                <textarea name="content" data-quill data-quill-height="250px" placeholder="Start writing your summary content here..." required>{{ old('content', $summary['content']) }}</textarea>
            </div>

            {{-- Footer Actions --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-outline-variant/30 dark:border-slate-700">
                <a href="{{ route('summaries') }}" class="px-6 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-variant dark:hover:bg-slate-700 transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-2 rounded-full text-sm font-semibold text-white bg-primary hover:bg-primary-container shadow-md hover:shadow-lg transition-all flex items-center gap-2">
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
            const titleInput = document.getElementById('title');
            const slugInput = document.getElementById('slug');
            if (!titleInput || !slugInput) return;

            function toSlug(value) {
                return value
                    .toLowerCase()
                    .normalize('NFD').replace(/\p{M}/gu, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            // An existing summary already has a slug, so only normalise what is typed.
            slugInput.addEventListener('blur', () => {
                slugInput.value = toSlug(slugInput.value) || toSlug(titleInput.value);
            });
        });
    </script>
@endpush
