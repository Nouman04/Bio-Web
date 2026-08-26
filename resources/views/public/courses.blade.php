@extends('public.layouts.app')

@section('title', 'Courses | Lumina LMS')

@push('styles')
<link href="{{ asset('cdn/tom-select/tomSelect.css') }}" rel="stylesheet"/>
<style>
    /* ── Instructor picker ───────────────────────────────────────────────────
       Tom Select ships its own chrome, so it is reshaped to match the glass
       inputs the rest of the site uses. */
    .ts-wrapper .ts-control {
        border: 1px solid #c6c5d7;
        border-radius: 9999px;
        padding: 0.65rem 1.15rem;
        background: #ffffff;
        font-size: 15px;
        box-shadow: none;
        min-height: 50px;
    }
    .ts-wrapper.focus .ts-control {
        border-color: #001330;
        box-shadow: 0 0 0 3px rgba(0, 19, 48, 0.15);
    }
    .ts-wrapper .ts-control > .item {
        background: #c2daff;
        color: #001330;
        border: none;
        border-radius: 9999px;
        padding: 2px 10px;
        font-size: 13px;
        font-weight: 600;
    }
    .ts-dropdown {
        border: 1px solid #c6c5d7;
        border-radius: 1rem;
        box-shadow: 0 12px 32px -8px rgba(0, 19, 48, 0.25);
        overflow: hidden;
    }
    .ts-dropdown .option { padding: 0.6rem 1rem; font-size: 14px; }
    .ts-dropdown .active { background: rgba(0, 19, 48, 0.08); color: #001330; }
</style>
@endpush

@section('content')
<main class="relative">
    <!-- Page Header -->
    <section class="pt-xl pb-lg relative z-10">
        <div class="absolute inset-0 bg-gradient-to-b from-primary/5 to-transparent -z-10"></div>
        <div class="max-w-container-max mx-auto px-md md:px-lg text-center">
            <h1 class="font-display-lg text-headline-lg md:text-display-lg text-on-surface mb-4">Our Courses</h1>
            <p class="text-on-surface-variant max-w-2xl mx-auto font-body-md text-lg">
                Browse every module in the library. Search by title, or narrow the list to the instructors you want.
            </p>
        </div>
    </section>

    <!-- Filters -->
    <section class="relative z-20 pb-lg">
        <div class="max-w-container-max mx-auto px-md md:px-lg">
            <form method="GET" action="{{ route('public.courses') }}"
                class="glass-panel rounded-3xl p-6 md:p-8 flex flex-col lg:flex-row gap-4 lg:items-end">
                <div class="flex-1 flex flex-col gap-2">
                    <label class="font-label-sm text-on-surface-variant" for="search">Search by title</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                        <input id="search" name="search" type="text" value="{{ $filters['search'] }}"
                            placeholder="e.g. Cell Biology"
                            class="w-full pl-12 pr-4 py-3 rounded-full border border-outline-variant bg-white text-on-surface placeholder:text-outline focus:border-primary focus:ring-2 focus:ring-primary/15 outline-none font-body-md">
                    </div>
                </div>

                <div class="flex-1 flex flex-col gap-2">
                    <label class="font-label-sm text-on-surface-variant" for="instructors">Instructors</label>
                    <select id="instructors" name="instructors[]" multiple placeholder="Any instructor">
                        @foreach($filters['instructors'] as $instructor)
                            <option value="{{ $instructor->uuid }}" selected>{{ $instructor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="bg-primary text-on-primary px-8 py-3 rounded-full font-label-md hover:bg-primary-container transition-all duration-300 active:scale-95 shadow-[0_4px_14px_0_rgba(0, 19, 48, 0.39)] whitespace-nowrap">
                        Search
                    </button>
                    @if($filters['search'] || $filters['instructors']->isNotEmpty())
                        <a href="{{ route('public.courses') }}"
                            class="text-on-surface-variant hover:text-primary font-label-md transition-colors whitespace-nowrap">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    <!-- Gallery -->
    <section class="pb-2xl relative z-10">
        <div class="max-w-container-max mx-auto px-md md:px-lg">
            <p class="font-label-md text-on-surface-variant mb-8">
                {{ $courses->total() }} {{ Str::plural('course', $courses->total()) }} found
                @if($courses->hasPages())
                    · page {{ $courses->currentPage() }} of {{ $courses->lastPage() }}
                @endif
            </p>

            @if($courses->isEmpty())
                <div class="glass-panel rounded-3xl p-16 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-primary-fixed to-white flex items-center justify-center text-primary mb-6">
                        <span class="material-symbols-outlined text-[32px]">search_off</span>
                    </div>
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-2">No courses match your search</h3>
                    <p class="text-on-surface-variant font-body-md">Try a different title, or clear the instructor filter.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($courses as $course)
                        <article class="glass-panel rounded-3xl overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 group">
                            <div class="h-44 relative overflow-hidden bg-gradient-to-br from-primary-fixed to-white flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary text-[56px] group-hover:scale-110 transition-transform duration-500">science</span>
                                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-primary shadow-sm">
                                    {{ $course['category'] ?: 'Course' }}
                                </div>
                            </div>
                            <div class="p-6 flex-1 flex flex-col">
                                <h2 class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors mb-2 line-clamp-2">
                                    {{ $course['title'] }}
                                </h2>
                                <p class="text-on-surface-variant text-body-md mb-6 line-clamp-3">{{ $course['excerpt'] }}</p>

                                <div class="mt-auto pt-5 border-t border-outline-variant/20 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary-fixed text-primary flex items-center justify-center font-label-md font-bold shrink-0">
                                        {{ Str::upper(Str::substr($course['instructor'] ?? '?', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-label-md text-on-surface truncate">{{ $course['instructor'] ?? 'Lumina LMS' }}</p>
                                        <p class="font-label-sm text-on-surface-variant">
                                            {{ $course['chapters_count'] }} {{ Str::plural('chapter', $course['chapters_count']) }}
                                        </p>
                                    </div>
                                    <a class="bg-primary text-on-primary px-5 py-2 rounded-full font-label-md hover:bg-primary-container transition-colors shadow-md shrink-0"
                                        href="{{ route('public.course.chapters', $course['uuid']) }}">Explore</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                {{-- Plain Laravel paging, styled for this site --}}
                @if($courses->hasPages())
                    <div class="mt-16">
                        {{ $courses->links('vendor.pagination.public') }}
                    </div>
                @endif
            @endif
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script src="{{ asset('cdn/tom-select/tomSelect.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Multi-select instructor filter, searched against the server so the
        // list stays short however many instructors there are.
        new TomSelect('#instructors', {
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            plugins: ['remove_button'],
            maxOptions: 20,
            loadThrottle: 300,
            load(query, callback) {
                const url = new URL('{{ route('public.courses.instructors') }}', window.location.origin);
                url.searchParams.set('q', query);

                fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                    .then(response => response.json())
                    .then(callback)
                    .catch(() => callback());
            },
            render: {
                no_results: () => '<div class="option">No instructors found.</div>',
            },
        });
    });
</script>
@endpush
