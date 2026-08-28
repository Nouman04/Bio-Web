{{-- Source column: uploaded file vs. external link --}}
@if($video->is_external)
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-secondary-container/20 text-secondary">
        <i class="fa-solid fa-link text-[10px]"></i>
        External
    </span>
@elseif($video->file_path)
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-tertiary-container/20 text-tertiary">
        <i class="fa-solid fa-upload text-[10px]"></i>
        Uploaded
    </span>
@else
    <span class="text-xs text-outline dark:text-slate-500">No source</span>
@endif
