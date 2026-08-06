@extends('layouts.app')

@section('title', 'Add Questions')
@section('meta-description', 'Bulk add questions to the EduAdmin LMS question bank.')

@section('page-title', 'Bulk Add Questions')
@section('page-subtitle', 'Add multiple questions quickly using the high-density card view.')

@section('styles')
<style>
    .card-input:focus, .card-select:focus, .card-textarea:focus {
        outline: none;
        box-shadow: inset 0 0 0 2px #4648d4, 0 0 8px rgba(70, 72, 212, 0.2);
        border-color: transparent;
        position: relative;
        z-index: 10;
    }
    .q-card { transition: box-shadow 0.25s ease, transform 0.25s ease; }
    .q-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
</style>
@endsection

@section('content')

    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('questions') }}">Question Bank</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Bulk Add</span>
    </div>

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-on-surface dark:text-white">Bulk Add Questions</h1>
            <p class="text-sm text-on-surface-variant dark:text-slate-400 mt-1 max-w-2xl">Add multiple questions to your bank quickly. Changes are autosaved locally until you submit.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('questions') }}" class="px-5 py-2 rounded-full border border-outline-variant text-on-surface-variant dark:text-slate-400 text-sm font-semibold hover:bg-surface-container-low dark:hover:bg-slate-700 transition-colors">
                Cancel
            </a>
            <button type="submit" form="bulk-questions-form" class="px-6 py-2 rounded-full bg-gradient-to-r from-primary to-[#5b5de0] text-white text-sm font-semibold shadow-[0px_4px_14px_rgba(70,72,212,0.3)] hover:shadow-[0px_6px_20px_rgba(70,72,212,0.4)] transition-all flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                Save All Questions
            </button>
        </div>
    </div>

    {{-- Card Form --}}
    <form action="{{ route('questions.store') }}" method="POST" id="bulk-questions-form">
        @csrf
        <div class="flex flex-col gap-4" id="question-cards-container">

            {{-- Question Card 1 (MCQ) --}}
            <div class="q-card bg-white/60 dark:bg-slate-800/80 backdrop-blur-[12px] border border-white/80 dark:border-slate-700 rounded-xl p-5 relative group" data-card="1">
                <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button type="button" class="text-on-surface-variant hover:text-error hover:bg-error/10 p-1.5 rounded-md transition-colors delete-card" title="Delete Question">
                        <i class="fa-solid fa-trash text-sm"></i>
                    </button>
                </div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-bold text-sm card-num">1</span>
                    <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-3">
                        <select name="questions[0][chapter]" class="card-select bg-surface/50 dark:bg-slate-900 border border-outline-variant/30 rounded-lg px-3 py-2 text-sm hover:border-outline-variant appearance-none cursor-pointer text-on-surface dark:text-slate-300">
                            @foreach($chapters as $ch)
                                <option value="{{ $ch['id'] }}">{{ $ch['name'] }}</option>
                            @endforeach
                        </select>
                        <select name="questions[0][topic]" class="card-select bg-surface/50 dark:bg-slate-900 border border-outline-variant/30 rounded-lg px-3 py-2 text-sm hover:border-outline-variant appearance-none cursor-pointer text-on-surface dark:text-slate-300">
                            <option value="">Select Topic...</option>
                        </select>
                        <select name="questions[0][type]" class="card-select bg-primary-container/10 border border-primary/20 rounded-lg px-3 py-2 text-sm text-primary font-medium hover:border-primary/50 appearance-none cursor-pointer q-type-select">
                            <option value="MCQ" selected>MCQ</option>
                            <option value="Theory">Theory</option>
                        </select>
                        <select name="questions[0][difficulty]" class="card-select bg-surface/50 dark:bg-slate-900 border border-outline-variant/30 rounded-lg px-3 py-2 text-sm hover:border-outline-variant appearance-none cursor-pointer text-on-surface dark:text-slate-300">
                            <option>Easy</option>
                            <option selected>Medium</option>
                            <option>Hard</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-on-surface-variant dark:text-slate-400 mb-1 font-semibold">Question Text</label>
                        <input name="questions[0][text]" class="card-input w-full bg-white dark:bg-slate-900 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm hover:border-primary/50 transition-all text-on-surface dark:text-slate-200" placeholder="Enter question text..." type="text">
                    </div>
                    {{-- MCQ Options --}}
                    <div class="mcq-section grid grid-cols-2 gap-4 bg-surface-variant/20 dark:bg-slate-900/40 p-4 rounded-lg border border-outline-variant/20 dark:border-slate-700">
                        <div class="col-span-2 text-xs text-on-surface-variant dark:text-slate-400 font-semibold mb-1">Answer Options</div>
                        @foreach(['A','B','C','D'] as $opt)
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-outline w-5 text-sm">{{ $opt }}</span>
                            <input name="questions[0][option_{{ strtolower($opt) }}]" class="card-input flex-1 bg-white dark:bg-slate-900 border border-outline-variant/50 rounded-lg px-3 py-1.5 text-sm text-on-surface dark:text-slate-200" placeholder="Option {{ $opt }}">
                        </div>
                        @endforeach
                        <div class="col-span-2 mt-1 flex items-center gap-3">
                            <label class="text-xs text-on-surface-variant dark:text-slate-400 font-semibold">Correct Answer:</label>
                            <select name="questions[0][correct]" class="card-select bg-white dark:bg-slate-900 border border-tertiary/30 rounded-lg px-3 py-1.5 text-sm text-tertiary font-medium hover:border-tertiary appearance-none cursor-pointer">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                    </div>
                    {{-- Theory Section (hidden) --}}
                    <div class="theory-section hidden bg-surface-variant/20 dark:bg-slate-900/40 p-4 rounded-lg border border-outline-variant/20 dark:border-slate-700">
                        <label class="block text-xs text-on-surface-variant dark:text-slate-400 mb-2 font-semibold">Expected Answer / Rubric</label>
                        <textarea name="questions[0][rubric]" class="card-textarea w-full bg-white dark:bg-slate-900 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm hover:border-primary/50 transition-all resize-y min-h-[100px] text-on-surface dark:text-slate-200" placeholder="Provide the expected answer or grading rubric..."></textarea>
                    </div>
                </div>
            </div>

            {{-- Empty placeholder card --}}
            <div class="bg-white/40 dark:bg-slate-800/40 backdrop-blur-[12px] border border-dashed border-outline-variant/60 dark:border-slate-600 rounded-xl p-5" id="empty-card">
                <div class="flex items-center gap-3 mb-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-surface-variant/50 text-outline font-bold text-sm card-num">2</span>
                    <p class="text-sm text-on-surface-variant dark:text-slate-500">Click <strong>"Add New Question Row"</strong> below to add another question.</p>
                </div>
            </div>

        </div>
    </form>

    {{-- Sticky Footer --}}
    <div class="sticky bottom-0 mt-6 bg-surface/80 dark:bg-slate-900/80 backdrop-blur-md border-t border-outline-variant/20 dark:border-slate-700 py-4 -mx-8 px-8 lg:-mx-12 lg:px-12">
        <button type="button" id="add-card-btn" class="py-2.5 rounded-full border border-primary text-primary hover:bg-primary/5 transition-colors font-semibold text-sm flex items-center gap-2 bg-white dark:bg-slate-800 shadow-sm px-5">
            <i class="fa-solid fa-plus text-xs"></i>
            Add New Question Row
        </button>
    </div>

@endsection

@push('scripts')
<script>
    let cardIndex = 1;

    // Toggle MCQ / Theory sections based on type select
    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('q-type-select')) return;
        const card = e.target.closest('[data-card]');
        if (!card) return;
        const isMCQ = e.target.value === 'MCQ';
        card.querySelector('.mcq-section')?.classList.toggle('hidden', !isMCQ);
        card.querySelector('.theory-section')?.classList.toggle('hidden', isMCQ);
    });

    // Delete card
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.delete-card')) return;
        e.target.closest('[data-card]')?.remove();
        renumber();
    });

    // Renumber badges
    function renumber() {
        let n = 1;
        document.querySelectorAll('[data-card] .card-num').forEach(el => el.textContent = n++);
    }

    // Add new card
    document.getElementById('add-card-btn').addEventListener('click', function() {
        cardIndex++;
        const idx = cardIndex;
        const container = document.getElementById('question-cards-container');
        const emptyCard = document.getElementById('empty-card');
        const tpl = `
        <div class="q-card bg-white/60 dark:bg-slate-800/80 backdrop-blur-[12px] border border-white/80 dark:border-slate-700 rounded-xl p-5 relative group" data-card="${idx}">
            <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button type="button" class="text-on-surface-variant hover:text-error hover:bg-error/10 p-1.5 rounded-md transition-colors delete-card" title="Delete">
                    <i class="fa-solid fa-trash text-sm"></i>
                </button>
            </div>
            <div class="flex items-center gap-3 mb-4">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary font-bold text-sm card-num">${idx}</span>
                <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-3">
                    <select name="questions[${idx}][chapter]" class="card-select bg-transparent dark:bg-slate-900 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-outline hover:border-outline-variant appearance-none cursor-pointer">
                        <option disabled selected value="">Select Chapter...</option>
                    </select>
                    <select name="questions[${idx}][topic]" class="card-select bg-transparent dark:bg-slate-900 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-outline hover:border-outline-variant appearance-none cursor-pointer">
                        <option disabled selected value="">Select Topic...</option>
                    </select>
                    <select name="questions[${idx}][type]" class="card-select bg-transparent dark:bg-slate-900 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-outline hover:border-outline-variant appearance-none cursor-pointer q-type-select">
                        <option disabled selected value="">Type...</option>
                        <option value="MCQ">MCQ</option>
                        <option value="Theory">Theory</option>
                    </select>
                    <select name="questions[${idx}][difficulty]" class="card-select bg-transparent dark:bg-slate-900 border border-outline-variant/50 rounded-lg px-3 py-2 text-sm text-outline hover:border-outline-variant appearance-none cursor-pointer">
                        <option disabled selected value="">Difficulty...</option>
                        <option>Easy</option>
                        <option>Medium</option>
                        <option>Hard</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs text-on-surface-variant mb-1 font-semibold">Question Text</label>
                <input name="questions[${idx}][text]" class="card-input w-full bg-transparent border border-dashed border-outline-variant/50 rounded-lg px-3 py-2 text-sm placeholder:text-outline/50 text-on-surface dark:text-slate-200" placeholder="Type question here..." type="text">
            </div>
            <div class="mcq-section hidden mt-4 grid grid-cols-2 gap-4 bg-surface-variant/20 p-4 rounded-lg border border-outline-variant/20">
                <div class="col-span-2 text-xs text-on-surface-variant font-semibold mb-1">Answer Options</div>
                ${['A','B','C','D'].map(o=>`<div class="flex items-center gap-2"><span class="font-bold text-outline w-5 text-sm">${o}</span><input name="questions[${idx}][option_${o.toLowerCase()}]" class="card-input flex-1 bg-white border border-outline-variant/50 rounded-lg px-3 py-1.5 text-sm" placeholder="Option ${o}"></div>`).join('')}
                <div class="col-span-2 mt-1 flex items-center gap-3">
                    <label class="text-xs text-on-surface-variant font-semibold">Correct Answer:</label>
                    <select name="questions[${idx}][correct]" class="card-select bg-white border border-tertiary/30 rounded-lg px-3 py-1.5 text-sm text-tertiary font-medium appearance-none cursor-pointer">
                        <option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option>
                    </select>
                </div>
            </div>
            <div class="theory-section hidden mt-4 bg-surface-variant/20 p-4 rounded-lg border border-outline-variant/20">
                <label class="block text-xs text-on-surface-variant mb-2 font-semibold">Expected Answer / Rubric</label>
                <textarea name="questions[${idx}][rubric]" class="card-textarea w-full bg-white border border-outline-variant/50 rounded-lg px-3 py-2 text-sm resize-y min-h-[100px]" placeholder="Provide the expected answer or grading rubric..."></textarea>
            </div>
        </div>`;
        emptyCard.insertAdjacentHTML('beforebegin', tpl);
        renumber();
    });
</script>
@endpush
