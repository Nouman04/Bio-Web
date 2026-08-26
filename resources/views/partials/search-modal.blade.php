@php
    // Each pill is [key => label, icon]; the key is the group the endpoint
    // returns, so the pill filters without any mapping on the client.
    $searchTypes = $searchTypes ?? [];
    $searchPlaceholder = $searchPlaceholder ?? 'Search modules and content…';
    $searchUrl = $searchUrl ?? route('search');
@endphp

{{-- Backdrop --}}
<div id="searchModalBackdrop" class="fixed inset-0 z-[90] bg-surface-variant/40 dark:bg-slate-950/60 backdrop-blur-md hidden"></div>

{{-- Modal --}}
<div id="searchModal" class="fixed inset-0 z-[100] hidden items-start justify-center p-4 pt-16 overflow-y-auto"
    data-search-url="{{ $searchUrl }}">
    <div class="relative w-full max-w-4xl bg-surface-container-lowest dark:bg-slate-800 rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-white/50 dark:border-slate-700">

        {{-- Search Input + Filter Pills --}}
        <div class="p-8 pb-5 border-b border-outline-variant/30 dark:border-slate-700 flex flex-col gap-5">
            <div class="flex items-center justify-between gap-4">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-6 top-1/2 -translate-y-1/2 text-xl text-on-surface-variant dark:text-slate-400"></i>
                    <input id="searchModalInput" type="text" autocomplete="off"
                        class="w-full bg-surface dark:bg-slate-900 py-4 pl-16 pr-12 rounded-2xl border-none outline-none focus:ring-2 focus:ring-primary/50 text-lg text-on-background dark:text-white placeholder:text-on-surface-variant/70 dark:placeholder:text-slate-500 transition-all shadow-inner"
                        placeholder="{{ $searchPlaceholder }}">
                    <button id="searchModalClear" type="button" aria-label="Clear search"
                        class="hidden absolute right-5 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-error transition-colors">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
                <button id="searchModalClose" type="button" aria-label="Close search"
                    class="w-12 h-12 rounded-full flex items-center justify-center bg-surface-container dark:bg-slate-700 hover:bg-surface-variant dark:hover:bg-slate-600 transition-colors text-on-surface-variant dark:text-slate-300 hover:text-on-surface dark:hover:text-white shrink-0 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            @if (count($searchTypes))
                {{-- Modules are a multi-select: toggle as many as you like,
                     then press Search. --}}
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-xs font-semibold text-on-surface-variant dark:text-slate-400">
                            Modules <span id="searchTypeCount" class="font-normal text-outline">— all</span>
                        </p>
                        <button type="button" id="searchTypesClear"
                            class="hidden text-xs font-semibold text-on-surface-variant hover:text-error transition-colors">
                            Clear selection
                        </button>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        @foreach ($searchTypes as $key => $type)
                            <button type="button" data-type="{{ $key }}" aria-pressed="false"
                                class="search-pill px-4 py-1.5 rounded-full text-sm font-semibold flex items-center gap-2 transition-colors border">
                                <i class="{{ $type['icon'] }} text-sm"></i>
                                {{ $type['label'] }}
                                <i class="search-pill-tick fa-solid fa-check text-[10px]"></i>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Results --}}
        <div class="max-h-[60vh] overflow-y-auto">
            {{-- Shimmer, shown while a search is in flight --}}
            <div id="searchShimmer" class="hidden p-6 flex flex-col gap-3" aria-hidden="true">
                @for ($i = 0; $i < 5; $i++)
                    <div class="flex items-center gap-4 p-3 rounded-xl">
                        <span class="search-shimmer search-shimmer-icon"></span>
                        <span class="flex-1 flex flex-col gap-2">
                            <span class="search-shimmer" style="width: {{ [70, 55, 62, 48, 66][$i] }}%"></span>
                            <span class="search-shimmer search-shimmer-sm" style="width: {{ [35, 28, 40, 25, 32][$i] }}%"></span>
                        </span>
                    </div>
                @endfor
            </div>

            {{-- Hits --}}
            <div id="searchResults" class="hidden p-4 flex flex-col gap-6"></div>

            {{-- Prompt / no matches --}}
            <div id="searchEmpty" class="p-12 flex flex-col items-center justify-center text-center min-h-[260px]">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                    <i id="searchEmptyIcon" class="fa-solid fa-magnifying-glass text-xl"></i>
                </div>
                <h3 id="searchEmptyTitle" class="text-lg font-semibold text-on-surface dark:text-white mb-1">Search from one place</h3>
                <p id="searchEmptyText" class="text-sm text-on-surface-variant dark:text-slate-400 max-w-md">
                    Start typing at least 2 characters, then narrow the hits with the categories above.
                </p>
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
    .search-pill:hover { background-color: #e0e3e5; }
    .dark .search-pill:hover { background-color: #1e293b; }
    .search-pill.is-active {
        background-color: #001330;
        color: #ffffff;
        border-color: transparent;
    }
    .search-pill.is-active:hover { background-color: #001330; }

    /* The tick only shows on a chosen module */
    .search-pill-tick { display: none; }
    .search-pill.is-active .search-pill-tick { display: inline-block; }

    /* Shimmer placeholders, so the panel keeps its shape while results load.
       Defined here because this modal is shared by both portals. */
    .search-shimmer {
        display: block;
        height: 0.875rem;
        border-radius: 9999px;
        background: linear-gradient(90deg, rgba(118, 117, 134, 0.10) 25%, rgba(118, 117, 134, 0.18) 37%, rgba(118, 117, 134, 0.10) 63%);
        background-size: 400% 100%;
        animation: search-shimmer 1.4s ease infinite;
    }
    .search-shimmer-sm { height: 0.625rem; }
    .search-shimmer-icon { width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; flex-shrink: 0; }
    .dark .search-shimmer {
        background: linear-gradient(90deg, rgba(148, 163, 184, 0.10) 25%, rgba(148, 163, 184, 0.20) 37%, rgba(148, 163, 184, 0.10) 63%);
        background-size: 400% 100%;
    }
    @keyframes search-shimmer {
        0% { background-position: 100% 50%; }
        100% { background-position: 0 50%; }
    }

    .search-hit.is-highlighted { background-color: rgba(0, 19, 48, 0.08); }
    .dark .search-hit.is-highlighted { background-color: rgba(0, 19, 48, 0.20); }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('searchModal');
        if (!modal) return;

        const trigger = document.getElementById('searchTrigger');
        const backdrop = document.getElementById('searchModalBackdrop');
        const closeBtn = document.getElementById('searchModalClose');
        const clearBtn = document.getElementById('searchModalClear');
        const input = document.getElementById('searchModalInput');
        const shimmer = document.getElementById('searchShimmer');
        const results = document.getElementById('searchResults');
        const empty = document.getElementById('searchEmpty');
        const emptyIcon = document.getElementById('searchEmptyIcon');
        const emptyTitle = document.getElementById('searchEmptyTitle');
        const emptyText = document.getElementById('searchEmptyText');
        const searchUrl = modal.dataset.searchUrl;

        const typeCount = document.getElementById('searchTypeCount');
        const typesClear = document.getElementById('searchTypesClear');

        const GROUP_LABELS = @json($searchTypes ? collect($searchTypes)->map(fn ($t) => $t['label']) : []);
        const GROUP_ICONS = @json($searchTypes ? collect($searchTypes)->map(fn ($t) => $t['icon']) : []);

        // Chosen modules. Empty means every module.
        const types = new Set();
        let debounce = null;
        let inFlight = null;
        let hits = [];
        let cursor = -1;

        // ── Opening and closing ─────────────────────────────────────────────
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
        }

        trigger?.addEventListener('click', openSearch);
        closeBtn?.addEventListener('click', closeSearch);
        backdrop?.addEventListener('click', closeSearch);

        // ── Panel states ────────────────────────────────────────────────────
        function showShimmer() {
            shimmer.classList.remove('hidden');
            results.classList.add('hidden');
            empty.classList.add('hidden');
        }

        function showEmpty(icon, title, text) {
            shimmer.classList.add('hidden');
            results.classList.add('hidden');
            empty.classList.remove('hidden');
            emptyIcon.className = icon + ' text-xl';
            emptyTitle.textContent = title;
            emptyText.textContent = text;
        }

        function showResults() {
            shimmer.classList.add('hidden');
            empty.classList.add('hidden');
            results.classList.remove('hidden');
        }

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        }

        // ── Rendering ───────────────────────────────────────────────────────
        function render(payload) {
            results.replaceChildren();
            hits = [];
            cursor = -1;

            if (!payload.total) {
                showEmpty('fa-solid fa-magnifying-glass-minus', 'No matches',
                    `Nothing found for “${payload.term}”. Try a different word or another category.`);
                return;
            }

            Object.entries(payload.groups).forEach(([key, rows]) => {
                const group = document.createElement('div');
                group.className = 'flex flex-col gap-1';

                const heading = document.createElement('p');
                heading.className = 'px-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/70 dark:text-slate-500';
                heading.textContent = GROUP_LABELS[key] ?? key;
                group.appendChild(heading);

                rows.forEach(row => {
                    const hit = document.createElement('a');
                    hit.href = row.url;
                    hit.className = 'search-hit flex items-center gap-4 p-3 rounded-xl hover:bg-primary/5 dark:hover:bg-primary/10 transition-colors group';
                    hit.innerHTML = `
                        <span class="w-10 h-10 rounded-xl bg-surface-container dark:bg-slate-900 text-primary flex items-center justify-center shrink-0">
                            <i class="${GROUP_ICONS[key] ?? 'fa-solid fa-file-lines'}"></i>
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-semibold text-on-surface dark:text-slate-200 group-hover:text-primary transition-colors truncate">${escapeHtml(row.title)}</span>
                            <span class="block text-xs text-on-surface-variant dark:text-slate-400 truncate">${escapeHtml(row.meta)}</span>
                        </span>
                        <i class="fa-solid fa-arrow-right text-xs text-outline-variant group-hover:text-primary group-hover:translate-x-0.5 transition-all"></i>`;

                    group.appendChild(hit);
                    hits.push(hit);
                });

                results.appendChild(group);
            });

            showResults();
        }

        // ── Searching ───────────────────────────────────────────────────────
        function run() {
            const term = input.value.trim();
            clearBtn.classList.toggle('hidden', term === '');

            // Abandon a request whose answer we no longer want.
            inFlight?.abort();

            if (term.length < 2) {
                showEmpty('fa-solid fa-magnifying-glass', 'Search from one place',
                    'Start typing at least 2 characters, then narrow the hits with the categories above.');
                return;
            }

            showShimmer();

            const controller = new AbortController();
            inFlight = controller;

            const url = new URL(searchUrl, window.location.origin);
            url.searchParams.set('q', term);
            types.forEach(t => url.searchParams.append('types[]', t));

            fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: controller.signal,
            })
                .then(response => response.ok ? response.json() : Promise.reject(response))
                .then(render)
                .catch(error => {
                    if (error.name === 'AbortError') return;
                    showEmpty('fa-solid fa-triangle-exclamation', 'Search is unavailable',
                        'Something went wrong running that search. Try again in a moment.');
                });
        }

        input?.addEventListener('input', () => {
            clearTimeout(debounce);
            debounce = setTimeout(run, 250);
        });

        clearBtn?.addEventListener('click', () => {
            input.value = '';
            input.focus();
            run();
        });

        // ── Modules ─────────────────────────────────────────────────────────
        // Toggling a pill re-runs the search. The same debounce as typing
        // coalesces a quick run of toggles into one request.
        function refreshTypes() {
            typeCount.textContent = types.size
                ? '— ' + types.size + ' selected'
                : '— all';
            typesClear.classList.toggle('hidden', types.size === 0);
        }

        modal.querySelectorAll('.search-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                const key = pill.dataset.type;
                const on = !types.has(key);

                on ? types.add(key) : types.delete(key);
                pill.classList.toggle('is-active', on);
                pill.setAttribute('aria-pressed', String(on));
                refreshTypes();

                clearTimeout(debounce);
                debounce = setTimeout(run, 250);
            });
        });

        typesClear?.addEventListener('click', () => {
            types.clear();
            modal.querySelectorAll('.search-pill').forEach(p => {
                p.classList.remove('is-active');
                p.setAttribute('aria-pressed', 'false');
            });
            refreshTypes();
            run();
        });

        refreshTypes();

        // ── Keyboard ────────────────────────────────────────────────────────
        function highlight(next) {
            if (!hits.length) return;
            hits[cursor]?.classList.remove('is-highlighted');
            cursor = (next + hits.length) % hits.length;
            hits[cursor].classList.add('is-highlighted');
            hits[cursor].scrollIntoView({ block: 'nearest' });
        }

        document.addEventListener('keydown', (e) => {
            const open = !modal.classList.contains('hidden');

            // Ctrl/Cmd+K opens it from anywhere.
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                open ? closeSearch() : openSearch();
                return;
            }

            if (!open) return;

            if (e.key === 'Escape') closeSearch();
            if (e.key === 'ArrowDown') { e.preventDefault(); highlight(cursor + 1); }
            if (e.key === 'ArrowUp') { e.preventDefault(); highlight(cursor - 1); }

            if (e.key === 'Enter') {
                e.preventDefault();
                // Enter opens the highlighted hit, or re-runs the search when
                // nothing is highlighted yet.
                if (hits[cursor]) {
                    window.location.href = hits[cursor].href;
                } else {
                    clearTimeout(debounce);
                    run();
                }
            }
        });
    });
</script>
@endpush
