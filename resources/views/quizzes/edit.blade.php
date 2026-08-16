@extends('layouts.app')

@section('title', 'Edit Quiz')
@section('meta-description', 'Edit an existing quiz or assessment.')

@section('page-title', 'Edit Quiz')
@section('page-subtitle', 'Edit an existing quiz or assessment.')

@push('styles')
<style>
    .glass-card {
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0px 10px 30px rgba(99, 102, 241, 0.08);
    }
</style>
@endpush

@section('content')
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('quizzes') }}" class="text-on-surface-variant hover:bg-surface-container-high rounded-full p-2 transition-all">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <h2 class="text-2xl md:text-3xl font-bold text-on-surface dark:text-white">
                Edit Quiz: <span class="font-normal text-on-surface-variant">Midterm Examination</span>
            </h2>
        </div>
        <button type="submit" form="edit-quiz-form" class="bg-primary text-on-primary px-6 py-2 rounded-full text-sm font-semibold shadow-sm hover:shadow-md transition-shadow flex items-center gap-2">
            <i class="fa-solid fa-save text-[16px]"></i>
            Save Changes
        </button>
    </div>

    {{-- Form Layout --}}
    <form id="edit-quiz-form" action="{{ route('quizzes.update', 1) }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        @method('PUT')
        
        {{-- Left Column: General Info (4 cols) --}}
        <div class="lg:col-span-4 flex flex-col gap-6">
            <section class="glass-card dark:bg-slate-800/80 rounded-xl p-6">
                <h3 class="text-lg font-bold text-on-surface dark:text-white border-b border-surface-variant dark:border-slate-700 pb-3 mb-4">General Information</h3>
                <div class="space-y-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-semibold text-on-surface dark:text-slate-200">Quiz Title</label>
                        <input class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-2 text-sm text-on-surface dark:text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-inner" type="text" value="Midterm Examination" name="title"/>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-semibold text-on-surface dark:text-slate-200">Description</label>
                        <textarea name="description" data-quill data-quill-height="140px">Comprehensive assessment covering chapters 1 through 5.</textarea>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-semibold text-on-surface dark:text-slate-200">Quiz Type</label>
                        <div class="bg-surface-container dark:bg-slate-700 border border-outline-variant/50 dark:border-slate-600 rounded-lg px-4 py-2 flex items-center gap-2 opacity-70 cursor-not-allowed">
                            <i class="fa-solid fa-lock text-on-surface-variant dark:text-slate-400 text-sm"></i>
                            <span class="text-sm text-on-surface-variant dark:text-slate-400">Multiple Choice (Standard)</span>
                        </div>
                        <p class="text-xs text-outline dark:text-slate-500 mt-1">Quiz type cannot be changed after creation.</p>
                    </div>
                </div>
            </section>

            <section class="glass-card dark:bg-slate-800/80 rounded-xl p-6">
                <h3 class="text-lg font-bold text-on-surface dark:text-white border-b border-surface-variant dark:border-slate-700 pb-3 mb-4">Settings</h3>
                <div class="space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input checked class="w-4 h-4 text-primary bg-surface-container dark:bg-slate-700 border-outline-variant dark:border-slate-600 rounded focus:ring-primary" type="checkbox" name="shuffle_questions"/>
                        <span class="text-sm text-on-surface dark:text-slate-200">Shuffle Questions</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input class="w-4 h-4 text-primary bg-surface-container dark:bg-slate-700 border-outline-variant dark:border-slate-600 rounded focus:ring-primary" type="checkbox" name="time_limit" checked/>
                        <span class="text-sm text-on-surface dark:text-slate-200">Time Limit (60 mins)</span>
                    </label>
                </div>
            </section>
        </div>

        {{-- Right Column: Selected Questions (8 cols) --}}
        <div class="lg:col-span-8 flex flex-col">
            <section class="glass-card dark:bg-slate-800/80 rounded-xl p-6 flex-1 flex flex-col">
                <div class="flex justify-between items-center mb-4 border-b border-outline-variant/30 dark:border-slate-700 pb-3">
                    <h3 class="text-lg font-bold text-on-surface dark:text-white">Selected Questions (3)</h3>
                    <span class="text-xs font-semibold bg-primary-container/10 dark:bg-primary/20 text-primary dark:text-primary-container px-3 py-1 rounded-full">Total Points: 30</span>
                </div>
                
                <div class="flex-1 space-y-4 overflow-y-auto pr-2">
                    {{-- Question Card 1 --}}
                    <div class="bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/50 dark:border-slate-700 rounded-lg p-4 flex gap-4 hover-lift group">
                        <div class="flex flex-col items-center justify-center cursor-move text-outline-variant dark:text-slate-500 hover:text-on-surface-variant dark:hover:text-slate-300 transition-colors">
                            <i class="fa-solid fa-grip-vertical text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-semibold text-primary bg-primary/10 dark:bg-primary/20 px-2 py-0.5 rounded">Multiple Choice</span>
                                <div class="flex items-center gap-2">
                                    <label class="text-xs font-medium text-on-surface-variant dark:text-slate-400">Points:</label>
                                    <input class="w-16 bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded px-2 py-1 text-sm text-on-surface dark:text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="number" value="10"/>
                                </div>
                            </div>
                            <p class="text-sm text-on-surface dark:text-slate-200 mb-3 font-medium">Which of the following best describes the process of photosynthesis?</p>
                            <div class="space-y-2">
                                <div class="text-sm text-on-surface-variant dark:text-slate-400 flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full border border-outline-variant dark:border-slate-600 flex-shrink-0"></span> It converts light energy into chemical energy.
                                </div>
                                <div class="text-sm text-tertiary flex items-center gap-2 bg-tertiary-container/10 dark:bg-tertiary/20 px-2 py-1.5 rounded">
                                    <span class="w-4 h-4 rounded-full bg-tertiary flex-shrink-0 flex items-center justify-center">
                                        <i class="fa-solid fa-check text-white text-[10px]"></i>
                                    </span> It produces oxygen as a byproduct.
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <button type="button" class="text-outline dark:text-slate-500 hover:text-error dark:hover:text-error transition-colors p-2 rounded hover:bg-error/10 opacity-0 group-hover:opacity-100">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Question Card 2 --}}
                    <div class="bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/50 dark:border-slate-700 rounded-lg p-4 flex gap-4 hover-lift group">
                        <div class="flex flex-col items-center justify-center cursor-move text-outline-variant dark:text-slate-500 hover:text-on-surface-variant dark:hover:text-slate-300 transition-colors">
                            <i class="fa-solid fa-grip-vertical text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-semibold text-primary bg-primary/10 dark:bg-primary/20 px-2 py-0.5 rounded">True/False</span>
                                <div class="flex items-center gap-2">
                                    <label class="text-xs font-medium text-on-surface-variant dark:text-slate-400">Points:</label>
                                    <input class="w-16 bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded px-2 py-1 text-sm text-on-surface dark:text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="number" value="5"/>
                                </div>
                            </div>
                            <p class="text-sm text-on-surface dark:text-slate-200 mb-3 font-medium">Mitochondria are known as the powerhouse of the cell.</p>
                            <div class="space-y-2">
                                <div class="text-sm text-tertiary flex items-center gap-2 bg-tertiary-container/10 dark:bg-tertiary/20 px-2 py-1.5 rounded">
                                    <i class="fa-regular fa-circle-dot text-tertiary text-base"></i> True
                                </div>
                                <div class="text-sm text-on-surface-variant dark:text-slate-400 flex items-center gap-2">
                                    <i class="fa-regular fa-circle text-outline-variant dark:text-slate-500 text-base"></i> False
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <button type="button" class="text-outline dark:text-slate-500 hover:text-error dark:hover:text-error transition-colors p-2 rounded hover:bg-error/10 opacity-0 group-hover:opacity-100">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Question Card 3 --}}
                    <div class="bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/50 dark:border-slate-700 rounded-lg p-4 flex gap-4 hover-lift group">
                        <div class="flex flex-col items-center justify-center cursor-move text-outline-variant dark:text-slate-500 hover:text-on-surface-variant dark:hover:text-slate-300 transition-colors">
                            <i class="fa-solid fa-grip-vertical text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-semibold text-secondary bg-secondary-container/20 px-2 py-0.5 rounded">Short Answer</span>
                                <div class="flex items-center gap-2">
                                    <label class="text-xs font-medium text-on-surface-variant dark:text-slate-400">Points:</label>
                                    <input class="w-16 bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/60 dark:border-slate-700 rounded px-2 py-1 text-sm text-on-surface dark:text-slate-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" type="number" value="15"/>
                                </div>
                            </div>
                            <p class="text-sm text-on-surface dark:text-slate-200 mb-3 font-medium">Explain the difference between mitosis and meiosis.</p>
                            <div class="text-sm text-outline dark:text-slate-400 italic bg-surface-container/50 dark:bg-slate-800 p-3 rounded-lg border-l-2 border-outline-variant dark:border-slate-600">
                                Expected keywords: identical, variation, diploid, haploid, somatic, gametes.
                            </div>
                        </div>
                        <div class="flex items-start">
                            <button type="button" class="text-outline dark:text-slate-500 hover:text-error dark:hover:text-error transition-colors p-2 rounded hover:bg-error/10 opacity-0 group-hover:opacity-100">
                                <i class="fa-solid fa-trash text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                {{-- Add More Button --}}
                <div class="mt-6 pt-4 border-t border-outline-variant/30 dark:border-slate-700 flex justify-center">
                    <button type="button" class="bg-surface-container dark:bg-slate-800 border-2 border-primary border-dashed hover:bg-primary/5 dark:hover:bg-primary/10 text-primary dark:text-primary-fixed-dim rounded-lg py-4 px-8 w-full text-sm font-semibold flex items-center justify-center gap-2 transition-colors">
                        <i class="fa-solid fa-circle-plus text-lg"></i>
                        Add More Questions from Bank
                    </button>
                </div>
            </section>
        </div>
    </form>
@endsection
