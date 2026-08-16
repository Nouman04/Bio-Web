@extends('layouts.app')

@section('title', 'Edit Topic')
@section('meta-description', 'Update a topic, its content and its attachments.')

@section('page-title', 'Edit Topic')
@section('page-subtitle', 'Update a topic, its content and its attachments.')

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
    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('topics') }}">Topics</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Edit</span>
    </div>

    {{-- Page Header --}}
    <div class="mb-8">
        <h2 class="text-2xl md:text-3xl font-bold text-on-surface dark:text-white">Edit Topic</h2>
        <p class="text-on-surface-variant dark:text-slate-400 mt-2">{{ $topic['title'] }}</p>
    </div>

    <div class="glass-panel dark:bg-slate-800/80 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
        <form action="{{ route('topics.update', $topic['id']) }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 flex flex-col gap-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Chapter --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="chapter_id">Chapter</label>
                    <select id="chapter_id" name="chapter_id" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        <option value="">Select a chapter</option>
                        <option value="ch1" {{ old('chapter_id', $topic['chapter']) === 'ch1' ? 'selected' : '' }}>Chapter 1: Fundamentals</option>
                        <option value="ch2" {{ old('chapter_id', $topic['chapter']) === 'ch2' ? 'selected' : '' }}>Chapter 2: Advanced Mechanics</option>
                    </select>
                </div>

                {{-- Topic Name --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="title">Topic Name</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $topic['title']) }}" class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="Enter topic name" required>
                </div>
            </div>

            {{-- Content --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400" for="content">Content</label>
                <textarea id="content" name="content" data-quill data-quill-height="220px" placeholder="Enter topic content...">{{ old('content', $topic['content']) }}</textarea>
            </div>

            {{-- Existing attachments --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Current Attachments</label>
                @if(count($topic['attachments']))
                    <ul class="flex flex-col gap-2">
                        @foreach($topic['attachments'] as $attachment)
                            <li class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg bg-surface-container-low dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700 text-sm">
                                <span class="flex items-center gap-2 min-w-0 text-on-surface dark:text-slate-200">
                                    <i class="fa-solid fa-paperclip text-xs text-on-surface-variant"></i>
                                    <span class="truncate">{{ $attachment['name'] }}</span>
                                    <span class="text-xs text-on-surface-variant dark:text-slate-400 shrink-0">{{ $attachment['size'] }}</span>
                                </span>
                                <label class="flex items-center gap-2 text-xs font-semibold text-on-surface-variant dark:text-slate-400 hover:text-error transition-colors cursor-pointer shrink-0">
                                    <input type="checkbox" name="remove_attachments[]" value="{{ $attachment['id'] }}" class="w-4 h-4 rounded border-outline-variant text-error focus:ring-error">
                                    Remove
                                </label>
                            </li>
                        @endforeach
                    </ul>
                    <p class="text-xs text-outline dark:text-slate-500 mt-1">Ticked files are deleted when you save.</p>
                @else
                    <p class="text-sm text-on-surface-variant dark:text-slate-400">No attachments yet.</p>
                @endif
            </div>

            {{-- Add attachments --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">
                    Add Attachments <span class="font-normal text-outline dark:text-slate-500">(Optional)</span>
                </label>
                <div class="relative group border-2 border-dashed border-outline-variant/60 dark:border-slate-600 bg-surface-container-low/50 dark:bg-slate-900/50 hover:border-primary dark:hover:border-primary hover:bg-primary/5 transition-colors rounded-xl flex flex-col items-center justify-center p-6 cursor-pointer overflow-hidden">
                    <div class="w-10 h-10 rounded-full bg-primary-container/20 text-primary flex items-center justify-center mb-2 group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <p class="text-sm font-semibold text-on-surface dark:text-white">Click or drag files to this area to upload</p>
                    <p class="text-xs font-medium text-outline dark:text-slate-500 mt-1">PDF, DOC, JPG, PNG up to 10MB each</p>
                    <input id="topic-attachments" name="attachments[]" type="file" multiple
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,image/*"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                </div>
                <ul id="topic-attachments-list" class="hidden flex-col gap-1.5 mt-1"></ul>
            </div>

            {{-- Linked Questions --}}
            @include('partials.question-widget', ['qwFieldName' => 'question_ids', 'qwLabel' => 'Linked Questions (Optional)'])

            {{-- Footer Actions --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-outline-variant/30 dark:border-slate-700">
                <a href="{{ route('topics') }}" class="px-5 py-2.5 rounded-full text-sm font-semibold border border-outline-variant/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-2">
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
            const attachmentsInput = document.getElementById('topic-attachments');
            const attachmentsList = document.getElementById('topic-attachments-list');
            if (!attachmentsInput || !attachmentsList) return;

            function renderAttachments() {
                attachmentsList.innerHTML = '';
                attachmentsList.classList.toggle('hidden', attachmentsInput.files.length === 0);
                attachmentsList.classList.toggle('flex', attachmentsInput.files.length > 0);

                Array.from(attachmentsInput.files).forEach((file, index) => {
                    const item = document.createElement('li');
                    item.className = 'flex items-center justify-between gap-3 px-3 py-2 rounded-lg bg-surface-container-low dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700 text-sm';
                    item.innerHTML = `
                        <span class="flex items-center gap-2 min-w-0 text-on-surface dark:text-slate-200">
                            <i class="fa-solid fa-paperclip text-xs text-on-surface-variant"></i>
                            <span class="truncate">${file.name}</span>
                        </span>
                        <span class="flex items-center gap-2 shrink-0">
                            <span class="text-xs text-on-surface-variant dark:text-slate-400">${(file.size / 1024).toFixed(0)} KB</span>
                            <button type="button" data-index="${index}" class="w-6 h-6 flex items-center justify-center rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </span>`;
                    attachmentsList.appendChild(item);
                });
            }

            attachmentsInput.addEventListener('change', renderAttachments);

            attachmentsList.addEventListener('click', (e) => {
                const button = e.target.closest('button[data-index]');
                if (!button) return;

                // FileList is read-only, so rebuild it without the removed file.
                const remaining = new DataTransfer();
                Array.from(attachmentsInput.files).forEach((file, index) => {
                    if (index !== Number(button.dataset.index)) remaining.items.add(file);
                });
                attachmentsInput.files = remaining.files;
                renderAttachments();
            });
        });
    </script>
@endpush
