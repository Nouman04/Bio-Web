@extends('layouts.student')

@section('title', 'Educational Diagrams – Chapter ' . $chapterId)
@section('meta-description', 'Visual diagrams and illustrations for this chapter')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .glass-card {
        background: rgba(255,255,255,0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.4);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        box-shadow: 0 10px 30px rgba(99,102,241,0.08);
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')

{{-- Breadcrumb + Header --}}
<div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-outline-variant/30 mb-8 pt-4">
    <div>
        <nav class="flex items-center gap-1.5 text-xs text-on-surface-variant mb-3 flex-wrap">
            <a href="{{ route('student.courses') }}" class="hover:text-primary transition-colors">My Courses</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.courses.show', ['id' => $courseId]) }}" class="hover:text-primary transition-colors">Course {{ $courseId }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <a href="{{ route('student.chapters.show', ['courseId' => $courseId, 'chapterId' => $chapterId]) }}" class="hover:text-primary transition-colors">Chapter {{ $chapterId }}</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span class="text-on-surface font-semibold">Diagrams</span>
        </nav>
        <h1 class="text-on-background" style="font-size:32px;line-height:40px;font-weight:700;letter-spacing:-0.02em;">Educational Diagrams</h1>
        <p class="text-on-surface-variant text-base mt-2 max-w-2xl">High-resolution visual aids for complex processes in Chapter {{ $chapterId }}.</p>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative">
            <select class="appearance-none bg-surface-container-lowest border border-outline-variant text-on-surface text-sm rounded-lg py-2.5 pl-4 pr-8 focus:ring-primary focus:border-primary hover:bg-surface-container-low transition-colors cursor-pointer">
                <option>All Courses</option>
                <option>Computer Science 101</option>
                <option>UI/UX Masterclass</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-on-surface-variant">
                <span class="material-symbols-outlined" style="font-size:20px;">arrow_drop_down</span>
            </div>
        </div>
        <div class="relative">
            <select class="appearance-none bg-surface-container-lowest border border-outline-variant text-on-surface text-sm rounded-lg py-2.5 pl-4 pr-8 focus:ring-primary focus:border-primary hover:bg-surface-container-low transition-colors cursor-pointer">
                <option>All Chapters</option>
                <option selected>Chapter {{ $chapterId }}</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-on-surface-variant">
                <span class="material-symbols-outlined" style="font-size:20px;">arrow_drop_down</span>
            </div>
        </div>
        <button class="flex items-center justify-center gap-2 bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:text-primary hover:border-primary hover:bg-primary/5 p-2.5 rounded-lg transition-all">
            <span class="material-symbols-outlined" style="font-size:20px;">sort</span>
        </button>
    </div>
</div>

{{-- Gallery Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

    {{-- Card 1 --}}
    <div class="glass-card rounded-xl overflow-hidden flex flex-col group relative">
        <div class="aspect-video relative overflow-hidden bg-surface-container-lowest border-b border-outline-variant/20">
            <img alt="Network Architecture Diagram" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuC3w0oGJ_0Ne6N89tMbUulMSlPoA5YxbzjzJUspil_FQuJfLdtgATPI1Q75NRfcKfYAdpJMoYm_2jHWjGWm1788qduXhSyKmbrCjGi5IAAWUTE3M7kfiUdMQ7DjMfM-LyKfKeg11CHJF0ma62dbJ7URxIpiyx9wno6ucI0mupgbVa8c5Z4HRmcYH-OxfQEkkz415C7PFGPiokC7nKbFSyIeJQMS3pSJtWFSmbZi14ysBp1k0pE20eSx"/>
            <div class="absolute top-3 left-3 bg-tertiary-container/90 backdrop-blur text-on-tertiary text-xs font-bold px-2 py-1 rounded-md flex items-center gap-1 shadow-sm">
                <span class="material-symbols-outlined" style="font-size:14px;">account_tree</span>
                Neural Nets
            </div>
        </div>
        <div class="p-5 flex flex-col flex-1">
            <h3 class="text-on-surface font-semibold text-base mb-2 line-clamp-2 group-hover:text-primary transition-colors">Network Architecture Diagram</h3>
            <p class="text-on-surface-variant text-xs mb-4 line-clamp-2">Layered view of inputs, hidden layers, and output nodes with connection weights annotated.</p>
            <div class="mt-auto pt-4 border-t border-outline-variant/20 flex items-center justify-between">
                <span class="text-outline text-xs">Added 2 days ago</span>
                <a href="{{ route('student.chapters.diagrams.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'diagramId' => 1]) }}" class="text-primary text-xs font-semibold hover:text-primary-container flex items-center gap-1 group/btn transition-colors">
                    View Full
                    <span class="material-symbols-outlined text-sm group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Card 2 --}}
    <div class="glass-card rounded-xl overflow-hidden flex flex-col group relative">
        <div class="aspect-video relative overflow-hidden bg-surface-container-lowest border-b border-outline-variant/20">
            <img alt="Gradient Descent Flow" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSss4cPy7OHyIM6UaKpoM3jUnkwByaBNvQClwnqDjiYb82CDsNlw41tZXHn7iPlTz5sG451ruvuEZbLXOrc4s738MOkxfUsSb4vEZAwb86j-ZpGAmXc76OAJ0q3LdSKHp8aS5J2V9drlO7CnyrHRl3mt6RvJsCPmnthQBaJuWZIXiQZXSN_wbegxal5Y3G6A1MIG_suUs9W5SElEo-Uuy2_MI8JGt-X9rnY2-UIT107W68GjrN58_S"/>
            <div class="absolute top-3 left-3 bg-secondary-container/90 backdrop-blur text-on-secondary-container text-xs font-bold px-2 py-1 rounded-md flex items-center gap-1 shadow-sm">
                <span class="material-symbols-outlined" style="font-size:14px;">schema</span>
                Optimization
            </div>
        </div>
        <div class="p-5 flex flex-col flex-1">
            <h3 class="text-on-surface font-semibold text-base mb-2 line-clamp-2 group-hover:text-primary transition-colors">Gradient Descent Flow</h3>
            <p class="text-on-surface-variant text-xs mb-4 line-clamp-2">Step-by-step visualization of weight updates descending a loss surface towards minimum.</p>
            <div class="mt-auto pt-4 border-t border-outline-variant/20 flex items-center justify-between">
                <span class="text-outline text-xs">Added 1 week ago</span>
                <a href="{{ route('student.chapters.diagrams.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'diagramId' => 1]) }}" class="text-primary text-xs font-semibold hover:text-primary-container flex items-center gap-1 group/btn transition-colors">
                    View Full
                    <span class="material-symbols-outlined text-sm group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Card 3 --}}
    <div class="glass-card rounded-xl overflow-hidden flex flex-col group relative">
        <div class="aspect-video relative overflow-hidden bg-surface-container-lowest border-b border-outline-variant/20">
            <img alt="Backpropagation Diagram" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuB7TjuIgojevZaNze2qHpUXqD1_QyoK9qKNlQihxyuP5HdMzGcI2ONVq6h-q5ZF-RWQgOpMvI9Kfm1efDkcflZjKr3QmQvNatx1doW44a1Qh2kjoiBUA2sMhWkxuh1R7n3uQlotyPwPsd48nJ3jlGi_cHNAAPHrIdhqbiNJ8TwlykaURZLiFhp-CRafX-4udIk_c4Ds_aseJd7vUzpz7M2Nw8uQH1cxVuurp4oKeo65egNvj-lbYLZr"/>
            <div class="absolute top-3 left-3 bg-tertiary-container/90 backdrop-blur text-on-tertiary text-xs font-bold px-2 py-1 rounded-md flex items-center gap-1 shadow-sm">
                <span class="material-symbols-outlined" style="font-size:14px;">device_hub</span>
                Training
            </div>
        </div>
        <div class="p-5 flex flex-col flex-1">
            <h3 class="text-on-surface font-semibold text-base mb-2 line-clamp-2 group-hover:text-primary transition-colors">Backpropagation Chain Rule</h3>
            <p class="text-on-surface-variant text-xs mb-4 line-clamp-2">Annotated diagram tracing partial derivatives from output loss back through every layer.</p>
            <div class="mt-auto pt-4 border-t border-outline-variant/20 flex items-center justify-between">
                <span class="text-outline text-xs">Added 2 weeks ago</span>
                <a href="{{ route('student.chapters.diagrams.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'diagramId' => 1]) }}" class="text-primary text-xs font-semibold hover:text-primary-container flex items-center gap-1 group/btn transition-colors">
                    View Full
                    <span class="material-symbols-outlined text-sm group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Card 4 --}}
    <div class="glass-card rounded-xl overflow-hidden flex flex-col group relative">
        <div class="aspect-video relative overflow-hidden bg-surface-container-lowest border-b border-outline-variant/20">
            <img alt="Activation Functions Comparison" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuAnV_QzTpAb1OwioSzq3fYIhHlhuwGnn6FbCFqLqMTbIodBty0VwQSCZeOrto8cjWK3sBLXhrHmCidt1kdsxD_Q9pDaDAcpWx5yBiv8BpfCbBH0e_3duXJJQ7E1nCRUE9nXBPZOr-oIc-WBo3uUSEGCT-8MvKFZgV3FJw2WyGyYrZihpixxjXvO1HD_hGUmEbZmD9UtqYaefEqcQEM3hh1VFu5NMSNbsNTf0kG7cebsugc_3TkASSah"/>
            <div class="absolute top-3 left-3 bg-primary/90 backdrop-blur text-on-primary text-xs font-bold px-2 py-1 rounded-md flex items-center gap-1 shadow-sm">
                <span class="material-symbols-outlined" style="font-size:14px;">functions</span>
                Math
            </div>
        </div>
        <div class="p-5 flex flex-col flex-1">
            <h3 class="text-on-surface font-semibold text-base mb-2 line-clamp-2 group-hover:text-primary transition-colors">Activation Functions Comparison</h3>
            <p class="text-on-surface-variant text-xs mb-4 line-clamp-2">Side-by-side plots of Sigmoid, Tanh, ReLU and Leaky ReLU across the real number line.</p>
            <div class="mt-auto pt-4 border-t border-outline-variant/20 flex items-center justify-between">
                <span class="text-outline text-xs">Added 1 month ago</span>
                <a href="{{ route('student.chapters.diagrams.show', ['courseId' => $courseId, 'chapterId' => $chapterId, 'diagramId' => 1]) }}" class="text-primary text-xs font-semibold hover:text-primary-container flex items-center gap-1 group/btn transition-colors">
                    View Full
                    <span class="material-symbols-outlined text-sm group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>

</div>

@endsection
