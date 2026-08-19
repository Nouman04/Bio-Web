{{-- Laravel's own paginator, styled for the public site. Not a DataTable —
     these are plain page links with the filters carried along. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="font-label-md text-on-surface-variant">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </p>

        <div class="flex items-center gap-1.5">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="w-10 h-10 flex items-center justify-center rounded-full text-outline-variant cursor-default">
                    <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous"
                    class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:bg-primary/10 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="w-10 h-10 flex items-center justify-center text-on-surface-variant">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                class="w-10 h-10 flex items-center justify-center rounded-full bg-primary text-on-primary font-label-md font-bold shadow-[0_4px_14px_0_rgba(70,72,212,0.39)]">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                                class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:bg-primary/10 hover:text-primary font-label-md transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next"
                    class="w-10 h-10 flex items-center justify-center rounded-full text-on-surface-variant hover:bg-primary/10 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[20px]">chevron_right</span>
                </a>
            @else
                <span class="w-10 h-10 flex items-center justify-center rounded-full text-outline-variant cursor-default">
                    <span class="material-symbols-outlined text-[20px]">chevron_right</span>
                </span>
            @endif
        </div>
    </nav>
@endif
