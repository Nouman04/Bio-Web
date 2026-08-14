@php
    $searchLinks = $searchLinks ?? [];
    $searchPlaceholder = $searchPlaceholder ?? 'Search modules and content…';
@endphp

{{-- Backdrop --}}
<div id="searchModalBackdrop" class="fixed inset-0 z-[90] bg-surface-variant/40 dark:bg-slate-950/60 backdrop-blur-md hidden"></div>

{{-- Modal --}}
<div id="searchModal" class="fixed inset-0 z-[100] hidden items-start justify-center p-4 pt-16 overflow-y-auto">
    <div class="relative w-full max-w-6xl bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-white/50 dark:border-slate-700">

        {{-- Search Input + Filter Pills --}}
        <div class="p-10 pb-6 border-b border-outline-variant/30 dark:border-slate-700 flex flex-col gap-6">
            <div class="flex items-center justify-between gap-4">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-6 top-1/2 -translate-y-1/2 text-xl text-on-surface-variant dark:text-slate-400"></i>
                    <input id="searchModalInput" type="text" autocomplete="off"
                        class="w-full bg-surface dark:bg-slate-900 py-5 pl-16 pr-4 rounded-2xl border-none outline-none focus:ring-2 focus:ring-primary/50 text-xl text-on-background dark:text-white placeholder:text-on-surface-variant/70 dark:placeholder:text-slate-500 transition-all shadow-inner"
                        placeholder="{{ $searchPlaceholder }}">
                </div>
                <button id="searchModalClose" type="button" aria-label="Close search"
                    class="w-12 h-12 rounded-full flex items-center justify-center bg-surface-container dark:bg-slate-700 hover:bg-surface-variant dark:hover:bg-slate-600 transition-colors text-on-surface-variant dark:text-slate-300 hover:text-on-surface dark:hover:text-white shrink-0 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            @if (count($searchLinks))
                <div class="flex flex-wrap items-center gap-2.5">
                    @foreach ($searchLinks as $link)
                        <button type="button"
                            class="search-pill {{ $loop->first ? 'is-active' : '' }} px-5 py-2 rounded-full text-sm font-semibold flex items-center gap-2 transition-colors border">
                            <i class="{{ $link['icon'] }} text-sm"></i>
                            {{ $link['label'] }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Empty State --}}
        <div class="p-16 flex flex-col items-center justify-center text-center bg-surface-container-lowest/50 dark:bg-slate-800/50 min-h-[340px]">
            <div class="w-full max-w-xl bg-surface/50 dark:bg-slate-900/40 border border-dashed border-outline-variant/30 dark:border-slate-700 rounded-2xl p-12">
                <h3 class="text-xl font-semibold text-on-surface dark:text-white mb-2">Search platform-related modules and content from one place</h3>
                <p class="text-base text-on-surface-variant dark:text-slate-400">Start typing at least 2 characters, then switch between the categories above.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .search-pill {
        background-color: #eceef0;
        color: #464554;
        border-color: rgba(199, 196, 215, 0.2);
    }
    .dark .search-pill {
        background-color: #0f172a;
        color: #cbd5e1;
        border-color: #334155;
    }
    .search-pill:hover {
        background-color: #e0e3e5;
    }
    .dark .search-pill:hover {
        background-color: #1e293b;
    }
    .search-pill.is-active {
        background-color: #4648d4;
        color: #ffffff;
        border-color: transparent;
    }
    .search-pill.is-active:hover {
        background-color: #4648d4;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const trigger = document.getElementById('searchTrigger');
        const modal = document.getElementById('searchModal');
        const backdrop = document.getElementById('searchModalBackdrop');
        const closeBtn = document.getElementById('searchModalClose');
        const input = document.getElementById('searchModalInput');

        function openSearch() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            backdrop.classList.remove('hidden');
            setTimeout(() => input?.focus(), 0);
        }

        function closeSearch() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            backdrop.classList.add('hidden');
            if (input) input.value = '';
        }

        trigger?.addEventListener('click', openSearch);
        closeBtn?.addEventListener('click', closeSearch);
        backdrop?.addEventListener('click', closeSearch);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeSearch();
        });

        // Filter pills are category toggles for the (not-yet-wired) search
        // results — purely visual, single-select, and never navigate.
        modal.querySelectorAll('.search-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                modal.querySelectorAll('.search-pill').forEach(p => p.classList.remove('is-active'));
                pill.classList.add('is-active');
            });
        });
    });
</script>
@endpush
