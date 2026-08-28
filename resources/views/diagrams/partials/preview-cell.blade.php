{{-- Thumbnail column --}}
@if($diagram->image_url)
    <a href="{{ $diagram->image_url }}" target="_blank" rel="noopener"
        class="block w-14 h-14 rounded-lg overflow-hidden border border-outline-variant/30 dark:border-slate-700 bg-surface-container-low dark:bg-slate-900 hover:ring-2 hover:ring-primary/40 transition-all">
        <img src="{{ $diagram->image_url }}" alt="{{ $diagram->title }}"
            class="w-full h-full object-cover" loading="lazy">
    </a>
@else
    <span class="w-14 h-14 rounded-lg flex items-center justify-center border border-dashed border-outline-variant/50 dark:border-slate-700 text-outline dark:text-slate-500">
        <i class="fa-regular fa-image"></i>
    </span>
@endif
