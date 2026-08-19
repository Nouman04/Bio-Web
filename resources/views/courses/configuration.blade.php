@extends('layouts.app')

@section('title', 'Configuration — ' . $course->title)
@section('meta-description', 'Choose which chapters of this course are public.')

@section('page-title', 'Configuration')
@section('page-subtitle', $course->title)

@push('styles')
<style>
    /* ── Public / private switch ─────────────────────────────────────────────
       A checkbox styled as a two-state switch: the knob slides right and the
       track turns primary when the chapter is public. */
    .visibility-row { transition: background-color 0.15s ease, border-color 0.15s ease; }
    .visibility-row:hover { background: rgba(70, 72, 212, 0.03); }
    .dark .visibility-row:hover { background: rgba(70, 72, 212, 0.10); }

    .visibility-switch {
        position: relative;
        width: 3.25rem;
        height: 1.75rem;
        border-radius: 9999px;
        background: rgba(118, 117, 134, 0.28);
        transition: background-color 0.2s ease;
        flex-shrink: 0;
    }
    .visibility-switch::after {
        content: '';
        position: absolute;
        top: 0.1875rem;
        left: 0.1875rem;
        width: 1.375rem;
        height: 1.375rem;
        border-radius: 9999px;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease;
    }
    .visibility-input:checked + .visibility-switch { background: #4648d4; }
    .visibility-input:checked + .visibility-switch::after { transform: translateX(1.5rem); }
    .visibility-input:focus-visible + .visibility-switch { outline: 2px solid #4648d4; outline-offset: 2px; }

    /* The label beside the switch reads the state, so it is coloured with it */
    .visibility-state { color: rgb(118, 117, 134); }
    .visibility-input:checked ~ .visibility-state { color: #4648d4; }

    /* Nothing is saved until Update is pressed, so changed rows are marked */
    .visibility-row.is-dirty { background: rgba(70, 72, 212, 0.04); }
    .visibility-row.is-dirty .visibility-dirty { opacity: 1; }
    .visibility-dirty { opacity: 0; transition: opacity 0.15s ease; }
</style>
@endpush

@section('content')
    {{-- Breadcrumbs --}}
    <div class="flex items-center text-xs font-medium text-on-surface-variant dark:text-slate-400 gap-2 mb-6">
        <a class="hover:text-primary transition-colors" href="{{ route('dashboard') }}">Home</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses') }}">Courses</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a class="hover:text-primary transition-colors" href="{{ route('courses.chapters', $course) }}">{{ $course->title }}</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-primary dark:text-primary-fixed-dim font-semibold">Configuration</span>
    </div>

    <form id="course-configuration-form" data-ajax-form method="POST"
        action="{{ route('courses.configuration.update', $course) }}" class="flex flex-col gap-6">
        @csrf
        @method('PUT')

        <div class="glass-panel bg-surface-container-lowest dark:bg-slate-800 rounded-3xl border border-outline-variant/30 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-outline-variant/30 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-on-surface dark:text-white">Chapter Visibility</h3>
                    <p class="text-xs text-on-surface-variant dark:text-slate-400 mt-1">
                        Public chapters are open to students. Private ones stay visible to staff only.
                    </p>
                </div>
                @if($chapters->isNotEmpty())
                    <div class="flex items-center gap-2">
                        <button type="button" data-bulk="public"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold border border-outline-variant/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 hover:text-primary hover:border-primary/40 transition-colors">
                            All public
                        </button>
                        <button type="button" data-bulk="private"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold border border-outline-variant/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 hover:text-primary hover:border-primary/40 transition-colors">
                            All private
                        </button>
                    </div>
                @endif
            </div>

            @forelse($chapters as $chapter)
                @php $isPublic = $chapter->visibility === 'public'; @endphp
                <label class="visibility-row flex items-center gap-4 px-6 py-4 border-b border-outline-variant/10 dark:border-slate-700/60 last:border-b-0 cursor-pointer"
                    data-initial="{{ $chapter->visibility }}">
                    <span class="inline-flex items-center justify-center min-w-[2rem] h-7 px-2 rounded-lg bg-surface-container-low dark:bg-slate-900 border border-outline-variant/30 dark:border-slate-700 text-xs font-bold text-on-surface dark:text-slate-200 shrink-0">
                        {{ $chapter->chapter_number }}
                    </span>

                    <span class="flex-1 min-w-0 flex flex-col gap-0.5">
                        <span class="font-semibold text-on-surface dark:text-white truncate">{{ $chapter->title }}</span>
                        <span class="text-xs text-on-surface-variant dark:text-slate-400">
                            {{ $chapter->status }}
                            <span class="visibility-dirty text-primary font-semibold">· unsaved</span>
                        </span>
                    </span>

                    {{-- An unchecked box is not posted at all, so this carries
                         the private case; the checkbox overrides it when on. --}}
                    <input type="hidden" name="chapters[{{ $chapter->id }}]" value="private">
                    <input type="checkbox" class="visibility-input sr-only"
                        name="chapters[{{ $chapter->id }}]" value="public" {{ $isPublic ? 'checked' : '' }}>
                    <span class="visibility-switch"></span>
                    <span class="visibility-state text-xs font-semibold w-14 text-right">{{ $isPublic ? 'Public' : 'Private' }}</span>
                </label>
            @empty
                <div class="px-6 py-16 text-center">
                    <div class="w-14 h-14 mx-auto bg-primary-container/20 text-primary rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-book-bookmark text-xl"></i>
                    </div>
                    <h4 class="text-base font-semibold text-on-surface dark:text-white mb-1">No chapters yet</h4>
                    <p class="text-sm text-on-surface-variant dark:text-slate-400">
                        Add chapters to this course and their visibility will be configurable here.
                    </p>
                </div>
            @endforelse
        </div>

        <div class="flex items-center justify-between gap-3 flex-wrap">
            <span id="config-summary" class="text-xs font-medium text-on-surface-variant dark:text-slate-400"></span>
            <div class="flex items-center gap-3">
                <a href="{{ route('courses') }}"
                    class="px-6 py-2.5 rounded-full border border-outline-variant/60 dark:border-slate-600 text-on-surface-variant dark:text-slate-400 text-sm font-semibold hover:bg-surface-container-high dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </a>
                <button type="submit" data-loading-text="Updating…" @disabled($chapters->isEmpty())
                    class="px-6 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white text-sm font-semibold shadow-md shadow-primary/20 hover:shadow-lg hover:-translate-y-0.5 transition-all inline-flex items-center gap-2 disabled:opacity-50 disabled:pointer-events-none">
                    <i class="fa-solid fa-circle-check text-xs"></i>
                    Update Configuration
                </button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('course-configuration-form');
            const rows = Array.from(form.querySelectorAll('.visibility-row'));
            const summary = document.getElementById('config-summary');

            function refresh() {
                let publicCount = 0;
                let changed = 0;

                rows.forEach(row => {
                    const input = row.querySelector('.visibility-input');
                    const now = input.checked ? 'public' : 'private';

                    row.querySelector('.visibility-state').textContent = input.checked ? 'Public' : 'Private';
                    row.classList.toggle('is-dirty', now !== row.dataset.initial);

                    if (input.checked) publicCount++;
                    if (now !== row.dataset.initial) changed++;
                });

                summary.textContent = rows.length
                    ? `${publicCount} of ${rows.length} chapters public`
                        + (changed ? ` · ${changed} unsaved change${changed === 1 ? '' : 's'}` : '')
                    : '';
            }

            form.addEventListener('change', (e) => {
                if (e.target.classList.contains('visibility-input')) refresh();
            });

            form.querySelectorAll('[data-bulk]').forEach(button => {
                button.addEventListener('click', () => {
                    const wantPublic = button.dataset.bulk === 'public';
                    rows.forEach(row => { row.querySelector('.visibility-input').checked = wantPublic; });
                    refresh();
                });
            });

            // Saved: the current state becomes the new baseline, so the
            // "unsaved" marks clear without a reload.
            form.addEventListener('ajax:success', () => {
                rows.forEach(row => {
                    row.dataset.initial = row.querySelector('.visibility-input').checked ? 'public' : 'private';
                });
                refresh();
            });

            refresh();
        });
    </script>
@endpush
