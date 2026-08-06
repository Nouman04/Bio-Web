@extends('layouts.app')

@section('title', 'Image Management')
@section('meta-description', 'Manage and organize visual assets for course content.')

@section('page-title', 'Images')
@section('page-subtitle', 'Manage and organize visual assets for course content.')

@push('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .table-row-hover:hover {
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
</style>
@endpush

@section('content')
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold text-on-surface dark:text-white">Images</h2>
            <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-1">Manage and organize visual assets for course content.</p>
        </div>
        <a href="{{ route('images.create') }}" class="bg-gradient-to-r from-primary to-primary-container text-white px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg hover:scale-[1.02] transition-all flex items-center gap-2">
            <i class="fa-solid fa-plus text-[16px]"></i>
            Add New Image
        </a>
    </div>

    {{-- Filters & Controls Glass Panel --}}
    <form action="{{ route('diagrams') }}" method="GET" class="glass-panel dark:bg-slate-800/80 rounded-2xl p-6 shadow-sm flex flex-col lg:flex-row gap-4 items-center justify-between mb-6 border-outline-variant/30 dark:border-slate-700">
        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
            {{-- Search --}}
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[14px]"></i>
                <input name="title" value="{{ $filters['title'] ?? '' }}" class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/50 dark:border-slate-700 rounded-lg text-sm text-on-surface dark:text-slate-200 focus:border-primary focus:ring-1 focus:ring-primary shadow-inner inset" placeholder="Search by title..." type="text"/>
            </div>
            {{-- Dropdowns --}}
            <div class="relative w-full sm:w-48">
                <select name="course" onchange="this.form.submit()" class="w-full py-2 pl-4 pr-10 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/50 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary shadow-inner inset text-on-surface dark:text-slate-200 appearance-none">
                    <option value="">All Courses</option>
                    <option value="CS101" {{ ($filters['course'] ?? '') == 'CS101' ? 'selected' : '' }}>Computer Science 101</option>
                    <option value="BIO201" {{ ($filters['course'] ?? '') == 'BIO201' ? 'selected' : '' }}>Advanced Biology</option>
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-xs"></i>
            </div>
        </div>
        {{-- Date Range --}}
        <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
            <div class="relative w-full sm:w-48">
                <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[14px]"></i>
                <input name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/50 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary shadow-inner inset text-on-surface dark:text-slate-200"/>
            </div>
            <div class="relative w-full sm:w-48">
                <i class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[14px]"></i>
                <input name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/50 dark:border-slate-700 rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary shadow-inner inset text-on-surface dark:text-slate-200"/>
            </div>
            <a href="{{ route('diagrams') }}" class="p-2.5 text-on-surface-variant border border-outline-variant rounded-lg hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors flex items-center justify-center" title="Reset Filters">
                <i class="fa-solid fa-arrow-rotate-left"></i>
            </a>
        </div>
    </form>

    {{-- Data Table Glass Panel --}}
    <div class="glass-panel dark:bg-slate-800/80 bg-white/50 dark:bg-slate-900/50 rounded-2xl overflow-hidden shadow-sm border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="border-b border-outline-variant/20 dark:border-slate-700 text-xs text-on-surface-variant dark:text-slate-400 bg-surface-container-lowest/50 dark:bg-slate-800/50">
                        <th class="p-4 font-semibold w-24 uppercase">Preview</th>
                        <th class="p-4 font-semibold uppercase">Title</th>
                        <th class="p-4 font-semibold hidden sm:table-cell uppercase">Chapter</th>
                        <th class="p-4 font-semibold hidden md:table-cell uppercase">Topic</th>
                        <th class="p-4 font-semibold hidden lg:table-cell uppercase">Date Added</th>
                        <th class="p-4 font-semibold text-right uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($images as $image)
                        <tr class="border-b border-outline-variant/10 dark:border-slate-700 bg-white/40 dark:bg-slate-800/40 table-row-hover">
                            <td class="p-4">
                                <div class="w-16 h-12 rounded bg-surface-variant dark:bg-slate-700 overflow-hidden border border-outline-variant/30 dark:border-slate-600 flex items-center justify-center">
                                    @if($image['has_image'])
                                        <img class="w-full h-full object-cover" src="{{ $image['url'] }}" alt="{{ $image['title'] }}" />
                                    @else
                                        <i class="fa-solid fa-image-slash text-outline-variant dark:text-slate-500"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4">
                                <p class="font-medium text-on-surface dark:text-slate-200">{{ $image['title'] }}</p>
                                <p class="text-on-surface-variant dark:text-slate-400 text-xs mt-0.5">{{ $image['meta'] }}</p>
                            </td>
                            <td class="p-4 hidden sm:table-cell text-on-surface-variant dark:text-slate-400">{{ $image['chapter'] }}</td>
                            <td class="p-4 hidden md:table-cell">
                                <span class="inline-flex items-center px-2 py-1 rounded {{ $image['topic_color'] }} text-xs font-medium">{{ $image['topic'] }}</span>
                            </td>
                            <td class="p-4 hidden lg:table-cell text-on-surface-variant dark:text-slate-400">{{ $image['date_added'] }}</td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-full transition-colors dark:hover:bg-slate-700" title="Edit">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <button class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-error hover:bg-error/10 rounded-full transition-colors dark:hover:bg-slate-700" title="Delete">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-on-surface-variant dark:text-slate-500">
                                No images found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination (Simple) --}}
        <div class="px-6 py-4 border-t border-outline-variant/10 dark:border-slate-700 flex items-center justify-between bg-surface-container-lowest/30 dark:bg-slate-800/30">
            <span class="text-xs font-medium text-on-surface-variant dark:text-slate-400">Showing 1 to 3 of 45 results</span>
            <div class="flex gap-1">
                <button class="w-8 h-8 flex items-center justify-center rounded text-outline dark:text-slate-500 hover:bg-surface-variant dark:hover:bg-slate-700 transition-colors disabled:opacity-50" disabled>
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <button class="w-8 h-8 flex items-center justify-center rounded text-on-surface dark:text-slate-300 hover:bg-surface-variant dark:hover:bg-slate-700 transition-colors">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>
@endsection
