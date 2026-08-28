{{--
    The filter bar above every chapter listing.

    Each listing used to write its own, and they had drifted into five different
    treatments — different chrome, different icon sizes, some in a bordered tray,
    the videos one in pills. This is the single version they all use now.

    Usage:

        <x-chapter-filter placeholder="Search notes"
            :search="$search"
            :active="$search || $type"
            :clear="route('student.chapters.notes', ['courseId' => $courseId, 'chapterId' => $chapterId])">

            <x-chapter-filter.select name="type">
                <option value="">All kinds</option>
                …
            </x-chapter-filter.select>
        </x-chapter-filter>

    The slot is where the page's own dropdowns go; leave it empty for a listing
    that only searches. Everything is handled on the server — a select submits
    the form on change, so there is no Apply step for the dropdowns.
--}}
@props([
    'search' => '',
    'placeholder' => 'Search',
    // Whether anything is currently filtering, which is what shows Clear.
    'active' => false,
    // Where Clear goes: the same listing with no query string.
    'clear' => null,
])

<form method="GET" {{ $attributes->merge(['class' => 'flex flex-wrap gap-3 items-center']) }}>
    <div class="relative">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant" style="font-size:18px;">search</span>
        <input type="search" name="search" value="{{ $search }}" placeholder="{{ $placeholder }}"
            class="bg-surface-container-lowest border border-outline-variant text-on-surface text-sm rounded-lg py-2 pl-10 pr-4 focus:ring-2 focus:ring-primary transition-all w-full sm:w-56">
    </div>

    {{ $slot }}

    <button type="submit" class="bg-primary-container text-on-primary-container text-sm font-semibold py-2 px-4 rounded-lg flex items-center gap-2 hover:bg-primary hover:text-on-primary transition-colors">
        <span class="material-symbols-outlined" style="font-size:18px;">filter_list</span> Filter
    </button>

    @if($active && $clear)
        <a href="{{ $clear }}"
            class="text-on-surface-variant hover:text-primary text-sm flex items-center gap-1 transition-colors">
            <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span> Clear
        </a>
    @endif
</form>
