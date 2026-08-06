@extends('layouts.app')

@section('title', 'Study Notes')
@section('meta-description', 'Manage study notes and resources.')

@section('page-title', 'Study Notes')
@section('page-subtitle', 'Manage and organize lecture notes, resources, and study materials.')

@push('styles')
<style>
    .modal { display: none; }
    .modal.active { display: flex; }
</style>
@endpush

@section('content')
    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Study Notes</span>
    </div>

    {{-- Page Header & Actions --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface dark:text-white">Study Notes</h1>
            <p class="font-body-md text-body-md text-on-surface-variant dark:text-slate-400 mt-1">Manage and organize lecture notes, resources, and study materials.</p>
        </div>
        <button onclick="document.getElementById('addNoteModal').classList.add('active')" class="bg-gradient-to-r from-primary to-primary-container text-on-primary rounded-full py-2.5 px-6 text-sm font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-plus text-sm"></i>
            Add Note
        </button>
    </div>

    {{-- Filters Card --}}
    <div class="bg-surface-container-lowest/70 dark:bg-slate-800 rounded-xl p-5 mb-6 shadow-sm border border-outline-variant/30 dark:border-slate-700 flex flex-wrap gap-4 items-end relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-2">Course / Chapter</label>
            <select class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none transition-all">
                <option value="">All Courses</option>
                <option>Physics 101</option>
                <option>Biology Fundamentals</option>
            </select>
        </div>
        <div class="flex-2 min-w-[250px]">
            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-2">Search Notes</label>
            <input class="w-full bg-white dark:bg-slate-900 border border-outline-variant rounded-xl text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary text-on-surface outline-none transition-all" placeholder="Title, tags..." type="text">
        </div>
        <div class="flex gap-2">
            <button class="bg-surface hover:bg-surface-variant dark:bg-slate-700 border border-outline-variant/30 text-on-surface-variant rounded-lg p-2 transition-colors shadow-sm" title="Clear Filters">
                <i class="fa-solid fa-filter-circle-xmark"></i>
            </button>
        </div>
    </div>

    {{-- Notes Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($notes as $note)
            <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-2xl p-5 hover:shadow-lg transition-shadow group flex flex-col h-full border border-outline-variant/30 dark:border-slate-700 relative">
                <div class="flex justify-between items-start mb-3">
                    <span class="inline-flex items-center px-2 py-1 rounded {{ $note['course_tag_color'] }} text-[10px] font-bold uppercase tracking-wider">{{ $note['course'] }}</span>
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                        <button class="w-7 h-7 flex items-center justify-center rounded-md text-on-surface-variant hover:text-primary hover:bg-primary/10"><i class="fa-solid fa-pen text-xs"></i></button>
                        <button class="w-7 h-7 flex items-center justify-center rounded-md text-on-surface-variant hover:text-error hover:bg-error/10"><i class="fa-solid fa-trash text-xs"></i></button>
                    </div>
                </div>
                <h3 class="font-semibold text-on-surface dark:text-white mb-2 line-clamp-1">{{ $note['title'] }}</h3>
                <p class="text-sm text-on-surface-variant dark:text-slate-400 mb-4 line-clamp-3 flex-1">
                    {{ $note['excerpt'] }}
                </p>
                <div class="flex items-center justify-between text-xs text-outline pt-4 border-t border-outline-variant/20 mt-auto">
                    <div class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-day"></i> {{ $note['date'] }}</div>
                    <div class="flex items-center gap-1.5"><i class="fa-solid fa-paperclip"></i> {{ $note['attachments'] }} Files</div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-on-surface-variant dark:text-slate-500">
                <i class="fa-regular fa-note-sticky text-4xl mb-3 block opacity-30"></i>
                No notes found.
            </div>
        @endforelse
    </div>

    {{-- Add Note Modal --}}
    <div id="addNoteModal" class="modal fixed inset-0 z-50 items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        <div class="bg-surface dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden animate-[fadeIn_0.2s_ease-out]">
            <div class="px-6 py-4 border-b border-outline-variant/20 flex justify-between items-center bg-surface-container-lowest dark:bg-slate-800">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Add New Note</h3>
                <button onclick="document.getElementById('addNoteModal').classList.remove('active')" class="text-on-surface-variant hover:text-error transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-error/10">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="p-6">
                <form action="{{ route('notes.store') }}" method="POST" id="note-form" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Note Title</label>
                            <input name="title" type="text" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-on-surface dark:text-slate-200 focus:ring-1 focus:ring-primary focus:border-primary outline-none" placeholder="e.g. Week 1 Summary" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Course / Tag</label>
                            <select name="course" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-on-surface dark:text-slate-200 focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                                <option>Physics 101</option>
                                <option>Biology</option>
                                <option>General</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Content</label>
                        <textarea name="content" class="w-full bg-white dark:bg-slate-800 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-on-surface dark:text-slate-200 focus:ring-1 focus:ring-primary focus:border-primary outline-none min-h-[120px] resize-y" placeholder="Write your note content here..." required></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant dark:text-slate-400 mb-1">Attachments</label>
                        <div class="border-2 border-dashed border-outline-variant/50 dark:border-slate-600 rounded-lg p-6 flex flex-col items-center justify-center text-outline bg-surface-container-lowest dark:bg-slate-800 hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl mb-2 text-primary/60"></i>
                            <span class="text-sm font-medium">Click to upload files (PDF, images, docx)</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="px-6 py-4 border-t border-outline-variant/20 bg-surface-container-lowest dark:bg-slate-800 flex justify-end gap-3">
                <button onclick="document.getElementById('addNoteModal').classList.remove('active')" class="px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container dark:hover:bg-slate-700 rounded-full transition-colors">Cancel</button>
                <button type="submit" form="note-form" class="px-6 py-2 text-sm font-semibold text-white bg-primary rounded-full hover:bg-primary-container shadow-md transition-all">Save Note</button>
            </div>
        </div>
    </div>
@endsection
