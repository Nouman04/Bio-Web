{{--
    One dropdown inside <x-chapter-filter>.

    By default a change submits the form straight away, which suits a listing
    with a single dropdown. A page with several — the quiz list — passes
    `:auto="false"` so the reader can set them all and then press Filter once.

    `icon` puts a glyph on the left — the videos sort control is marked that
    way. Without it the select is padded for text alone.
--}}
@props(['name', 'icon' => null, 'auto' => true])

<div class="relative">
    <select name="{{ $name }}" @if($auto) onchange="this.form.submit()" @endif
        class="appearance-none bg-surface-container-lowest border border-outline-variant text-on-surface text-sm rounded-lg py-2 {{ $icon ? 'pl-10' : 'pl-4' }} pr-10 focus:ring-2 focus:ring-primary transition-all cursor-pointer">
        {{ $slot }}
    </select>

    @if($icon)
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant" style="font-size:18px;">{{ $icon }}</span>
    @endif

    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant" style="font-size:18px;">expand_more</span>
</div>
