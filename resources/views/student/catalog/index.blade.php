@extends('layouts.student')

@section('title', 'Explore Courses – Catalog')
@section('meta-description', 'Discover your next learning adventure — browse all available courses')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0px 10px 30px rgba(99,102,241,0.08);
        border-color: rgba(99,102,241,0.2);
    }
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-primary-container/20 to-surface-container-lowest p-8 md:p-12 border border-white/50 glass-card mt-4 mb-8">
    <div class="relative z-10 max-w-2xl">
        <h2 class="text-on-surface mb-4" style="font-size:48px;line-height:56px;letter-spacing:-0.02em;font-weight:700;">Discover Your Next Learning Adventure</h2>
        <p class="text-on-surface-variant mb-8" style="font-size:18px;line-height:28px;">Explore thousands of high-quality courses designed to elevate your skills in tech, design, business, and beyond.</p>
        <div class="flex flex-wrap gap-4">
            <button class="bg-primary text-on-primary px-6 py-3 rounded-full text-sm font-semibold shadow-md hover:shadow-lg transition-all hover:-translate-y-0.5 bg-gradient-to-r from-primary to-primary-container flex items-center gap-2">
                Start Exploring
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </button>
        </div>
    </div>
    <!-- Decorative blobs -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-primary/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
    <div class="absolute bottom-0 right-20 w-48 h-48 bg-secondary-container/20 rounded-full blur-2xl translate-y-1/3 pointer-events-none"></div>
</section>

<!-- Filters & Sorting Bar -->
<section class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-4 border-b border-surface-variant mb-8">
    <!-- Category Pills -->
    <div class="flex overflow-x-auto pb-2 md:pb-0 gap-2 hide-scrollbar w-full md:w-auto" id="categoryPills">
        <button onclick="filterCategory(this, 'all')"
            class="px-4 py-1.5 rounded-full bg-primary text-on-primary text-xs font-semibold whitespace-nowrap shadow-sm category-pill active">
            All Courses
        </button>
        <button onclick="filterCategory(this, 'Development')"
            class="px-4 py-1.5 rounded-full bg-surface-container-lowest border border-outline-variant text-on-surface-variant text-xs font-semibold whitespace-nowrap hover:bg-surface-variant transition-colors category-pill">
            Development
        </button>
        <button onclick="filterCategory(this, 'UI/UX Design')"
            class="px-4 py-1.5 rounded-full bg-surface-container-lowest border border-outline-variant text-on-surface-variant text-xs font-semibold whitespace-nowrap hover:bg-surface-variant transition-colors category-pill">
            UI/UX Design
        </button>
        <button onclick="filterCategory(this, 'Data Science')"
            class="px-4 py-1.5 rounded-full bg-surface-container-lowest border border-outline-variant text-on-surface-variant text-xs font-semibold whitespace-nowrap hover:bg-surface-variant transition-colors category-pill">
            Data Science
        </button>
        <button onclick="filterCategory(this, 'Business')"
            class="px-4 py-1.5 rounded-full bg-surface-container-lowest border border-outline-variant text-on-surface-variant text-xs font-semibold whitespace-nowrap hover:bg-surface-variant transition-colors category-pill">
            Business
        </button>
        <button onclick="filterCategory(this, 'Marketing')"
            class="px-4 py-1.5 rounded-full bg-surface-container-lowest border border-outline-variant text-on-surface-variant text-xs font-semibold whitespace-nowrap hover:bg-surface-variant transition-colors category-pill">
            Marketing
        </button>
    </div>
    <!-- Sort Dropdown -->
    <div class="flex-shrink-0 flex items-center gap-2 self-end md:self-auto">
        <span class="text-on-surface-variant text-xs">Sort by:</span>
        <div class="relative">
            <select class="appearance-none bg-surface-container-lowest border border-outline-variant text-on-surface text-xs rounded-lg pl-3 pr-8 py-1.5 focus:ring-primary focus:border-primary shadow-sm cursor-pointer">
                <option>Most Popular</option>
                <option>Highest Rated</option>
                <option>Newest</option>
            </select>
            <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-outline text-sm">expand_more</span>
        </div>
    </div>
</section>

<!-- Course Grid -->
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="courseGrid">

    <!-- Card 1 -->
    <article class="glass-card rounded-xl overflow-hidden flex flex-col h-full group" data-category="Development">
        <div class="relative h-40 overflow-hidden">
            <img alt="Advanced React" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuBOj6w5Ded1S-hu1PEmaZ6qfXyG2zqpgNxAczR80MaT6U_24_QsmjdpSRT28UUZKgfW3Oyr1LRfOQYGhLxPcGG-sj-eZ2hrPh0DWqPrZKOSgH14vJ9-O15S6cnYjkIdhYa4YAsbXK9Ur1lXG2HXNs6sjSynsULeUXPitpGX_YOlCbyX5PhuWaUUFWXPPxWwXD5GjXbNZRFYs5035dn_AbsH7ubdDgardgI5YhXiS1yYwT348EULkEYp"/>
            <div class="absolute top-3 left-3 bg-tertiary-container text-on-tertiary text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded">Development</div>
        </div>
        <div class="p-4 flex flex-col flex-1">
            <h3 class="text-on-surface mb-2 line-clamp-2 leading-tight" style="font-size:20px;line-height:28px;font-weight:600;">Advanced React &amp; State Management Patterns</h3>
            <p class="text-on-surface-variant mb-4 line-clamp-2 text-sm">Master complex UI state architecture and build scalable web applications from scratch.</p>
            <div class="mt-auto pt-4 border-t border-surface-variant flex items-center justify-between">
                <div class="flex items-center gap-1 text-secondary-container">
                    <span class="material-symbols-outlined text-sm">star</span>
                    <span class="text-on-surface text-xs font-medium">4.9</span>
                    <span class="text-outline text-xs">(1.2k)</span>
                </div>
                <button class="text-primary text-xs font-semibold hover:underline flex items-center gap-1">
                    Enroll Now <span class="material-symbols-outlined text-xs">arrow_forward</span>
                </button>
            </div>
        </div>
    </article>

    <!-- Card 2 -->
    <article class="glass-card rounded-xl overflow-hidden flex flex-col h-full group" data-category="UI/UX Design">
        <div class="relative h-40 overflow-hidden">
            <img alt="UI/UX Design" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuDr0HOYOIYGalxSr3XbU_kMtPHxJiPVAHB-5zkVjQCHyo4TVdqqDujnRqNAezkd7OQMaTuSv_NNIz6DKvu-Q6l-ERXvJ-A1dTtyy8Vsqo-EtXwM0YcY4dKmFpU-m8CXOyoNsIuC_Wl1QXDvbYtwR7j_-_Lw0iHUFx4VfCAX9M26sXrxNywloeEYMxJEgreoji5DSqZkVcK8FdLMda1Gg6aWB1gBqPRteaALnX-ir_OBxxAZc_nqonU8"/>
            <div class="absolute top-3 left-3 bg-secondary-container text-on-secondary-container text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded">UI/UX Design</div>
        </div>
        <div class="p-4 flex flex-col flex-1">
            <h3 class="text-on-surface mb-2 line-clamp-2 leading-tight" style="font-size:20px;line-height:28px;font-weight:600;">Designing Delightful User Experiences</h3>
            <p class="text-on-surface-variant mb-4 line-clamp-2 text-sm">Learn the psychology behind user behavior and create interfaces that users love.</p>
            <div class="mt-auto pt-4 border-t border-surface-variant flex items-center justify-between">
                <div class="flex items-center gap-1 text-secondary-container">
                    <span class="material-symbols-outlined text-sm">star</span>
                    <span class="text-on-surface text-xs font-medium">4.8</span>
                    <span class="text-outline text-xs">(845)</span>
                </div>
                <button class="text-primary text-xs font-semibold hover:underline flex items-center gap-1">
                    Enroll Now <span class="material-symbols-outlined text-xs">arrow_forward</span>
                </button>
            </div>
        </div>
    </article>

    <!-- Card 3 -->
    <article class="glass-card rounded-xl overflow-hidden flex flex-col h-full group" data-category="Data Science">
        <div class="relative h-40 overflow-hidden">
            <img alt="Data Science" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuAGGuRaP26sR_L-RgUsugWVYf1w76cHyXSTI5ImBFWDg0UAPhT42_HrFVxjYUfRXS4-ktbkqplq7ytS_H-94A9qOpVSrOgaD79Lb3HgCzBusaeSX8d5ror2BF_9yty9prtfONwnLf9wsWSh9wnmPmmCvnX5MIGcLU_TD8H5P1wkmg6mfrZNZVZICaa03NlWP2Q9cLxFhsLBnytDtnSo4qiEKFSwVIo6VbFIoJGvBWUMGDkgvdcxLY_Z"/>
            <div class="absolute top-3 left-3 bg-primary text-on-primary text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded">Data Science</div>
        </div>
        <div class="p-4 flex flex-col flex-1">
            <h3 class="text-on-surface mb-2 line-clamp-2 leading-tight" style="font-size:20px;line-height:28px;font-weight:600;">Python for Data Analysis &amp; Machine Learning</h3>
            <p class="text-on-surface-variant mb-4 line-clamp-2 text-sm">A comprehensive guide to Pandas, NumPy, and Scikit-Learn for aspiring data scientists.</p>
            <div class="mt-auto pt-4 border-t border-surface-variant flex items-center justify-between">
                <div class="flex items-center gap-1 text-secondary-container">
                    <span class="material-symbols-outlined text-sm">star</span>
                    <span class="text-on-surface text-xs font-medium">4.7</span>
                    <span class="text-outline text-xs">(2.1k)</span>
                </div>
                <button class="text-primary text-xs font-semibold hover:underline flex items-center gap-1">
                    Enroll Now <span class="material-symbols-outlined text-xs">arrow_forward</span>
                </button>
            </div>
        </div>
    </article>

    <!-- Card 4 -->
    <article class="glass-card rounded-xl overflow-hidden flex flex-col h-full group" data-category="Business">
        <div class="relative h-40 overflow-hidden">
            <img alt="Business" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuCPFFJ2Zfom_OIj7hsTpUHR50erqhD0zYQt21EyLbez8cdu26x1Fr18RU5N2qBHsLDgv5Av3BCnbWhpAoj21njMOfqZbdABhrXnOKXIDHD3WNjmq-dneQxaBffZQpuN9RKnG3REJz4Jp9AKyOeNWL_A0gA5rDZ15QJPOdnCT4ZX7OjH4QqlopHAWKdkdxkNku-nlcAQyumtDqfMO5wUYwxT81TF4Jk_NuEkfQF8MYx2W_B5ngWYB6LG"/>
            <div class="absolute top-3 left-3 bg-error text-on-error text-xs font-bold uppercase tracking-wide px-2 py-0.5 rounded">Business</div>
        </div>
        <div class="p-4 flex flex-col flex-1">
            <h3 class="text-on-surface mb-2 line-clamp-2 leading-tight" style="font-size:20px;line-height:28px;font-weight:600;">Strategic Leadership &amp; Agile Management</h3>
            <p class="text-on-surface-variant mb-4 line-clamp-2 text-sm">Develop the critical thinking skills needed to lead high-performing teams in dynamic environments.</p>
            <div class="mt-auto pt-4 border-t border-surface-variant flex items-center justify-between">
                <div class="flex items-center gap-1 text-secondary-container">
                    <span class="material-symbols-outlined text-sm">star</span>
                    <span class="text-on-surface text-xs font-medium">4.9</span>
                    <span class="text-outline text-xs">(530)</span>
                </div>
                <button class="text-primary text-xs font-semibold hover:underline flex items-center gap-1">
                    Enroll Now <span class="material-symbols-outlined text-xs">arrow_forward</span>
                </button>
            </div>
        </div>
    </article>

</section>

<!-- Load More -->
<div class="flex justify-center pt-8 pb-4">
    <button class="px-6 py-2 border border-outline text-on-surface text-xs font-semibold rounded-full hover:bg-surface-variant transition-colors flex items-center gap-2">
        Load More Courses
        <span class="material-symbols-outlined text-sm">refresh</span>
    </button>
</div>
@endsection

@push('scripts')
<script>
    function filterCategory(btn, category) {
        // Update pill styles
        document.querySelectorAll('.category-pill').forEach(p => {
            p.classList.remove('bg-primary','text-on-primary');
            p.classList.add('bg-surface-container-lowest','border','border-outline-variant','text-on-surface-variant');
        });
        btn.classList.add('bg-primary','text-on-primary');
        btn.classList.remove('bg-surface-container-lowest','border','border-outline-variant','text-on-surface-variant');

        // Show/hide cards
        document.querySelectorAll('#courseGrid article').forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endpush
