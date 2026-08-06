@extends('layouts.app')

@section('title', 'Create Flashcard Set')
@section('meta-description', 'Build flashcard sets by selecting questions from the bank.')

{{-- Hide default header to give more vertical space to the split pane --}}
@section('hide-header', true)

@section('content')
    {{-- Top Action Bar --}}
    <header class="h-20 bg-surface/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-outline-variant/20 dark:border-slate-700 px-4 md:px-8 flex flex-col md:flex-row md:items-center justify-between shrink-0 z-30 sticky top-0 gap-2 -mx-6 md:-mx-8 lg:-mx-12 -mt-6">
        <div class="flex-1 max-w-2xl flex items-center gap-2">
            <a href="{{ route('flashcards') }}" class="w-10 h-10 flex items-center justify-center text-outline hover:text-primary rounded-full hover:bg-primary/10 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <input type="text" value="Biology 101: Cellular Structures" class="w-full bg-transparent border-none text-xl font-bold text-on-surface dark:text-white placeholder:text-outline focus:ring-0 px-0 h-full">
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('flashcards') }}" class="px-6 py-2 rounded-full border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-colors text-sm font-semibold">Cancel</a>
            <button form="flashcard-form" class="bg-gradient-to-r from-primary to-primary-container text-white px-6 py-2 rounded-full text-sm font-semibold flex items-center gap-2 shadow-md hover:shadow-lg transition-all">
                <i class="fa-solid fa-save"></i> Save Set
            </button>
        </div>
    </header>

    {{-- Split Workspace --}}
    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden -mx-6 md:-mx-8 lg:-mx-12 h-[calc(100vh-80px)]">
        
        {{-- Left Panel: Question Bank Source --}}
        <div class="w-full lg:w-[350px] xl:w-[400px] border-r border-outline-variant/20 dark:border-slate-700 flex flex-col bg-surface/50 dark:bg-slate-900/50 z-10 shrink-0 h-[40vh] lg:h-full overflow-hidden">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 shrink-0 space-y-4">
                <h2 class="text-lg font-bold text-on-surface dark:text-white">Question Bank Source</h2>
                <div class="flex flex-col gap-3">
                    <select class="w-full bg-white dark:bg-slate-800 border border-outline-variant/30 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-primary">
                        <option>Chapter 3: The Cell</option>
                        <option>Chapter 4: Metabolism</option>
                    </select>
                    <select class="w-full bg-white dark:bg-slate-800 border border-outline-variant/30 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-primary">
                        <option>Select Topic...</option>
                        <option>Cell Membrane</option>
                    </select>
                    <div class="relative">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm"></i>
                        <input type="text" placeholder="Search questions..." class="w-full bg-white dark:bg-slate-800 border border-outline-variant/30 rounded-lg pl-9 pr-4 py-2.5 text-sm outline-none focus:border-primary">
                    </div>
                </div>
            </div>

            {{-- Draggable Question List --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                {{-- Item 1 --}}
                <div class="bg-white/80 dark:bg-slate-800 border border-outline-variant/30 rounded-xl p-4 cursor-pointer group hover:border-primary/50 hover:shadow-md transition-all relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-tertiary opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="flex gap-3">
                        <i class="fa-solid fa-grip-vertical text-outline/50 mt-1 cursor-grab"></i>
                        <div>
                            <span class="inline-block px-2 py-0.5 bg-surface-variant text-on-surface-variant dark:bg-slate-700 dark:text-slate-300 text-[10px] font-bold rounded mb-2 uppercase">Multiple Choice</span>
                            <p class="text-sm text-on-surface dark:text-slate-200 line-clamp-3">What is the primary function of the mitochondria in a eukaryotic cell?</p>
                            <button class="mt-3 text-primary text-xs font-semibold flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fa-solid fa-circle-plus"></i> Add to Set
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Selected Item --}}
                <div class="bg-primary/5 border border-primary rounded-xl p-4 relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary"></div>
                    <div class="absolute right-3 top-3">
                        <i class="fa-solid fa-circle-check text-primary"></i>
                    </div>
                    <div class="flex gap-3">
                        <i class="fa-solid fa-grip-vertical text-outline/30 mt-1 cursor-grab"></i>
                        <div class="pr-6">
                            <span class="inline-block px-2 py-0.5 bg-surface-variant text-on-surface-variant dark:bg-slate-700 dark:text-slate-300 text-[10px] font-bold rounded mb-2 uppercase">True/False</span>
                            <p class="text-sm text-on-surface dark:text-slate-200 opacity-80">Plant cells contain both chloroplasts and mitochondria. (True/False)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Panel: Editor Area --}}
        <div class="flex-1 flex flex-col bg-surface-container-lowest dark:bg-slate-900 relative z-0 h-[60vh] lg:h-full">
            <div class="p-6 border-b border-outline-variant/20 dark:border-slate-700 bg-surface/50 dark:bg-slate-800/50 backdrop-blur z-10 flex justify-between items-center shrink-0">
                <h2 class="text-lg font-bold text-on-surface dark:text-white">Flashcard Editor</h2>
                <span class="text-xs font-semibold text-on-surface-variant bg-surface-variant dark:bg-slate-700 dark:text-slate-300 px-3 py-1 rounded-full">2 Cards Selected</span>
            </div>

            <form id="flashcard-form" action="{{ route('flashcards.store') }}" method="POST" class="flex-1 overflow-y-auto p-4 md:p-8 space-y-6 pb-32">
                @csrf
                
                {{-- Editor Card 1 --}}
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 md:p-6 shadow-sm border border-outline-variant/30 dark:border-slate-700 flex flex-col md:flex-row gap-4 md:gap-6 relative group hover:shadow-md transition-shadow">
                    <div class="flex flex-col items-center gap-2 md:border-r border-outline-variant/20 md:pr-4 shrink-0">
                        <span class="text-outline-variant font-bold text-xl">01</span>
                        <i class="fa-solid fa-grip-vertical text-outline cursor-grab"></i>
                    </div>
                    <div class="flex-1 flex flex-col xl:flex-row gap-6">
                        <div class="flex-1 space-y-3">
                            <h3 class="text-xs font-bold text-on-surface-variant dark:text-slate-400 uppercase tracking-wider">Front (Question)</h3>
                            <div class="p-4 bg-surface dark:bg-slate-900 rounded-xl border border-outline-variant/30 min-h-[120px]">
                                <p class="text-sm text-on-surface dark:text-slate-300">What is the primary function of the mitochondria in a eukaryotic cell?</p>
                            </div>
                        </div>
                        <div class="flex-1 space-y-3">
                            <h3 class="text-xs font-bold text-primary uppercase tracking-wider">Back (Answer/Hint)</h3>
                            <textarea name="cards[0][answer]" class="w-full bg-white dark:bg-slate-900 border border-outline-variant/30 rounded-xl p-4 text-sm text-on-surface dark:text-slate-300 min-h-[120px] resize-y focus:ring-1 focus:ring-primary outline-none transition-all" placeholder="Enter answer or hint..."></textarea>
                        </div>
                    </div>
                    <button type="button" class="absolute -top-3 -right-3 w-8 h-8 bg-error text-white rounded-full flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-all hover:scale-110">
                        <i class="fa-solid fa-times text-sm"></i>
                    </button>
                </div>

                {{-- Drop Zone --}}
                <div class="border-2 border-dashed border-primary/30 rounded-2xl p-12 flex flex-col items-center justify-center text-outline-variant bg-primary/5 hover:bg-primary/10 transition-colors cursor-pointer">
                    <i class="fa-regular fa-file-lines text-4xl mb-4 text-primary/60"></i>
                    <p class="text-lg font-bold text-primary">Drag & Drop more questions here</p>
                    <p class="text-sm mt-2 text-on-surface-variant">or select them from the Question Bank</p>
                </div>
            </form>
        </div>

    </div>
@endsection
