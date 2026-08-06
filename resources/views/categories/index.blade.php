@extends('layouts.app')

@section('title', 'Categories')
@section('meta-description', 'Manage course categories and subcategories in EduAdmin LMS.')

@section('page-title', 'Categories')
@section('page-subtitle', 'Manage course categories and subcategories.')

@section('content')

    {{-- Breadcrumb Row --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-on-surface-variant dark:text-slate-500">Content</span>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Categories</span>
    </div>

    {{-- Filters & Search Section --}}
    <div class="glass-panel bg-surface-container-lowest/70 dark:bg-slate-800 rounded-2xl p-4 flex flex-col md:flex-row gap-4 justify-between items-center mb-6 border border-outline-variant/30 dark:border-slate-700">
        <form action="{{ route('categories') }}" method="GET" class="w-full flex flex-col md:flex-row gap-4 justify-between items-center">
            <!-- Retain current sorts -->
            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
            <input type="hidden" name="direction" value="{{ $filters['direction'] }}">

            <div class="w-full md:w-auto flex-1 max-w-md relative group">
                <span class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors"></span>
                <input name="search" value="{{ $filters['search'] }}"
                    class="w-full pl-10 pr-4 py-2.5 bg-surface-container-low dark:bg-slate-900 rounded-xl border border-outline-variant focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm outline-none transition-all"
                    placeholder="Search categories..." type="text">
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <a href="{{ route('categories') }}"
                    class="p-2.5 text-on-surface-variant border border-outline-variant rounded-xl hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors flex items-center justify-center"
                    title="Reset Filters">
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                </a>
                <button type="button"
                    class="flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-primary-container text-on-primary px-6 py-2.5 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Add Category
                </button>
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="bg-surface-container-lowest dark:bg-slate-800 rounded-3xl overflow-hidden shadow-sm border border-outline-variant/30 dark:border-slate-700">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant/20 bg-surface-container-low/40 dark:bg-slate-900/40">
                        <th class="py-4 px-6 text-sm font-semibold text-on-surface-variant dark:text-slate-400 w-1/3">
                            @php
                                $nextDir = ($filters['sort'] == 'title' && $filters['direction'] == 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a href="{{ route('categories', array_merge(request()->query(), ['sort' => 'title', 'direction' => $nextDir])) }}" class="flex items-center gap-1 hover:text-primary transition-colors">
                                Title
                                @if($filters['sort'] == 'title')
                                    <i class="fa-solid {{ $filters['direction'] == 'asc' ? 'fa-sort-up' : 'fa-sort-down' }} text-xs"></i>
                                @else
                                    <i class="fa-solid fa-sort text-xs opacity-40"></i>
                                @endif
                            </a>
                        </th>
                        <th class="py-4 px-6 text-sm font-semibold text-on-surface-variant dark:text-slate-400 w-1/4">Parent Category</th>
                        <th class="py-4 px-6 text-sm font-semibold text-on-surface-variant dark:text-slate-400 w-1/5">Slug</th>
                        <th class="py-4 px-6 text-sm font-semibold text-on-surface-variant dark:text-slate-400 text-center w-1/6">Courses</th>
                        <th class="py-4 px-6 text-sm font-semibold text-on-surface-variant dark:text-slate-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 dark:divide-slate-700">
                    @forelse($categories as $c)
                        <tr class="hover:bg-primary/5 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    @if(isset($c['isChild']) && $c['isChild'])
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700/50 flex items-center justify-center text-slate-500 ml-4">
                                            <i class="fa-solid fa-chevron-right text-xs"></i>
                                        </div>
                                        <span class="text-sm font-semibold text-on-surface-variant dark:text-slate-300">{{ $c['title'] }}</span>
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold shadow-sm">
                                            <i class="fa-solid fa-folder text-sm"></i>
                                        </div>
                                        <span class="text-sm font-semibold text-on-background dark:text-white">{{ $c['title'] }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6 text-sm text-on-surface dark:text-slate-300">
                                {{ $c['parent'] ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-sm text-on-surface-variant dark:text-slate-400">
                                {{ $c['slug'] }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-container/10 text-primary">
                                    {{ $c['courses'] }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
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
                                No categories found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
