{{-- Guide type column --}}
@if($guide->type === 'atp_guides')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-secondary-container/20 text-secondary">
        <i class="fa-solid fa-flask-vial text-[10px]"></i>
        ATP Guide
    </span>
@else
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-tertiary-container/20 text-tertiary">
        <i class="fa-solid fa-book-open text-[10px]"></i>
        Theory Guide
    </span>
@endif
