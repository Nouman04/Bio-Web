{{-- Actions column for the diagrams DataTable --}}
<div class="relative inline-block text-left action-dropdown">
    <button type="button" class="action-dropdown-trigger w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant dark:text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors" title="Actions">
        <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
    </button>
    <div class="action-dropdown-menu hidden absolute right-0 z-20 mt-1 w-44 rounded-xl bg-surface-container-lowest dark:bg-slate-800 border border-outline-variant/30 dark:border-slate-700 shadow-lg py-1">
        @if($diagram->image_url)
            <a href="{{ $diagram->image_url }}" target="_blank" rel="noopener"
                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
                <i class="fa-solid fa-up-right-from-square w-4 text-on-surface-variant"></i>
                View Image
            </a>
        @endif
        @can('edit diagram')
            <button type="button"
                onclick="openEditDiagramModal(this)"
                data-id="{{ $diagram->id }}"
                data-chapter-id="{{ $diagram->chapter_id }}"
                data-topic-id="{{ $diagram->topic_id }}"
                data-title="{{ $diagram->title }}"
                data-slug="{{ $diagram->slug }}"
                data-content="{{ $diagram->content }}"
                data-image="{{ $diagram->image_url }}"
                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-on-surface dark:text-slate-200 hover:bg-primary/5 transition-colors">
                <i class="fa-solid fa-pen w-4 text-on-surface-variant"></i>
                Edit
            </button>
        @endcan
        @can('delete diagram')
            <div class="my-1 border-t border-outline-variant/20 dark:border-slate-700"></div>
            <button type="button"
                onclick="deleteDiagram(this)"
                data-id="{{ $diagram->id }}"
                data-title="{{ $diagram->title }}"
                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-error hover:bg-error/5 transition-colors">
                <i class="fa-solid fa-trash w-4"></i>
                Delete
            </button>
        @endcan
    </div>
</div>
