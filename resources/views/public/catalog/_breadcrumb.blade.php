{{-- Catalogue breadcrumb, in the mockups' shape: crumbs are [label => url],
     and the last entry is rendered as the current page. --}}
<nav aria-label="Breadcrumb" class="mb-sm">
    <ol class="flex flex-wrap items-center space-x-2 font-label-md text-label-md text-on-surface-variant">
        @foreach($crumbs as $label => $url)
            <li>
                @if($loop->last)
                    <span aria-current="page" class="text-on-surface font-semibold">{{ $label }}</span>
                @else
                    <a class="hover:text-primary transition-colors duration-200" href="{{ $url }}">{{ $label }}</a>
                @endif
            </li>
            @unless($loop->last)
                <li><span aria-hidden="true" class="material-symbols-outlined text-[16px] mx-xs opacity-50">chevron_right</span></li>
            @endunless
        @endforeach
    </ol>
</nav>
