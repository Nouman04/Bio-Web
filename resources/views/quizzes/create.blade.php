@extends('layouts.app')

@section('title', 'Create New Quiz')
@section('meta-description', 'Create and configure a new quiz or assessment.')

@section('page-title', 'Create New Quiz')
@section('page-subtitle', 'Create and configure a new quiz or assessment.')

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
    {{-- Header Section --}}
    <div class="flex justify-between items-center z-20 mb-6">
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2 text-xs font-medium text-outline dark:text-slate-400">
                <span>Assessments</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span>Quizzes</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-primary font-semibold">Create</span>
            </div>
            <h2 class="text-2xl md:text-3xl font-bold text-on-surface dark:text-white">Create New Quiz</h2>
        </div>
    </div>

    {{-- Form Layout --}}
    <form action="{{ route('quizzes.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        
        {{-- Left Column: Settings (4 cols) --}}
        <div class="lg:col-span-4 flex flex-col gap-6">
            {{-- General Info Card --}}
            <div class="glass-panel dark:bg-slate-800/80 rounded-xl p-6 flex flex-col gap-4">
                <h3 class="text-lg font-bold text-on-surface dark:text-white border-b border-surface-variant dark:border-slate-700 pb-3 mb-1">General Information</h3>
                
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="title">Quiz Title</label>
                    <input class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] placeholder:text-outline/70 text-on-surface dark:text-slate-200" id="title" name="title" placeholder="e.g., Final Examination" type="text" required/>
                </div>
                
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="description">Description</label>
                    <textarea id="description" name="description" data-quill data-quill-height="140px" placeholder="Provide a brief description of the quiz..."></textarea>
                </div>
                
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="type">Quiz Type</label>
                    <div class="relative">
                        <select class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-3 text-sm appearance-none focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] cursor-pointer text-on-surface dark:text-slate-200" id="type" name="type">
                            <option value="mixed">Mixed (MCQs & Theory)</option>
                            <option value="mcq">Multiple Choice Only</option>
                            <option value="theory">Theory / Essay Only</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-outline text-xs pointer-events-none"></i>
                    </div>
                </div>
            </div>

            {{-- Configuration Card --}}
            <div class="glass-panel dark:bg-slate-800/80 rounded-xl p-6 flex flex-col gap-4">
                <h3 class="text-lg font-bold text-on-surface dark:text-white border-b border-surface-variant dark:border-slate-700 pb-3 mb-1">Configuration</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="duration">Duration (Mins)</label>
                        <input class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] text-on-surface dark:text-slate-200" id="duration" name="duration" type="number" min="0" value="60"/>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-semibold text-on-surface dark:text-slate-200" for="passing_score">Passing Score (%)</label>
                        <input class="w-full bg-surface-container-lowest dark:bg-slate-900 border border-outline-variant/60 dark:border-slate-700 rounded-lg px-4 py-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)] text-on-surface dark:text-slate-200" id="passing_score" name="passing_score" type="number" min="0" max="100" value="70"/>
                    </div>
                </div>
                
                <div class="flex items-center justify-between py-2">
                    <div>
                        <p class="text-sm font-semibold text-on-surface dark:text-slate-200">Shuffle Questions</p>
                        <p class="text-xs text-on-surface-variant dark:text-slate-400">Randomize question order</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="shuffle_questions" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-surface-container-high dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
                
                <div class="flex items-center justify-between py-2">
                    <div>
                        <p class="text-sm font-semibold text-on-surface dark:text-slate-200">Show Results Immediate</p>
                        <p class="text-xs text-on-surface-variant dark:text-slate-400">Student sees score upon submit</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="show_results" class="sr-only peer">
                        <div class="w-11 h-6 bg-surface-container-high dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Right Column: Question Selection (8 cols) --}}
        <div class="lg:col-span-8 flex flex-col gap-6">
            <div class="glass-panel dark:bg-slate-800/80 rounded-xl p-6 flex flex-col gap-4 min-h-[500px]">
                <div class="flex justify-between items-center border-b border-surface-variant dark:border-slate-700 pb-3">
                    <h3 class="text-lg font-bold text-on-surface dark:text-white">Selected Questions</h3>
                    <button type="button" class="text-sm font-semibold text-primary hover:text-primary-container transition-colors flex items-center gap-1">
                        <i class="fa-solid fa-plus text-xs"></i> Add from Bank
                    </button>
                </div>
                
                <div class="flex-1 flex flex-col items-center justify-center py-12 px-4 text-center border-2 border-dashed border-outline-variant/50 dark:border-slate-600 rounded-lg bg-surface-container-lowest/50 dark:bg-slate-900/50">
                    <div class="w-16 h-16 bg-primary-container/20 text-primary rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-clipboard-question text-2xl"></i>
                    </div>
                    <h4 class="text-base font-semibold text-on-surface dark:text-white mb-2">No Questions Added Yet</h4>
                    <p class="text-sm text-on-surface-variant dark:text-slate-400 max-w-sm mb-6">Start building your quiz by selecting questions from the Question Bank or create new ones on the fly.</p>
                    <div class="flex gap-3">
                        <button type="button" class="px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-container transition-colors shadow-sm">
                            Browse Bank
                        </button>
                        <button type="button" class="px-4 py-2 border border-outline/50 text-on-surface text-sm font-semibold rounded-lg hover:bg-surface-container-high dark:text-white dark:hover:bg-slate-700 transition-colors">
                            Create Question
                        </button>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('quizzes') }}" class="px-6 py-2.5 rounded-full border border-outline/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 text-sm font-semibold hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors inline-block text-center">
                    Cancel
                </a>
                <button class="px-6 py-2.5 rounded-full bg-surface-variant text-on-surface-variant text-sm font-semibold hover:bg-outline-variant transition-all flex items-center gap-2 cursor-pointer" type="submit">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    Save Draft
                </button>
                <button class="px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md shadow-primary/20 hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 cursor-pointer" type="submit">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    Publish Quiz
                </button>
            </div>
        </div>
    </form>
@endsection
