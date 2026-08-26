{{--
    One link in a sidebar.

    Both sidebars used to spell out the same forty-odd utility classes on every
    link, which is why they had drifted apart. This is the one implementation.

        <x-sidebar.item :href="route('courses')" icon="fa-solid fa-graduation-cap"
            label="Courses" :active="request()->routeIs('courses*')" :badge="$count" />

    `badge` is only drawn when it is a number above zero, so a quiet sidebar
    stays quiet. The label is hidden when the rail is collapsed; `data-tip`
    carries it to the CSS tooltip so a collapsed rail is still readable.
--}}
@props([
    'href',
    'icon',
    'label',
    'active' => false,
    'badge' => null,
    // A badge that means "this needs you", rather than a neutral total.
    'urgent' => false,
])

<a href="{{ $href }}" data-tip="{{ $label }}"
    @class([
        'sidebar-item group relative flex items-center justify-center lg:justify-start px-4 py-3 rounded-2xl center-on-collapse transition-all duration-300',
        'active' => $active,
        'text-on-surface-variant dark:text-slate-300 hover:bg-surface-container-low dark:hover:bg-slate-800 hover:text-on-surface dark:hover:text-white' => ! $active,
    ])
    @if($active) aria-current="page" @endif>

    <i class="{{ $icon }} text-xl sidebar-icon w-6 text-center flex-shrink-0"></i>

    <span class="block ml-4 hide-on-collapse flex-1 text-left">{{ $label }}</span>

    @if(is_numeric($badge) && $badge > 0)
        <span @class([
            'sidebar-badge hide-on-collapse',
            'is-urgent' => $urgent,
        ])>{{ $badge > 99 ? '99+' : $badge }}</span>

        {{-- Collapsed, the count moves onto the icon rather than disappearing,
             so a rail with nothing showing really means nothing is waiting. --}}
        <span class="sidebar-count">{{ $badge > 99 ? '99+' : $badge }}</span>
    @endif
</a>
