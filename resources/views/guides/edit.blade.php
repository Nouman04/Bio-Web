@extends('layouts.app')

@section('title', 'Edit Guide')
@section('meta-description', 'Update an existing guide and its curriculum content.')

@section('page-title', 'Edit Guide')
@section('page-subtitle', 'Update an existing guide and its curriculum content.')

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
    {{-- Breadcrumbs / Back Link --}}
    <div class="mb-6">
        <a class="inline-flex items-center gap-2 text-primary hover:text-primary/80 transition-colors text-sm font-semibold group" href="{{ route('guides') }}">
            <i class="fa-solid fa-arrow-left text-xs group-hover:-translate-x-1 transition-transform"></i>
            Back to Guides
        </a>
    </div>

    {{-- Page Header --}}
    <div class="mb-8">
        <h2 class="text-2xl md:text-4xl font-bold text-on-surface dark:text-white">Edit Guide</h2>
        <p class="text-on-surface-variant dark:text-slate-400 mt-2">{{ $guide['title'] }}</p>
    </div>

    {{-- Form Card (Glassmorphism) --}}
    <div class="glass-panel dark:bg-slate-800/80 rounded-xl flex flex-col relative border-outline-variant/40 dark:border-slate-700 bg-white/50 dark:bg-slate-900/50">
        <form action="{{ route('guides.update', $guide['id']) }}" method="POST" class="p-6 md:p-8 flex flex-col gap-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Guide Title --}}
                <div class="md:col-span-2 flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="title">Guide Title</label>
                    <input class="bg-white dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary w-full shadow-sm outline-none transition-all" id="title" name="title" placeholder="e.g., Introduction to Advanced Quantum Mechanics" type="text" value="{{ old('title', $guide['title']) }}" required>
                </div>

                {{-- Slug --}}
                <div class="md:col-span-2 flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="slug">Slug</label>
                    <div class="flex items-stretch rounded-lg border border-outline-variant/60 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden focus-within:border-primary focus-within:ring-1 focus-within:ring-primary transition-all">
                        <span class="hidden sm:flex items-center px-4 text-sm text-on-surface-variant dark:text-slate-400 bg-surface-container-low dark:bg-slate-800 border-r border-outline-variant/60 dark:border-slate-700 select-none">/guides/</span>
                        <input class="flex-1 bg-transparent px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 outline-none border-none focus:ring-0" id="slug" name="slug" placeholder="introduction-to-advanced-quantum-mechanics" type="text" value="{{ old('slug', $guide['slug']) }}" required>
                    </div>
                    <p class="text-xs text-on-surface-variant dark:text-slate-400">Changing the slug changes the guide's public URL and breaks existing links.</p>
                </div>

                {{-- Chapter Dropdown --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="chapter">Chapter</label>
                    <div class="relative">
                        <select class="bg-white dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary w-full shadow-sm outline-none transition-all appearance-none" id="chapter" name="chapter" required>
                            <option disabled value="">Select a Chapter</option>
                            <option value="ch1" {{ old('chapter', $guide['chapter']) === 'ch1' ? 'selected' : '' }}>Chapter 1: Foundations</option>
                            <option value="ch2" {{ old('chapter', $guide['chapter']) === 'ch2' ? 'selected' : '' }}>Chapter 2: Core Principles</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-xs"></i>
                    </div>
                </div>

                {{-- Topic Dropdown --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="topic">Topic</label>
                    <div class="relative">
                        <select class="bg-white dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary w-full shadow-sm outline-none transition-all appearance-none" id="topic" name="topic" required>
                            <option disabled value="">Select a Topic</option>
                            <option value="t1" {{ old('topic', $guide['topic']) === 't1' ? 'selected' : '' }}>Theoretical Frameworks</option>
                            <option value="t2" {{ old('topic', $guide['topic']) === 't2' ? 'selected' : '' }}>Practical Case Studies</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-xs"></i>
                    </div>
                </div>

                {{-- Guide Type Dropdown --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="type">Guide Type</label>
                    <div class="relative">
                        <select class="bg-white dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary w-full shadow-sm outline-none transition-all appearance-none" id="type" name="type" required>
                            <option value="theory" {{ old('type', $guide['type']) === 'theory' ? 'selected' : '' }}>Theory Guide</option>
                            <option value="atp" {{ old('type', $guide['type']) === 'atp' ? 'selected' : '' }}>ATP Guide</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-xs"></i>
                    </div>
                </div>
            </div>

            {{-- Rich Text Editor Area --}}
            <div class="flex flex-col gap-2 mt-2 flex-1 min-h-[300px]">
                <label class="text-sm font-semibold text-on-surface dark:text-slate-200">Guide Content</label>
                <textarea name="content" data-quill data-quill-height="320px" placeholder="Start writing the guide content here..." required>{{ old('content', $guide['content']) }}</textarea>
            </div>

            {{-- Linked Questions --}}
            @include('partials.question-widget', ['qwFieldName' => 'question_ids', 'qwLabel' => 'Linked Questions (Optional)'])

            {{-- Footer Actions --}}
            <div class="flex items-center justify-end gap-4 mt-4 pt-6 border-t border-outline-variant/40 dark:border-slate-700">
                <a href="{{ route('guides') }}" class="px-6 py-2.5 rounded-full text-sm font-semibold border border-outline-variant/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors cursor-pointer inline-block">
                    Cancel
                </a>
                <button class="px-6 py-2.5 rounded-full text-sm font-semibold bg-gradient-to-r from-primary to-primary-container text-white shadow-md hover:shadow-lg hover:-translate-y-0.5 flex items-center gap-2 cursor-pointer transition-all" type="submit">
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

            // An existing guide already has a slug, so never auto-track the title here —
            // only normalise whatever the user types.
            slugInput.addEventListener('blur', () => {
                slugInput.value = toSlug(slugInput.value) || toSlug(titleInput.value);
            });
        });
    </script>
@endpush
