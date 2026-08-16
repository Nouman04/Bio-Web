@extends('layouts.app')

@section('title', 'Edit Note')
@section('meta-description', 'Update a study note, its content and its attachments.')

@section('page-title', 'Edit Note')
@section('page-subtitle', 'Update a study note, its content and its attachments.')

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
        <a class="hover:text-primary transition-colors" href="{{ route('notes') }}">Notes</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Edit</span>
    </div>

    {{-- Page Header --}}
    <div class="mb-8">
        <h2 class="text-2xl md:text-3xl font-bold text-on-surface dark:text-white">Edit Note</h2>
        <p class="text-on-surface-variant dark:text-slate-400 mt-2">{{ $note['title'] }}</p>
    </div>

    <div class="glass-panel dark:bg-slate-800/80 rounded-2xl border border-outline-variant/30 dark:border-slate-700 shadow-sm">
        <form action="{{ route('notes.update', $note['id']) }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1" for="title">Note Title</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $note['title']) }}" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-on-surface dark:text-slate-200 focus:ring-1 focus:ring-primary focus:border-primary outline-none" placeholder="e.g. Week 1 Summary" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1" for="course">Course / Tag</label>
                    <select id="course" name="course" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-on-surface dark:text-slate-200 focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                        <option {{ old('course', $note['course']) === 'Physics 101' ? 'selected' : '' }}>Physics 101</option>
                        <option {{ old('course', $note['course']) === 'Biology' ? 'selected' : '' }}>Biology</option>
                        <option {{ old('course', $note['course']) === 'General' ? 'selected' : '' }}>General</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1" for="chapter">Chapter</label>
                    <select id="chapter" name="chapter" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-on-surface dark:text-slate-200 focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                        <option value="">Select Chapter</option>
                        <option value="Ch1" {{ old('chapter', $note['chapter']) === 'Ch1' ? 'selected' : '' }}>Chapter 1: Fundamentals</option>
                        <option value="Ch2" {{ old('chapter', $note['chapter']) === 'Ch2' ? 'selected' : '' }}>Chapter 2: Advanced</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1" for="topic">Topic</label>
                    <select id="topic" name="topic" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-on-surface dark:text-slate-200 focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                        <option value="">Select Topic</option>
                        <option value="Basics" {{ old('topic', $note['topic']) === 'Basics' ? 'selected' : '' }}>Basics</option>
                        <option value="Advanced" {{ old('topic', $note['topic']) === 'Advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1" for="type">Note Type</label>
                    <select id="type" name="type" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-on-surface dark:text-slate-200 focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                        <option value="exam_notes" {{ old('type', $note['type']) === 'exam_notes' ? 'selected' : '' }}>Exam Notes</option>
                        <option value="summary" {{ old('type', $note['type']) === 'summary' ? 'selected' : '' }}>Summary</option>
                        <option value="flashcards" {{ old('type', $note['type']) === 'flashcards' ? 'selected' : '' }}>Flashcards</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1" for="content">Content</label>
                <textarea id="content" name="content" data-quill data-quill-height="240px" placeholder="Write your note content here..." required>{{ old('content', $note['content']) }}</textarea>
            </div>

            @include('partials.question-widget', ['qwFieldName' => 'question_ids', 'qwLabel' => 'Linked Questions (Optional)'])

            {{-- Existing attachments --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Current Attachments</label>
                @if(count($note['attachments']))
                    <ul class="flex flex-col gap-2">
                        @foreach($note['attachments'] as $attachment)
                            <li class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 text-sm">
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
                    <p class="text-xs text-outline dark:text-slate-500 mt-2">Ticked files are deleted when you save.</p>
                @else
                    <p class="text-sm text-on-surface-variant dark:text-slate-400">No attachments yet.</p>
                @endif
            </div>

            {{-- Add attachments --}}
            <div>
                <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Add Attachments</label>
                <div class="relative border-2 border-dashed border-outline-variant/50 dark:border-slate-600 rounded-lg p-6 flex flex-col items-center justify-center text-outline bg-surface-container-lowest dark:bg-slate-800 hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors cursor-pointer overflow-hidden">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl mb-2 text-primary/60"></i>
                    <span class="text-sm font-medium">Click to upload files (PDF, images, docx)</span>
                    <input id="note-attachments" name="attachments[]" type="file" multiple
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,image/*"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                </div>
                <ul id="note-attachments-list" class="hidden flex-col gap-1.5 mt-2"></ul>
            </div>

            {{-- Footer Actions --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-outline-variant/20 dark:border-slate-700">
                <a href="{{ route('notes') }}" class="px-5 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container dark:hover:bg-slate-700 rounded-full transition-colors">Cancel</a>
                <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-primary rounded-full hover:bg-primary-container shadow-md transition-all flex items-center gap-2">
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
            const attachmentsInput = document.getElementById('note-attachments');
            const attachmentsList = document.getElementById('note-attachments-list');
            if (!attachmentsInput || !attachmentsList) return;

            function renderAttachments() {
                attachmentsList.innerHTML = '';
                attachmentsList.classList.toggle('hidden', attachmentsInput.files.length === 0);
                attachmentsList.classList.toggle('flex', attachmentsInput.files.length > 0);

                Array.from(attachmentsInput.files).forEach((file, index) => {
                    const item = document.createElement('li');
                    item.className = 'flex items-center justify-between gap-3 px-3 py-2 rounded-lg bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 text-sm';
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
