@extends('layouts.app')

@section('title', 'Chapters')
@section('meta-description', 'Manage course chapters and curriculum structure in EduAdmin LMS.')

@section('page-title', 'Chapters')
@section('page-subtitle', 'Manage and organize instructional content structure.')

@section('content')

    {{-- Ambient Background Glow --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-[-1]">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-primary-fixed-dim/20 rounded-full blur-[120px]"></div>
        <div class="absolute top-[60%] -right-[10%] w-[40%] h-[40%] bg-tertiary-fixed-dim/10 rounded-full blur-[100px]"></div>
    </div>

    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-on-surface-variant dark:text-slate-500">Content</span>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Chapters</span>
    </div>

    {{-- Filters & Action Row --}}
    <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-4 flex flex-col md:flex-row gap-4 justify-between items-center mb-6 border border-outline-variant/30 dark:border-slate-700">
        <form action="{{ route('chapters') }}" method="GET" class="w-full flex flex-col md:flex-row gap-4 justify-between items-center">
            
            <div class="relative w-full md:w-56">
                <select name="course" onchange="this.form.submit()"
                    class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 pl-4 pr-10 text-sm text-on-surface focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none cursor-pointer">
                    <option value="">All Courses</option>
                    <option value="Computer Science 101" {{ $filters['course'] == 'Computer Science 101' ? 'selected' : '' }}>Computer Science 101</option>
                    <option value="Advanced Biology" {{ $filters['course'] == 'Advanced Biology' ? 'selected' : '' }}>Advanced Biology</option>
                    <option value="UX/UI Design Principles" {{ $filters['course'] == 'UX/UI Design Principles' ? 'selected' : '' }}>UX/UI Design Principles</option>
                </select>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <a href="{{ route('chapters') }}"
                    class="p-2.5 text-on-surface-variant border border-outline-variant rounded-xl hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors flex items-center justify-center"
                    title="Reset Filters">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                </a>
                <button type="button" onclick="openCreateChapterModal()"
                    class="flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all whitespace-nowrap">
                    <i class="fa-solid fa-plus text-xs"></i>
                    New Chapter
                </button>
            </div>
        </form>
    </div>

    {{-- Bento Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 relative overflow-hidden border border-outline-variant/30 dark:border-slate-700 shadow-sm flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Total Chapters</h3>
                <span class="fa-solid fa-book-bookmark text-primary bg-primary/10 p-2 rounded-lg text-sm"></span>
            </div>
            <div>
                <span class="text-3xl font-bold text-on-surface dark:text-white">{{ $totalCount }}</span>
            </div>
        </div>
        <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 relative overflow-hidden border border-outline-variant/30 dark:border-slate-700 shadow-sm flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Avg. Chapters / Course</h3>
                <span class="fa-solid fa-chart-bar text-secondary bg-secondary-container/20 p-2 rounded-lg text-sm"></span>
            </div>
            <div>
                <span class="text-3xl font-bold text-on-surface dark:text-white">1.3</span>
            </div>
        </div>
        <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-5 relative overflow-hidden border border-outline-variant/30 dark:border-slate-700 shadow-sm flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-xs font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Drafts</h3>
                <span class="fa-solid fa-file-signature text-tertiary bg-tertiary-container/20 p-2 rounded-lg text-sm"></span>
            </div>
            <div>
                <span class="text-3xl font-bold text-on-surface dark:text-white">{{ $draftsCount }}</span>
            </div>
        </div>
    </div>

    {{-- Main Data Table Card --}}
    <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-3xl overflow-hidden shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant/20 bg-surface-container-low/40 dark:bg-slate-900/40 text-sm font-semibold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-6 w-20">Chapter</th>
                        <th class="py-4 px-6">Title &amp; Description</th>
                        <th class="py-4 px-6">Course</th>
                        <th class="py-4 px-6 w-32">Status</th>
                        <th class="py-4 px-6 text-right w-32">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 dark:divide-slate-700 text-sm">
                    @forelse($chapters as $c)
                        <tr class="hover:bg-primary/5 transition-colors">
                            <td class="py-4 px-6 align-top">
                                <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-surface-container-low dark:bg-slate-900 text-on-surface dark:text-slate-300 font-semibold text-xs">
                                    {{ $c['num'] }}
                                </div>
                            </td>
                            <td class="py-4 px-6 align-top max-w-md">
                                <h4 class="font-semibold text-on-surface dark:text-white mb-1">{{ $c['title'] }}</h4>
                                <p class="text-xs text-on-surface-variant dark:text-slate-400 truncate">{{ $c['desc'] }}</p>
                            </td>
                            <td class="py-4 px-6 align-top">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-semibold">
                                    {{ $c['course'] }}
                                </span>
                            </td>
                            <td class="py-4 px-6 align-top">
                                @if($c['status'] === 'Published')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold text-tertiary bg-tertiary-container/20">
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold text-on-surface-variant bg-slate-100 dark:bg-slate-700">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 align-top text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button class="p-2 text-on-surface-variant dark:text-slate-400 hover:text-primary transition-colors rounded-full hover:bg-primary/10">
                                        <i class="fa-solid fa-pen text-sm"></i>
                                    </button>
                                    <button class="p-2 text-on-surface-variant dark:text-slate-400 hover:text-error transition-colors rounded-full hover:bg-error/10">
                                        <i class="fa-solid fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-on-surface-variant">
                                No chapters found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create Chapter Modal --}}
    <div id="create-chapter-modal-container" class="fixed inset-0 z-[100] flex items-center justify-center hidden" aria-modal="true" role="dialog">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm" onclick="closeCreateChapterModal()"></div>
        <!-- Panel -->
        <div class="relative w-full max-w-md mx-4 glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-outline-variant/30 dark:border-slate-700 animate-[fadeSlideIn_0.25s_ease]">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 flex justify-between items-center bg-surface-container-low/40 dark:bg-slate-900/40">
                <h3 class="text-lg font-bold text-on-surface dark:text-white">Create New Chapter</h3>
                <button type="button" onclick="closeCreateChapterModal()" class="p-1 rounded-full text-on-surface-variant hover:text-error hover:bg-error/10 transition-all">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form action="#" method="POST" class="p-6 flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Course</label>
                    <select class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface">
                        <option>Select a course</option>
                        <option>Computer Science 101</option>
                        <option>Advanced Biology</option>
                        <option>UX/UI Design Principles</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Chapter Number</label>
                    <input class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="e.g. 1" type="number">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Title</label>
                    <input class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface" placeholder="Enter chapter title" type="text">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">Description</label>
                    <textarea class="w-full bg-surface-container-low dark:bg-slate-900 border border-outline-variant rounded-xl py-2.5 px-4 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-on-surface h-24" placeholder="Brief description of the chapter content"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeCreateChapterModal()" class="px-5 py-2 rounded-full text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-full bg-primary text-on-primary text-sm font-semibold shadow-sm hover:bg-primary/95 transition-colors">Create Chapter</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openCreateChapterModal() {
            document.getElementById('create-chapter-modal-container').classList.remove('hidden');
        }

        function closeCreateChapterModal() {
            document.getElementById('create-chapter-modal-container').classList.add('hidden');
        }
    </script>
@endpush
