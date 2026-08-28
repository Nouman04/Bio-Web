{{--
    A labelled run of sidebar links. The label folds away when the rail is
    collapsed, which the .sidebar-group-label rule already handled — nothing
    had ever used it until now.
--}}
@props(['label' => null])

<div class="w-full flex flex-col space-y-1">
    @if($label)
        <p class="sidebar-group-label hide-on-collapse px-4 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider">
            {{ $label }}
        </p>
    @endif

    {{ $slot }}
</div>
